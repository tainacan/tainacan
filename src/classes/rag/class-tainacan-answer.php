<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Retrieval, then an answer grounded in what was retrieved.
 *
 * The only inference this plugin performs, and the only place where being wrong is
 * invisible: a generated sentence reads as confidently whether or not the sources
 * support it. So the shape here is fixed and refuses to improvise:
 *
 * 1. **Citations first.** The answer is built from the numbered snippets that
 *    retrieval actually returned to *this* caller, which means the visibility filter
 *    already ran. An answer cannot cite a document the caller could not open.
 * 2. **No generator, no answer.** The generator is a callable supplied through
 *    `tainacan_rag_answer_generator`. When nothing supplies one, the response is
 *    `answer: null` with `reason: no_generator` and the citations and prompt intact —
 *    not an error, and never a fabricated summary. Retrieval succeeded; generation is
 *    simply not configured.
 * 3. **The prompt is returned with the answer.** An operator asking "why did it say
 *    that" needs to see the exact context and instruction that produced it. It
 *    contains only snippets the caller already received.
 *
 * The generator contract is a filter rather than a direct call into the WordPress AI
 * plugin because this plugin must work without it: no `ai/*` ability is registered on
 * this stack, so a hard dependency would be dead code that only a mocked test could
 * exercise.
 *
 * @since 1.5.0
 */
class Answer {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Sources quoted into the prompt.
	 *
	 * @var int
	 */
	const DEFAULT_SOURCES = 3;

	/**
	 * Hard ceiling on sources.
	 *
	 * @var int
	 */
	const MAX_SOURCES = 5;

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
	public function for_ability_answer( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		$args = [];

		if ( isset( $input['space_id'] ) && (int) $input['space_id'] > 0 ) {
			$args['space_id'] = (int) $input['space_id'];
		}

		if ( isset( $input['collection_id'] ) && (int) $input['collection_id'] > 0 ) {
			$args['collection_ids'] = [ (int) $input['collection_id'] ];
		}

		if ( isset( $input['sources'] ) && (int) $input['sources'] > 0 ) {
			$args['sources'] = (int) $input['sources'];
		}

		return $this->answer( isset( $input['query'] ) ? $input['query'] : '', $args );
	}

	/**
	 * Answer a question from the index.
	 *
	 * @param string $query Question.
	 * @param array  $args  space_id, collection_ids, sources.
	 * @return array|\WP_Error
	 */
	public function answer( $query, array $args = [] ) {
		$query = is_string( $query ) ? trim( $query ) : '';

		if ( '' === $query ) {
			return new \WP_Error(
				'tainacan_rag_missing_query',
				__( 'A non-empty query is required.', 'tainacan' )
			);
		}

		$search_args = [ 'limit' => 10 ];

		if ( ! empty( $args['collection_ids'] ) ) {
			$search_args['collection_ids'] = array_map( 'intval', (array) $args['collection_ids'] );
		}

		if ( isset( $args['space_id'] ) ) {
			$search_args['space_id'] = (int) $args['space_id'];
		}

		$search = Semantic_Search::get_instance()->search( $query, $search_args );

		if ( is_wp_error( $search ) ) {
			return $search;
		}

		$sources = isset( $args['sources'] ) ? (int) $args['sources'] : self::DEFAULT_SOURCES;
		$sources = max( 1, min( self::MAX_SOURCES, $sources ) );

		$citations = $this->citations_from( $search['results'], $sources );

		$retrieval = [
			'count'        => (int) $search['count'],
			'candidates'   => (int) $search['candidates'],
			'filtered_out' => (int) $search['filtered_out'],
			'space_id'     => (int) $search['space']['id'],
		];

		if ( empty( $citations ) ) {
			return [
				'answer'    => null,
				'reason'    => 'no_results',
				'message'   => __( 'Nothing in the index matches this question, so there is nothing to answer from. The index may need a backfill, or the question may be outside the indexed content.', 'tainacan' ),
				'citations' => [],
				'retrieval' => $retrieval,
			];
		}

		$prompt    = $this->build_prompt( $query, $citations );
		$generator = $this->generator();

		if ( ! $generator ) {
			return [
				'answer'    => null,
				'reason'    => 'no_generator',
				'message'   => __( 'No text generator is configured, so the retrieved sources are returned without an answer. Wire one through the tainacan_rag_answer_generator filter to generate text.', 'tainacan' ),
				'citations' => $citations,
				'prompt'    => $prompt,
				'retrieval' => $retrieval,
			];
		}

		$generated = call_user_func(
			$generator,
			$prompt,
			[
				'query'     => $query,
				'citations' => $citations,
				'retrieval' => $retrieval,
			]
		);

		if ( is_wp_error( $generated ) ) {
			$generated->add_data( [ 'citations' => $citations, 'retrieval' => $retrieval ] );

			return $generated;
		}

		$generated = is_string( $generated ) ? trim( $generated ) : '';

		if ( '' === $generated ) {
			return new \WP_Error(
				'tainacan_rag_empty_answer',
				__( 'The generator returned no text. The sources are listed so the answer can be written by hand.', 'tainacan' ),
				[ 'citations' => $citations, 'retrieval' => $retrieval ]
			);
		}

		return [
			'answer'    => $generated,
			'reason'    => 'answered',
			'citations' => $citations,
			'prompt'    => $prompt,
			'retrieval' => $retrieval,
		];
	}

	/**
	 * The generator callable, if the site wired one.
	 *
	 * Anything that is not callable is dropped rather than trusted: a filter that
	 * returns a class name with a typo would otherwise explode inside `call_user_func`
	 * at the worst moment.
	 *
	 * @return callable|null
	 */
	public function generator() {
		/**
		 * Filters the text generator used by `tainacan/rag-answer`.
		 *
		 * The callable receives the prompt and a context array (query, citations,
		 * retrieval) and must return a string or a WP_Error.
		 *
		 * @param callable|null $generator Generator callable.
		 */
		$generator = apply_filters( 'tainacan_rag_answer_generator', null );

		return is_callable( $generator ) ? $generator : null;
	}

	/**
	 * Turn retrieval hits into numbered sources.
	 *
	 * @param array $results Retrieval results.
	 * @param int   $sources How many to keep.
	 * @return array
	 */
	public function citations_from( array $results, $sources = self::DEFAULT_SOURCES ) {
		$sources   = max( 1, min( self::MAX_SOURCES, (int) $sources ) );
		$citations = [];
		$number    = 0;

		foreach ( $results as $hit ) {
			if ( empty( $hit['snippets'] ) ) {
				continue;
			}

			$number++;

			$snippet = $hit['snippets'][0];

			$citations[] = [
				'n'       => $number,
				'item_id' => (int) $hit['item_id'],
				'title'   => (string) get_the_title( (int) $hit['item_id'] ),
				'score'   => (float) $hit['score'],
				'source'  => isset( $snippet['source'] ) ? (string) $snippet['source'] : '',
				'content' => isset( $snippet['content'] ) ? (string) $snippet['content'] : '',
			];

			if ( count( $citations ) >= $sources ) {
				break;
			}
		}

		return $citations;
	}

	/**
	 * Build the grounded prompt. Pure, so the wording is testable.
	 *
	 * @param string $query     Question.
	 * @param array  $citations Numbered sources.
	 * @return string
	 */
	public function build_prompt( $query, array $citations ) {
		$lines = [
			'Answer the question using only the numbered sources below.',
			'Rules:',
			'- Cite the source number in square brackets after each statement, for example [1].',
			'- If the sources do not contain the answer, say so plainly. Do not guess.',
			'- Do not add facts that are not in the sources, even if you know them.',
			'',
			'Sources:',
		];

		foreach ( $citations as $citation ) {
			$lines[] = '';
			$lines[] = '[' . (int) $citation['n'] . '] ' . $citation['title'];
			$lines[] = $citation['content'];
		}

		$lines[] = '';
		$lines[] = 'Question: ' . trim( (string) $query );
		$lines[] = 'Answer:';

		return implode( "\n", $lines );
	}
}
