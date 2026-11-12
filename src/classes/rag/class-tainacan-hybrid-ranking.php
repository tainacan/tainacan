<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Keyword and vector results, fused by rank instead of by score.
 *
 * The two sides produce numbers that cannot be added: cosine similarity lives in
 * [-1, 1] and is model-specific, while a keyword ranking has no score at all, only an
 * order. Reciprocal rank fusion sidesteps the unit problem — an item scores
 * `1 / (k + rank)` in each list it appears in, so agreement between the lists is what
 * wins, and a single very high cosine similarity cannot drown a term match.
 *
 * Two properties are deliberate:
 *
 * 1. **Keyword search is the fallback, not the exception.** Every vector-side failure
 *    (no active space, provider not configured, provider paused, index unreadable)
 *    degrades to keyword-only results and records the reason in `degraded`. A search
 *    box that returns nothing because an API key expired is worse than one that
 *    returns worse results.
 * 2. **Visibility is the repository's job, not this class's.** Keyword IDs come from
 *    `Items::fetch_ids()`, so collection readability and status apply exactly as they
 *    do everywhere else. Vector IDs come from `Semantic_Search`, which already
 *    re-fetches through the same repository.
 *
 * @since 1.5.0
 */
class Hybrid_Ranking {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * RRF constant. 60 is the value from the original paper and the common default;
	 * it flattens the head of each list so the top hit does not dominate.
	 *
	 * @var int
	 */
	const RRF_K = 60;

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_search( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		$args = [];

		if ( isset( $input['space_id'] ) && (int) $input['space_id'] > 0 ) {
			$args['space_id'] = (int) $input['space_id'];
		}

		if ( isset( $input['limit'] ) && (int) $input['limit'] > 0 ) {
			$args['limit'] = (int) $input['limit'];
		}

		if ( isset( $input['collection_id'] ) && (int) $input['collection_id'] > 0 ) {
			$args['collection_ids'] = [ (int) $input['collection_id'] ];
		}

		return $this->search( isset( $input['query'] ) ? $input['query'] : '', $args );
	}

	/**
	 * Hybrid search: vector hits plus keyword hits, fused by rank.
	 *
	 * @param string $query Query text.
	 * @param array  $args  space_id, limit, collection_ids.
	 * @return array|\WP_Error
	 */
	public function search( $query, array $args = [] ) {
		$query = is_string( $query ) ? trim( $query ) : '';

		if ( '' === $query ) {
			return new \WP_Error(
				'tainacan_rag_missing_query',
				__( 'A non-empty query is required.', 'tainacan' )
			);
		}

		$limit          = isset( $args['limit'] ) ? max( 1, min( 50, (int) $args['limit'] ) ) : 10;
		$collection_ids = isset( $args['collection_ids'] ) ? array_map( 'intval', (array) $args['collection_ids'] ) : [];

		$vector_hits = [];
		$degraded    = '';

		$search_args = [ 'limit' => $limit ];

		if ( ! empty( $collection_ids ) ) {
			$search_args['collection_ids'] = $collection_ids;
		}

		if ( isset( $args['space_id'] ) && (int) $args['space_id'] > 0 ) {
			$search_args['space_id'] = (int) $args['space_id'];
		}

		$vector = Semantic_Search::get_instance()->search( $query, $search_args );

		if ( is_wp_error( $vector ) ) {
			$degraded = $vector->get_error_code();
		} else {
			$vector_hits = $vector['results'];
		}

		$keyword_ids = $this->keyword_item_ids( $query, $collection_ids, $limit );

		$vector_ids = array_map( 'intval', array_column( $vector_hits, 'item_id' ) );
		$fusion     = $this->fuse( [ $vector_ids, $keyword_ids ] );

		$vector_by_id  = [];
		$vector_ranks  = [];
		$keyword_ranks = [];

		foreach ( $vector_ids as $position => $item_id ) {
			$vector_ranks[ $item_id ] = $position + 1;
		}

		foreach ( $keyword_ids as $position => $item_id ) {
			$keyword_ranks[ (int) $item_id ] = $position + 1;
		}

		foreach ( $vector_hits as $hit ) {
			$vector_by_id[ (int) $hit['item_id'] ] = $hit;
		}

		$results = [];

		foreach ( $fusion['order'] as $item_id ) {
			$hit = isset( $vector_by_id[ $item_id ] ) ? $vector_by_id[ $item_id ] : [];

			$results[] = [
				'item_id'      => (int) $item_id,
				'title'        => (string) get_the_title( (int) $item_id ),
				'rrf'          => round( $fusion['scores'][ $item_id ], 6 ),
				'vector_rank'  => isset( $vector_ranks[ $item_id ] ) ? (int) $vector_ranks[ $item_id ] : null,
				'vector_score' => isset( $hit['score'] ) ? (float) $hit['score'] : null,
				'keyword_rank' => isset( $keyword_ranks[ $item_id ] ) ? (int) $keyword_ranks[ $item_id ] : null,
				'snippets'     => isset( $hit['snippets'] ) ? $hit['snippets'] : [],
			];
		}

		return [
			'query'             => $query,
			'results'           => $results,
			'count'             => count( $results ),
			'fusion'            => 'rrf',
			'k'                 => self::RRF_K,
			'vector_candidates' => count( $vector_ids ),
			'keyword_candidates' => count( $keyword_ids ),
			'degraded'          => $degraded,
		];
	}

	/**
	 * Item IDs a keyword search returns, through the repository.
	 *
	 * @param string $query          Query text.
	 * @param int[]  $collection_ids Optional collection scope.
	 * @param int    $limit          How many IDs to ask for.
	 * @return int[]
	 */
	public function keyword_item_ids( $query, array $collection_ids = [], $limit = 10 ) {
		$query = is_string( $query ) ? trim( $query ) : '';

		if ( '' === $query ) {
			return [];
		}

		$ids = \Tainacan\Repositories\Items::get_instance()->fetch_ids(
			[
				's'              => $query,
				'posts_per_page' => max( 1, min( 50, (int) $limit ) ),
				'post_status'    => 'publish',
			],
			array_map( 'intval', $collection_ids )
		);

		return is_array( $ids ) ? array_map( 'intval', $ids ) : [];
	}

	/**
	 * Reciprocal rank fusion. Pure, so the arithmetic is testable without a database.
	 *
	 * @param array $ranked_lists Ordered lists of item IDs.
	 * @param int   $k            RRF constant.
	 * @return array{order: int[], scores: array<int, float>}
	 */
	public function fuse( array $ranked_lists, $k = self::RRF_K ) {
		$k      = max( 1, (int) $k );
		$scores = [];

		foreach ( $ranked_lists as $list ) {
			$rank = 0;

			foreach ( (array) $list as $item_id ) {
				$item_id = (int) $item_id;

				if ( $item_id <= 0 ) {
					continue;
				}

				$rank++;

				if ( ! isset( $scores[ $item_id ] ) ) {
					$scores[ $item_id ] = 0.0;
				}

				$scores[ $item_id ] += 1 / ( $k + $rank );
			}
		}

		$order = array_keys( $scores );

		// Ties are broken by the lower ID so the same inputs always produce the same order.
		usort(
			$order,
			static function ( $a, $b ) use ( $scores ) {
				if ( $scores[ $a ] === $scores[ $b ] ) {
					return $a <=> $b;
				}

				return $scores[ $a ] < $scores[ $b ] ? 1 : -1;
			}
		);

		return [
			'order'  => $order,
			'scores' => $scores,
		];
	}

	/**
	 * Reorder one page of search results by hybrid rank.
	 *
	 * Deliberately limited to the posts the query already returned: the keyword side
	 * is the page's own order and the vector side is filtered down to those IDs, so
	 * which page a result lands on is still WordPress's decision. Changing page
	 * membership would mean owning `found_posts` and pagination, which is a different
	 * change with a different risk.
	 *
	 * @param string   $query          Query text.
	 * @param array    $posts          WP_Post objects from the query.
	 * @param int[]    $collection_ids Collections the query covers.
	 * @return array
	 */
	public function rerank_posts( $query, array $posts, array $collection_ids = [] ) {
		if ( count( $posts ) < 2 ) {
			return $posts;
		}

		$ids = [];

		foreach ( $posts as $post ) {
			if ( $post instanceof \WP_Post ) {
				$ids[] = (int) $post->ID;
			}
		}

		if ( count( $ids ) < 2 ) {
			return $posts;
		}

		$args = [ 'limit' => min( 50, count( $ids ) * 2 ) ];

		if ( ! empty( $collection_ids ) ) {
			$args['collection_ids'] = $collection_ids;
		}

		$vector = Semantic_Search::get_instance()->search( $query, $args );

		if ( is_wp_error( $vector ) ) {
			return $posts;
		}

		$vector_ids = array_values(
			array_intersect(
				array_map( 'intval', array_column( $vector['results'], 'item_id' ) ),
				$ids
			)
		);

		if ( count( $vector_ids ) < 2 ) {
			return $posts;
		}

		$fusion   = $this->fuse( [ $vector_ids, $ids ] );
		$position = array_flip( $fusion['order'] );

		usort(
			$posts,
			static function ( $a, $b ) use ( $position ) {
				$left  = isset( $position[ (int) $a->ID ] ) ? $position[ (int) $a->ID ] : PHP_INT_MAX;
				$right = isset( $position[ (int) $b->ID ] ) ? $position[ (int) $b->ID ] : PHP_INT_MAX;

				return $left <=> $right;
			}
		);

		return $posts;
	}
}
