<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Turns an item into vectors, and removes them when the item stops being public.
 *
 * Three rules shape this class:
 *
 * 1. **Never embed inline on save.** Save enqueues; a worker drains. A provider
 *    round trip inside a save request blocks the editor and times out on an import.
 * 2. **Never re-embed unchanged content.** Every chunk carries a hash of its
 *    normalised text, and only chunks whose hash is absent get sent to the provider.
 *    Re-saving an item with one edited word must cost one chunk, not a document.
 * 3. **Remove immediately, do not wait for the worker.** Unpublish, trash and delete
 *    drop the vectors right away, because a stale vector is a result a user is no
 *    longer allowed to see.
 *
 * @since 1.5.0
 */
class Indexer {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Wire change capture.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'save_post', [ $this, 'on_save' ], 20, 3 );
		add_action( 'transition_post_status', [ $this, 'on_status_change' ], 20, 3 );
		add_action( 'before_delete_post', [ $this, 'on_delete' ], 20, 2 );
	}

	/**
	 * Whether a post type is a Tainacan item.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function is_item_post_type( $post_type ) {
		if ( ! class_exists( '\Tainacan\Entities\Collection' ) ) {
			return false;
		}

		$prefix = \Tainacan\Entities\Collection::$db_identifier_prefix;

		return is_string( $prefix ) && '' !== $prefix && 0 === strpos( (string) $post_type, $prefix );
	}

	/**
	 * Queue an item after a save.
	 *
	 * @param int      $post_id   Post id.
	 * @param \WP_Post $post      Post object.
	 * @param bool     $update    Whether this is an update.
	 * @return void
	 */
	public function on_save( $post_id, $post = null, $update = false ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$post_type = $post instanceof \WP_Post ? $post->post_type : get_post_type( $post_id );

		if ( ! $this->is_item_post_type( $post_type ) ) {
			return;
		}

		$space = Space_Repository::get_instance()->get_active( 'text' );

		if ( ! $space ) {
			return;
		}

		Index_Queue::get_instance()->enqueue( $space['id'], (int) $post_id );
	}

	/**
	 * Drop vectors as soon as an item stops being public.
	 *
	 * @param string   $new_status New status.
	 * @param string   $old_status Old status.
	 * @param \WP_Post $post       Post object.
	 * @return void
	 */
	public function on_status_change( $new_status, $old_status, $post ) {
		if ( ! $post instanceof \WP_Post || ! $this->is_item_post_type( $post->post_type ) ) {
			return;
		}

		if ( 'publish' === $new_status ) {
			// Becoming public: queue an index instead of removing anything.
			$this->on_save( $post->ID, $post, true );
			return;
		}

		if ( 'publish' === $old_status && 'publish' !== $new_status ) {
			$this->remove_item_everywhere( (int) $post->ID );
		}
	}

	/**
	 * Drop vectors when an item is deleted for good.
	 *
	 * @param int           $post_id Post id.
	 * @param \WP_Post|null $post    Post object.
	 * @return void
	 */
	public function on_delete( $post_id, $post = null ) {
		$post_type = $post instanceof \WP_Post ? $post->post_type : get_post_type( $post_id );

		if ( ! $this->is_item_post_type( $post_type ) ) {
			return;
		}

		$this->remove_item_everywhere( (int) $post_id );
	}

	/**
	 * Remove an item from every space.
	 *
	 * @param int $item_id Item id.
	 * @return void
	 */
	private function remove_item_everywhere( $item_id ) {
		foreach ( Space_Repository::get_instance()->get_spaces() as $space ) {
			$this->remove_item( $item_id, $space );
		}
	}

	/**
	 * Build the chunks for an item.
	 *
	 * Sources are the title, the description and the extracted document text. Item
	 * metadata values are not included yet: the item-metadata read API is a
	 * separate shape and guessing at it here would produce chunks that quietly omit
	 * half the record.
	 *
	 * @param int $item_id Item id.
	 * @return array
	 */
	public function build_chunks( $item_id ) {
		$item_id = (int) $item_id;
		$post    = get_post( $item_id );

		if ( ! $post instanceof \WP_Post ) {
			return [];
		}

		$chunker = new Chunker();
		$chunks  = [];

		$sources = [
			'title'   => $post->post_title,
			'content' => $post->post_content,
			'document' => (string) get_post_meta( $item_id, \Tainacan\Media::$content_index_meta, true ),
		];

		$index = 0;

		foreach ( $sources as $source => $text ) {
			foreach ( $chunker->chunk( $text ) as $content ) {
				$chunks[] = [
					'source'      => $source,
					'chunk_index' => $index,
					'content'     => $content,
					'hash'        => $chunker->hash( $content ),
				];
				$index++;
			}
		}

		return $chunks;
	}

	/**
	 * Decide what to embed and what to delete. Pure, so the diff is testable.
	 *
	 * @param array $chunks          Chunks built for the item.
	 * @param array $existing_hashes Hashes already stored for the item.
	 * @return array{to_embed: array, to_delete: array, unchanged: int}
	 */
	public function plan( array $chunks, array $existing_hashes ) {
		$existing = array_flip( array_map( 'strval', $existing_hashes ) );

		$to_embed = [];
		$keep     = [];

		foreach ( $chunks as $chunk ) {
			if ( isset( $existing[ $chunk['hash'] ] ) ) {
				$keep[ $chunk['hash'] ] = true;
				continue;
			}

			$to_embed[]                 = $chunk;
			$keep[ $chunk['hash'] ] = true;
		}

		$to_delete = [];

		foreach ( array_keys( $existing ) as $hash ) {
			if ( ! isset( $keep[ $hash ] ) ) {
				$to_delete[] = $hash;
			}
		}

		return [
			'to_embed'  => $to_embed,
			'to_delete' => $to_delete,
			'unchanged' => count( $chunks ) - count( $to_embed ),
		];
	}

	/**
	 * The collection an item belongs to.
	 *
	 * A Tainacan item is a post whose post type identifies its collection, so the
	 * mapping is the reverse of what the Collections repository already exposes.
	 * Returning 0 instead of throwing keeps an item that lost its collection from
	 * aborting a whole indexing run.
	 *
	 * @param int $item_id Item id.
	 * @return int
	 */
	public function collection_id_for( $item_id ) {
		$post = get_post( (int) $item_id );

		if ( ! $post instanceof \WP_Post ) {
			return 0;
		}

		$collection_id = \Tainacan\Repositories\Collections::get_instance()->get_id_by_db_identifier( $post->post_type );

		return $collection_id ? (int) $collection_id : 0;
	}

	/**
	 * Existing chunk hashes for one item in one space.
	 *
	 * @param int   $item_id Item id.
	 * @param array $space   Space row.
	 * @return string[]
	 */
	public function existing_hashes( $item_id, array $space ) {
		global $wpdb;

		if ( empty( $space['table'] ) ) {
			return [];
		}

		$table = $space['table'];
		$rows  = $wpdb->get_col( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry.
			$wpdb->prepare( "SELECT content_hash FROM {$table} WHERE item_id = %d", (int) $item_id )
		);

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Index one item into one space.
	 *
	 * @param int   $item_id Item id.
	 * @param array $space   Space row.
	 * @return array|\WP_Error
	 */
	public function index_item( $item_id, array $space ) {
		global $wpdb;

		if ( empty( $space['table'] ) ) {
			return new \WP_Error(
				'tainacan_rag_space_invalid',
				__( 'The space has no storage table.', 'tainacan' )
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

		$chunks = $this->build_chunks( $item_id );
		$plan   = $this->plan( $chunks, $this->existing_hashes( $item_id, $space ) );

		$table    = $space['table'];
		$embedded = 0;

		/*
		 * The collection id, not the space id. The table is already per space, so
		 * writing the space id here made every row carry the same value and turned
		 * the `collection_id IN (...)` scoping in Semantic_Search into a no-op that
		 * returned items from every collection. Rows indexed before this fix must be
		 * rebuilt: their collection_id holds a space id.
		 */
		$collection_id = $this->collection_id_for( $item_id );

		if ( ! empty( $plan['to_embed'] ) ) {
			$batch_size = max( 1, (int) $provider->get_max_batch() );

			foreach ( array_chunk( $plan['to_embed'], $batch_size ) as $batch ) {
				$result = $provider->embed(
					array_column( $batch, 'content' ),
					$space['model'],
					$space['modality']
				);

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				if ( count( $result['vectors'] ) !== count( $batch ) ) {
					return new \WP_Error(
						'tainacan_rag_vector_count_mismatch',
						__( 'The provider returned a different number of vectors than inputs.', 'tainacan' )
					);
				}

				foreach ( $batch as $position => $chunk ) {
					$vector = '[' . implode( ',', array_map( 'floatval', $result['vectors'][ $position ] ) ) . ']';

					$inserted = $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry, vector literal built from floats.
						$wpdb->prepare(
							"INSERT INTO {$table} (item_id, collection_id, source, chunk_index, content, content_hash, embedding, indexed_at)
							 VALUES (%d, %d, %s, %d, %s, %s, VEC_FromText(%s), %s)",
							(int) $item_id,
							(int) $collection_id,
							$chunk['source'],
							(int) $chunk['chunk_index'],
							$chunk['content'],
							$chunk['hash'],
							$vector,
							gmdate( 'Y-m-d H:i:s' )
						)
					);

					if ( false === $inserted ) {
						return new \WP_Error(
							'tainacan_rag_insert_failed',
							sprintf(
								/* translators: %s: database error */
								__( 'A chunk could not be stored: %s', 'tainacan' ),
								$wpdb->last_error
							)
						);
					}

					$embedded++;
				}
			}
		}

		if ( ! empty( $plan['to_delete'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $plan['to_delete'] ), '%s' ) );

			$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry, placeholders generated from the hash count.
				$wpdb->prepare(
					"DELETE FROM {$table} WHERE item_id = %d AND content_hash IN ({$placeholders})",
					array_merge( [ (int) $item_id ], $plan['to_delete'] )
				)
			);
		}

		return [
			'item_id'   => (int) $item_id,
			'embedded'  => $embedded,
			'deleted'   => count( $plan['to_delete'] ),
			'unchanged' => (int) $plan['unchanged'],
		];
	}

	/**
	 * Remove every chunk of an item from a space.
	 *
	 * @param int   $item_id Item id.
	 * @param array $space   Space row.
	 * @return int Rows removed.
	 */
	public function remove_item( $item_id, array $space ) {
		global $wpdb;

		if ( empty( $space['table'] ) ) {
			return 0;
		}

		$table  = $space['table'];
		$before = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry.
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE item_id = %d", (int) $item_id )
		);

		if ( $before > 0 ) {
			$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry.
				$wpdb->prepare( "DELETE FROM {$table} WHERE item_id = %d", (int) $item_id )
			);
		}

		return $before;
	}

	/**
	 * Enqueue one item for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_enqueue( $input = [] ) {
		$input   = is_array( $input ) ? $input : [];
		$item_id = isset( $input['item_id'] ) ? (int) $input['item_id'] : 0;

		if ( $item_id <= 0 ) {
			return new \WP_Error(
				'tainacan_rag_missing_item',
				__( 'An item_id is required.', 'tainacan' )
			);
		}

		$space = Space_Repository::get_instance()->get_active( 'text' );

		if ( ! $space ) {
			return new \WP_Error(
				'tainacan_rag_no_active_space',
				__( 'No text space is active. Create one and activate it before indexing.', 'tainacan' )
			);
		}

		return [
			'queued'   => (bool) Index_Queue::get_instance()->enqueue( $space['id'], $item_id ),
			'space_id' => (int) $space['id'],
			'item_id'  => $item_id,
		];
	}

	/**
	 * Drain the queue for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_run( $input = [] ) {
		$input = is_array( $input ) ? $input : [];
		$limit = isset( $input['limit'] ) ? (int) $input['limit'] : 10;

		$queue   = Index_Queue::get_instance();
		$breaker = Provider_Circuit_Breaker::get_instance();
		$pending = $queue->reserve( $limit );
		$results = [];
		$skipped = 0;
		$paused  = null;

		foreach ( $pending as $row ) {
			$space = Space_Repository::get_instance()->get_space( (int) $row['space_id'] );

			if ( ! $space ) {
				$queue->mark_failed( (int) $row['id'], __( 'Space no longer exists.', 'tainacan' ) );
				continue;
			}

			/*
			 * A paused provider+model pair stops the whole run. Row-level backoff
			 * cannot help when the provider is down: every row would retry and fail
			 * on its own schedule, producing a trickle of doomed requests.
			 */
			$pause = $breaker->is_paused( $space['provider'], $space['model'] );

			if ( $pause ) {
				$paused = $pause;
				$skipped = count( $pending ) - count( $results );
				break;
			}

			$result = $this->index_item( (int) $row['item_id'], $space );

			if ( is_wp_error( $result ) ) {
				$breaker->record_failure( $space['provider'], $space['model'], $result->get_error_message() );
				$queue->mark_failed( (int) $row['id'], $result->get_error_message() );
				$results[] = [
					'item_id' => (int) $row['item_id'],
					'error'   => $result->get_error_message(),
				];
				continue;
			}

			$breaker->record_success( $space['provider'], $space['model'] );
			$queue->mark_done( (int) $row['id'] );
			$results[] = $result;
		}

		return [
			'processed' => count( $results ),
			'skipped'   => (int) $skipped,
			'paused'    => $paused,
			'results'   => $results,
			'counts'    => $queue->counts(),
		];
	}
}
