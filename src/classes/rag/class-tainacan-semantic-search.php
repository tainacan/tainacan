<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Retrieval: the read side of the index.
 *
 * The dangerous part of semantic search is not the distance function, it is that a
 * vector index has no idea what a capability is. An ANN scan happily returns the
 * chunk of a private draft to an anonymous visitor, and the result looks like a
 * perfectly good ranking. So every query runs in two stages:
 *
 * 1. **Candidates** from one space table, ordered by cosine distance, with a
 *    `LIMIT`. `ORDER BY VEC_DISTANCE_COSINE(...) LIMIT n` is the only shape MariaDB
 *    could ever answer from a vector index, so it is the shape used — but on the
 *    11.8.9 server this stack runs, `EXPLAIN` still reports a full scan with a
 *    filesort, even without a `WHERE` clause and even with `FORCE INDEX`. Retrieval
 *    is therefore O(chunks in the space) today: correct, bounded by `OVERFETCH`,
 *    and not yet an ANN scan. Recorded as a finding rather than assumed away.
 * 2. **Visibility** by re-fetching the candidate item IDs through the `Items`
 *    repository, which applies collection readability, post status and the
 *    per-collection `read_private_posts` capabilities. Only IDs that survive come
 *    back, and the count of what was dropped is reported as `filtered_out` so the
 *    filter is observable rather than invisible.
 *
 * Stage 2 over-fetches on purpose: asking for exactly `limit` candidates means a
 * page of private drafts returns almost nothing. `OVERFETCH` widens the net, and
 * the final list is trimmed to `limit` after filtering.
 *
 * Two things this class deliberately does not do:
 *
 * - It never searches across spaces. Vectors from different models are not
 *   comparable even at equal dimensions, so a query belongs to exactly one space.
 * - It never re-embeds to compute similarity. `similar_to_item()` reads the item's
 *   stored chunk and embeds that, so an item that was never indexed is reported as
 *   such instead of silently compared against a different model's geometry.
 *
 * @since 1.5.0
 */
class Semantic_Search {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Results returned when the caller does not ask for a number.
	 *
	 * @var int
	 */
	const DEFAULT_LIMIT = 10;

	/**
	 * Hard ceiling on results, so one call cannot become a bulk export.
	 *
	 * @var int
	 */
	const MAX_LIMIT = 50;

	/**
	 * Snippets kept per item when the caller does not ask for a number.
	 *
	 * @var int
	 */
	const DEFAULT_SNIPPETS = 2;

	/**
	 * Hard ceiling on snippets per item.
	 *
	 * @var int
	 */
	const MAX_SNIPPETS = 5;

	/**
	 * How many candidates to pull per requested result before visibility filtering.
	 *
	 * @var int
	 */
	const OVERFETCH = 4;

	/**
	 * Nothing to hook: retrieval is driven by calls, not by events.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Ability callback: query the active text space.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_search( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		return $this->search(
			isset( $input['query'] ) ? $input['query'] : '',
			$this->args_from_input( $input )
		);
	}

	/**
	 * Ability callback: items near an already indexed item.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_similar( $input = [] ) {
		$input   = is_array( $input ) ? $input : [];
		$item_id = isset( $input['item_id'] ) ? (int) $input['item_id'] : 0;

		return $this->similar_to_item( $item_id, $this->args_from_input( $input ) );
	}

	/**
	 * Translate ability input into the internal argument shape.
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	public function args_from_input( array $input ) {
		$args = [];

		if ( isset( $input['space_id'] ) && (int) $input['space_id'] > 0 ) {
			$args['space_id'] = (int) $input['space_id'];
		}

		if ( isset( $input['limit'] ) && (int) $input['limit'] > 0 ) {
			$args['limit'] = (int) $input['limit'];
		}

		if ( isset( $input['snippets'] ) && (int) $input['snippets'] > 0 ) {
			$args['snippets'] = (int) $input['snippets'];
		}

		if ( isset( $input['collection_id'] ) && (int) $input['collection_id'] > 0 ) {
			$args['collection_ids'] = [ (int) $input['collection_id'] ];
		}

		return $args;
	}

	/**
	 * Search one space with a text query.
	 *
	 * @param string $query Natural language query.
	 * @param array  $args  space_id, limit, snippets, collection_ids.
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

		$space = $this->resolve_space( $args );

		if ( is_wp_error( $space ) ) {
			return $space;
		}

		$embedding = $this->embed_query( $space, $query );

		if ( is_wp_error( $embedding ) ) {
			return $embedding;
		}

		return $this->query_space( $space, $embedding['vector'], $args, $query );
	}

	/**
	 * Items similar to an already indexed item, within its own space.
	 *
	 * The seed text is the item's stored chunk rather than a fresh embedding of the
	 * item, which makes "not indexed" a distinct, actionable answer instead of a
	 * comparison against a different model's geometry.
	 *
	 * @param int   $item_id Item id.
	 * @param array $args    space_id, limit, snippets, collection_ids.
	 * @return array|\WP_Error
	 */
	public function similar_to_item( $item_id, array $args = [] ) {
		global $wpdb;

		$item_id = (int) $item_id;

		if ( $item_id <= 0 ) {
			return new \WP_Error(
				'tainacan_rag_missing_item',
				__( 'An item_id is required.', 'tainacan' )
			);
		}

		// Reading the seed through the repository means an unreadable item is
		// indistinguishable from a missing one, which is the point.
		$item = \Tainacan\Repositories\Items::get_instance()->fetch( $item_id );

		if ( ! $item instanceof \Tainacan\Entities\Item ) {
			return new \WP_Error(
				'tainacan_rag_item_not_found',
				__( 'No readable item exists with that id.', 'tainacan' )
			);
		}

		$space = $this->resolve_space( $args );

		if ( is_wp_error( $space ) ) {
			return $space;
		}

		$table = $space['table'];
		$seed  = $wpdb->get_var( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry.
			$wpdb->prepare(
				"SELECT content FROM {$table} WHERE item_id = %d ORDER BY chunk_index ASC LIMIT 1",
				$item_id
			)
		);

		if ( ! is_string( $seed ) || '' === trim( $seed ) ) {
			return new \WP_Error(
				'tainacan_rag_item_not_indexed',
				__( 'That item has no vectors in this space yet. Queue it for indexing first.', 'tainacan' ),
				[
					'item_id'  => $item_id,
					'space_id' => $space['id'],
				]
			);
		}

		$embedding = $this->embed_query( $space, $seed );

		if ( is_wp_error( $embedding ) ) {
			return $embedding;
		}

		$args['exclude_item_id'] = $item_id;

		$result = $this->query_space( $space, $embedding['vector'], $args, $seed );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$result['seed_item_id'] = $item_id;

		return $result;
	}

	/**
	 * Pick the space a query runs in.
	 *
	 * @param array $args space_id or modality.
	 * @return array|\WP_Error
	 */
	public function resolve_space( array $args ) {
		$spaces = Space_Repository::get_instance();

		if ( isset( $args['space_id'] ) && (int) $args['space_id'] > 0 ) {
			$space = $spaces->get_space( (int) $args['space_id'] );

			if ( ! $space ) {
				return new \WP_Error(
					'tainacan_rag_space_not_found',
					__( 'No space exists with that id.', 'tainacan' )
				);
			}

			return $space;
		}

		$modality = isset( $args['modality'] ) ? (string) $args['modality'] : 'text';
		$space    = $spaces->get_active( $modality );

		if ( ! $space ) {
			return new \WP_Error(
				'tainacan_rag_no_active_space',
				sprintf(
					/* translators: %s: modality, text or image */
					__( 'No %s space is active. Create one and activate it before searching.', 'tainacan' ),
					$modality
				)
			);
		}

		return $space;
	}

	/**
	 * Embed a query inside a space, respecting a paused provider.
	 *
	 * @param array  $space Space row.
	 * @param string $text  Text to embed.
	 * @return array|\WP_Error Array with `vector`, `model` and `dimensions`.
	 */
	public function embed_query( array $space, $text ) {
		$breaker = Provider_Circuit_Breaker::get_instance();

		if ( $breaker->is_paused( $space['provider'], $space['model'] ) ) {
			return new \WP_Error(
				'tainacan_rag_provider_paused',
				__( 'This provider and model are paused after repeated failures, so the query was not sent. Similar-items still works, because it reuses stored vectors.', 'tainacan' ),
				[
					'provider' => $space['provider'],
					'model'    => $space['model'],
				]
			);
		}

		$provider = Provider_Registry::get_instance()->get_provider( $space['provider'] );

		if ( ! $provider ) {
			return new \WP_Error(
				'tainacan_rag_provider_missing',
				sprintf(
					/* translators: %s: provider id */
					__( 'The provider %s is no longer registered.', 'tainacan' ),
					$space['provider']
				)
			);
		}

		if ( ! $provider->is_configured() ) {
			return new \WP_Error(
				'tainacan_rag_provider_not_configured',
				__( 'The provider for this space is not configured.', 'tainacan' )
			);
		}

		$result = $provider->embed( [ (string) $text ], $space['model'], $space['modality'] );

		if ( is_wp_error( $result ) ) {
			$breaker->record_failure( $space['provider'], $space['model'], $result->get_error_message() );

			return $result;
		}

		if ( empty( $result['vectors'][0] ) || ! is_array( $result['vectors'][0] ) ) {
			return new \WP_Error(
				'tainacan_rag_empty_vector',
				__( 'The provider returned no vector for the query.', 'tainacan' )
			);
		}

		$vector = $result['vectors'][0];

		if ( count( $vector ) !== (int) $space['dimensions'] ) {
			return new \WP_Error(
				'tainacan_rag_dimension_mismatch',
				sprintf(
					/* translators: 1: returned dimensions, 2: space dimensions */
					__( 'The provider returned %1$d dimensions for a space built for %2$d.', 'tainacan' ),
					count( $vector ),
					(int) $space['dimensions']
				)
			);
		}

		$breaker->record_success( $space['provider'], $space['model'] );

		return [
			'vector'     => '[' . implode( ',', array_map( 'floatval', $vector ) ) . ']',
			'model'      => $space['model'],
			'dimensions' => (int) $space['dimensions'],
		];
	}

	/**
	 * Build the ANN statement and its parameters. Pure, so the shape is testable.
	 *
	 * The distance is computed twice on purpose: once in `SELECT` so the caller has
	 * the score, and once in `ORDER BY`, because `ORDER BY VEC_DISTANCE_COSINE(...)
	 * LIMIT n` is the only form a vector index could serve. Ordering by the `distance`
	 * alias would rule that out permanently.
	 *
	 * Measured on MariaDB 11.8.9: none of these shapes reaches the index yet, with or
	 * without a `WHERE`, and `FORCE INDEX` does not help either. The duplicate stays
	 * because it costs nothing and keeps the door open; the sprint record carries the
	 * measurement so nobody reads it as a claim.
	 *
	 * That duplication fixes the parameter order: the `SELECT` placeholder comes
	 * first in the statement text, the `WHERE` placeholders second, the `ORDER BY`
	 * copy third, the limit last. Tests pin the alignment rather than trusting it.
	 *
	 * @param string $table           Space table name.
	 * @param string $vector          Vector literal.
	 * @param int[]  $collection_ids  Optional collection filter.
	 * @param int    $limit           Candidate count.
	 * @param int    $exclude_item_id Optional item to leave out.
	 * @return array{sql: string, params: array}
	 */
	public function build_ann_query( $table, $vector, array $collection_ids = [], $limit = self::DEFAULT_LIMIT, $exclude_item_id = 0 ) {
		$where         = [];
		$where_params  = [];

		$collection_ids = array_values( array_filter( array_map( 'intval', $collection_ids ) ) );

		if ( ! empty( $collection_ids ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $collection_ids ), '%d' ) );
			$where[]      = "collection_id IN ({$placeholders})";
			$where_params = array_merge( $where_params, $collection_ids );
		}

		if ( (int) $exclude_item_id > 0 ) {
			$where[]        = 'item_id <> %d';
			$where_params[] = (int) $exclude_item_id;
		}

		$sql = "SELECT item_id, collection_id, source, chunk_index, content,\n"
			. "\tVEC_DISTANCE_COSINE(embedding, VEC_FromText(%s)) AS distance\n"
			. "FROM {$table}\n";

		if ( ! empty( $where ) ) {
			$sql .= 'WHERE ' . implode( ' AND ', $where ) . "\n";
		}

		$sql .= "ORDER BY VEC_DISTANCE_COSINE(embedding, VEC_FromText(%s)) ASC\n"
			. 'LIMIT %d';

		$params = array_merge(
			[ (string) $vector ],
			$where_params,
			[ (string) $vector ],
			[ max( 1, (int) $limit ) ]
		);

		return [
			'sql'    => $sql,
			'params' => $params,
		];
	}

	/**
	 * Post statuses the current user may read in every collection involved.
	 *
	 * Starts from `publish` and only adds `private` when the user holds
	 * `read_private_posts` for every collection present among the candidates. One
	 * collection the user cannot read is enough to keep the whole query public-only,
	 * because a mixed status list would otherwise let a single-collection query skip
	 * WordPress's own capability check.
	 *
	 * @param int[] $collection_ids Collections present among the candidates.
	 * @return string[]
	 */
	public function readable_statuses( array $collection_ids ) {
		$statuses = [ 'publish' ];

		if ( ! is_user_logged_in() || empty( $collection_ids ) ) {
			return $statuses;
		}

		$collections = \Tainacan\Repositories\Collections::get_instance();

		foreach ( $collection_ids as $collection_id ) {
			$collection = $collections->fetch( (int) $collection_id );

			if ( ! $collection instanceof \Tainacan\Entities\Collection ) {
				return $statuses;
			}

			if ( ! $collection->can_read() ) {
				return $statuses;
			}

			$capabilities = $collection->get_items_capabilities();
			$capability   = isset( $capabilities->read_private_posts ) ? $capabilities->read_private_posts : '';

			if ( '' === $capability || ! current_user_can( $capability ) ) {
				return $statuses;
			}
		}

		$statuses[] = 'private';

		return $statuses;
	}

	/**
	 * Keep only the candidate item IDs the current user can actually read.
	 *
	 * @param int[] $item_ids       Candidate item IDs, best first.
	 * @param int[] $collection_ids Collections present among the candidates.
	 * @return int[]
	 */
	public function visible_item_ids( array $item_ids, array $collection_ids ) {
		$item_ids = array_values( array_unique( array_map( 'intval', $item_ids ) ) );

		if ( empty( $item_ids ) ) {
			return [];
		}

		$readable = \Tainacan\Repositories\Items::get_instance()->fetch_ids(
			[
				'post__in'       => $item_ids,
				'posts_per_page' => count( $item_ids ),
				'post_status'    => $this->readable_statuses( $collection_ids ),
			]
		);

		$allowed = array_flip( array_map( 'intval', (array) $readable ) );
		$visible = [];

		foreach ( $item_ids as $item_id ) {
			if ( ! isset( $allowed[ $item_id ] ) ) {
				continue;
			}

			$post = get_post( $item_id );

			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			// A password on the post protects its content, and a snippet is content.
			if ( '' !== (string) $post->post_password && ! current_user_can( 'edit_post', $item_id ) ) {
				continue;
			}

			$visible[] = $item_id;
		}

		return $visible;
	}

	/**
	 * Group candidate rows into per-item hits. Pure.
	 *
	 * Rows arrive ordered by ascending distance, so the first row seen for an item
	 * carries its best score and the item order that comes out is already the
	 * ranking.
	 *
	 * @param array $rows        Rows with item_id, collection_id, source, chunk_index, content, distance.
	 * @param int[] $visible_ids Item ids the caller may see.
	 * @param int   $snippets    Snippets to keep per item.
	 * @return array
	 */
	public function collapse_hits( array $rows, array $visible_ids, $snippets = self::DEFAULT_SNIPPETS ) {
		$visible  = array_flip( array_map( 'intval', $visible_ids ) );
		$snippets = max( 1, min( self::MAX_SNIPPETS, (int) $snippets ) );
		$hits     = [];

		foreach ( $rows as $row ) {
			$item_id = isset( $row['item_id'] ) ? (int) $row['item_id'] : 0;

			if ( $item_id <= 0 || ! isset( $visible[ $item_id ] ) ) {
				continue;
			}

			$distance = isset( $row['distance'] ) ? (float) $row['distance'] : 1.0;
			$score    = round( 1 - $distance, 6 );

			if ( ! isset( $hits[ $item_id ] ) ) {
				$hits[ $item_id ] = [
					'item_id'       => $item_id,
					'collection_id' => isset( $row['collection_id'] ) ? (int) $row['collection_id'] : 0,
					'score'         => $score,
					'snippets'      => [],
				];
			}

			if ( count( $hits[ $item_id ]['snippets'] ) >= $snippets ) {
				continue;
			}

			$hits[ $item_id ]['snippets'][] = [
				'source'      => isset( $row['source'] ) ? (string) $row['source'] : '',
				'chunk_index' => isset( $row['chunk_index'] ) ? (int) $row['chunk_index'] : 0,
				'content'     => isset( $row['content'] ) ? (string) $row['content'] : '',
				'score'       => $score,
			];
		}

		return array_values( $hits );
	}

	/**
	 * Run the ANN scan, filter by visibility, and assemble the response.
	 *
	 * @param array  $space  Space row.
	 * @param string $vector Query vector literal.
	 * @param array  $args   limit, snippets, collection_ids, exclude_item_id.
	 * @param string $query  Text the caller searched with, echoed back.
	 * @return array|\WP_Error
	 */
	private function query_space( array $space, $vector, array $args, $query ) {
		global $wpdb;

		$limit    = isset( $args['limit'] ) ? min( self::MAX_LIMIT, max( 1, (int) $args['limit'] ) ) : self::DEFAULT_LIMIT;
		$snippets = isset( $args['snippets'] ) ? (int) $args['snippets'] : self::DEFAULT_SNIPPETS;

		$collection_ids = isset( $args['collection_ids'] ) ? (array) $args['collection_ids'] : [];
		$exclude        = isset( $args['exclude_item_id'] ) ? (int) $args['exclude_item_id'] : 0;

		// Over-fetch so visibility filtering still has something to return. The cap
		// is what keeps a wide request from turning into a table scan in memory.
		$candidates = min( self::MAX_LIMIT * self::OVERFETCH, $limit * self::OVERFETCH );

		$statement = $this->build_ann_query( $space['table'], $vector, $collection_ids, $candidates, $exclude );
		$prepared  = $wpdb->prepare( $statement['sql'], $statement['params'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Built by build_ann_query from a registry table name.
		$rows      = $wpdb->get_results( $prepared, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		if ( ! empty( $wpdb->last_error ) ) {
			return new \WP_Error(
				'tainacan_rag_query_failed',
				sprintf(
					/* translators: %s: database error */
					__( 'The vector table could not be queried: %s', 'tainacan' ),
					$wpdb->last_error
				),
				[
					'space_id' => $space['id'],
					'table'    => $space['table'],
				]
			);
		}

		$rows = is_array( $rows ) ? $rows : [];

		// Distances come back as strings from the driver; cast once so both the
		// filtering and the scoring below see numbers.
		$rows = array_map(
			static function ( $row ) {
				$row['distance'] = isset( $row['distance'] ) ? (float) $row['distance'] : 1.0;
				return $row;
			},
			$rows
		);

		$row_collections = array_values(
			array_unique(
				array_map(
					static function ( $row ) {
						return (int) $row['collection_id'];
					},
					$rows
				)
			)
		);

		foreach ( $collection_ids as $collection_id ) {
			if ( ! in_array( (int) $collection_id, $row_collections, true ) ) {
				$row_collections[] = (int) $collection_id;
			}
		}

		$candidate_ids = array_values(
			array_unique(
				array_map(
					static function ( $row ) {
						return (int) $row['item_id'];
					},
					$rows
				)
			)
		);

		$visible = $this->visible_item_ids( $candidate_ids, $row_collections );
		$hits    = $this->collapse_hits( $rows, $visible, $snippets );
		$hits    = array_slice( $hits, 0, $limit );

		return [
			'query'        => (string) $query,
			'space'        => [
				'id'         => (int) $space['id'],
				'key'        => (string) $space['key'],
				'provider'   => (string) $space['provider'],
				'model'      => (string) $space['model'],
				'modality'   => (string) $space['modality'],
				'dimensions' => (int) $space['dimensions'],
			],
			'results'      => $hits,
			'count'        => count( $hits ),
			'candidates'   => count( $candidate_ids ),
			'filtered_out' => max( 0, count( $candidate_ids ) - count( $visible ) ),
		];
	}
}
