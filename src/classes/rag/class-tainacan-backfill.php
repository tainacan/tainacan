<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Resumable per-collection backfill.
 *
 * Indexing existing content is never automatic: a feature has to ask for it. When it
 * does, the work is walked in batches with a persisted cursor, so an interrupted run
 * resumes where it stopped instead of starting over.
 *
 * The cursor is the highest item ID already queued, stored in an option per
 * space+collection pair. Using the ID rather than an offset is deliberate: an
 * offset walks a shifting list, and an import running alongside the backfill would
 * make it skip or repeat items.
 *
 * This enqueues only. Embedding stays in the worker, so a backfill cannot turn into
 * a long request that times out halfway with no record of where it stopped.
 *
 * @since 1.5.0
 */
class Backfill {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Option name prefix.
	 *
	 * @var string
	 */
	const OPTION_PREFIX = 'tainacan_rag_backfill_';

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Option key for a space and collection.
	 *
	 * @param int $space_id      Space id.
	 * @param int $collection_id Collection id.
	 * @return string
	 */
	public function option_key( $space_id, $collection_id ) {
		return self::OPTION_PREFIX . (int) $space_id . '_' . (int) $collection_id;
	}

	/**
	 * Start or restart a backfill.
	 *
	 * @param int $space_id      Space id.
	 * @param int $collection_id Collection id.
	 * @return array|\WP_Error
	 */
	public function start( $space_id, $collection_id ) {
		$space      = Space_Repository::get_instance()->get_space( (int) $space_id );
		$collection = $this->collection_post_type( (int) $collection_id );

		if ( ! $space ) {
			return new \WP_Error( 'tainacan_rag_space_not_found', __( 'No space exists with that id.', 'tainacan' ) );
		}

		if ( is_wp_error( $collection ) ) {
			return $collection;
		}

		$total = (int) $this->count_items( $collection );

		$state = [
			'space_id'      => (int) $space_id,
			'collection_id' => (int) $collection_id,
			'post_type'     => $collection,
			'cursor'        => 0,
			'total'         => $total,
			'enqueued'      => 0,
			'status'        => $total > 0 ? 'running' : 'complete',
			'started_at'    => gmdate( 'Y-m-d H:i:s' ),
			'updated_at'    => gmdate( 'Y-m-d H:i:s' ),
		];

		update_option( $this->option_key( $space_id, $collection_id ), $state, false );

		return $state;
	}

	/**
	 * Queue the next batch.
	 *
	 * @param int $space_id      Space id.
	 * @param int $collection_id Collection id.
	 * @param int $batch         Batch size.
	 * @return array|\WP_Error
	 */
	public function step( $space_id, $collection_id, $batch = 50 ) {
		global $wpdb;

		$key   = $this->option_key( $space_id, $collection_id );
		$state = get_option( $key, [] );

		if ( ! is_array( $state ) || empty( $state['post_type'] ) ) {
			return new \WP_Error(
				'tainacan_rag_backfill_not_started',
				__( 'This backfill has not been started. Call start first.', 'tainacan' )
			);
		}

		if ( isset( $state['status'] ) && 'complete' === $state['status'] ) {
			return $state;
		}

		$batch    = max( 1, min( 200, (int) $batch ) );
		$post_type = $state['post_type'];

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Core table name from $wpdb.
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND ID > %d ORDER BY ID ASC LIMIT %d",
				$post_type,
				(int) $state['cursor'],
				$batch
			)
		);

		$ids = is_array( $ids ) ? array_map( 'intval', $ids ) : [];

		$queue = Index_Queue::get_instance();

		foreach ( $ids as $item_id ) {
			$queue->enqueue( (int) $space_id, $item_id );
		}

		if ( ! empty( $ids ) ) {
			$state['cursor']   = max( $ids );
			$state['enqueued'] = (int) $state['enqueued'] + count( $ids );
		}

		$state['updated_at'] = gmdate( 'Y-m-d H:i:s' );

		/*
		 * A short batch means the end. A full batch does not: with exactly one
		 * batch of work left, the last step would report "running" and make an
		 * operator poll again for nothing. So when the batch was full, ask whether
		 * anything remains beyond the new cursor.
		 */
		$complete = count( $ids ) < $batch;

		if ( ! $complete && ! empty( $ids ) ) {
			$remaining = $wpdb->get_var( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Core table name from $wpdb.
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND ID > %d LIMIT 1",
					$post_type,
					(int) $state['cursor']
				)
			);

			$complete = empty( $remaining );
		}

		$state['status'] = $complete ? 'complete' : 'running';

		update_option( $key, $state, false );

		return $state;
	}

	/**
	 * Current state, or null.
	 *
	 * @param int $space_id      Space id.
	 * @param int $collection_id Collection id.
	 * @return array|null
	 */
	public function status( $space_id, $collection_id ) {
		$state = get_option( $this->option_key( $space_id, $collection_id ), [] );

		return is_array( $state ) && ! empty( $state ) ? $state : null;
	}

	/**
	 * Post type of a Tainacan collection.
	 *
	 * @param int $collection_id Collection id.
	 * @return string|\WP_Error
	 */
	private function collection_post_type( $collection_id ) {
		if ( $collection_id <= 0 ) {
			return new \WP_Error(
				'tainacan_rag_missing_collection',
				__( 'A collection_id is required. Backfill walks one collection at a time.', 'tainacan' )
			);
		}

		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( $collection_id );

		if ( ! $collection || ! method_exists( $collection, 'get_db_identifier' ) ) {
			return new \WP_Error(
				'tainacan_rag_collection_not_found',
				__( 'No collection exists with that id.', 'tainacan' )
			);
		}

		$post_type = $collection->get_db_identifier();

		if ( ! $post_type ) {
			return new \WP_Error(
				'tainacan_rag_collection_not_published',
				__( 'The collection has no storage yet, so it has no items to backfill.', 'tainacan' )
			);
		}

		return $post_type;
	}

	/**
	 * Published item count for a post type.
	 *
	 * @param string $post_type Post type.
	 * @return int
	 */
	private function count_items( $post_type ) {
		global $wpdb;

		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Core table name from $wpdb.
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				$post_type
			)
		);
	}

	/**
	 * Start for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_start( $input = [] ) {
		$input  = is_array( $input ) ? $input : [];
		$space  = Space_Repository::get_instance()->get_active( 'text' );
		$target = isset( $input['space_id'] ) ? (int) $input['space_id'] : ( $space ? (int) $space['id'] : 0 );

		if ( $target <= 0 ) {
			return new \WP_Error(
				'tainacan_rag_no_active_space',
				__( 'No text space is active. Create one and activate it before backfilling.', 'tainacan' )
			);
		}

		return $this->start( $target, isset( $input['collection_id'] ) ? (int) $input['collection_id'] : 0 );
	}

	/**
	 * Step for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_step( $input = [] ) {
		$input  = is_array( $input ) ? $input : [];
		$space  = Space_Repository::get_instance()->get_active( 'text' );
		$target = isset( $input['space_id'] ) ? (int) $input['space_id'] : ( $space ? (int) $space['id'] : 0 );

		if ( $target <= 0 ) {
			return new \WP_Error(
				'tainacan_rag_no_active_space',
				__( 'No text space is active.', 'tainacan' )
			);
		}

		return $this->step(
			$target,
			isset( $input['collection_id'] ) ? (int) $input['collection_id'] : 0,
			isset( $input['batch'] ) ? (int) $input['batch'] : 50
		);
	}
}
