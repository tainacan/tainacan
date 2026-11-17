<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * The facts an operator needs before touching anything, in one read-only call.
 *
 * A semantic index fails in ways that look like nothing: a space whose table was
 * dropped, a queue quietly full of failures, an index that covers one collection out
 * of six. None of those raise an error, and all of them are visible in a handful of
 * counts. This class collects those counts and nothing else — it never mutates state
 * except through the one queue action it exposes on purpose.
 *
 * Two rules shape the queries here:
 *
 * 1. **Never assume the table exists.** A registry row outlives its table whenever
 *    someone drops it by hand, so every read of a space table is suppressed and
 *    reported as `available: false` instead of emitting database errors on a screen.
 * 2. **Counts, not row lists.** Everything is a GROUP BY with a small cap, because
 *    this feeds a page that reloads, not an export.
 *
 * @since 1.5.0
 */
class Status {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Failed rows shown at once.
	 *
	 * @var int
	 */
	const FAILED_PREVIEW = 10;

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Ability callback: the whole report.
	 *
	 * @param mixed $input Ability input.
	 * @return array
	 */
	public function for_ability( $input = [] ) {
		return $this->report();
	}

	/**
	 * Ability callback: put failed queue rows back in line.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_retry_queue( $input = [] ) {
		$input = is_array( $input ) ? $input : [];
		$limit = isset( $input['limit'] ) && (int) $input['limit'] > 0 ? (int) $input['limit'] : 50;

		$requeued = Index_Queue::get_instance()->retry_failed( $limit );

		return [
			'requeued' => (int) $requeued,
			'queue'    => Index_Queue::get_instance()->counts(),
		];
	}

	/**
	 * The full report.
	 *
	 * @return array
	 */
	public function report() {
		$spaces = Space_Repository::get_instance()->get_spaces();

		$queue_by_space = $this->queue_by_space();

		$space_rows = [];

		foreach ( $spaces as $space ) {
			$space_rows[] = $this->space_status( $space, isset( $queue_by_space[ $space['id'] ] ) ? $queue_by_space[ $space['id'] ] : [] );
		}

		$active_text  = Space_Repository::get_instance()->get_active( 'text' );
		$active_image = Space_Repository::get_instance()->get_active( 'image' );

		return [
			'environment'  => $this->environment(),
			'spaces'       => $space_rows,
			'active'       => [
				'text'  => $active_text ? (int) $active_text['id'] : null,
				'image' => $active_image ? (int) $active_image['id'] : null,
			],
			'queue'        => Index_Queue::get_instance()->counts(),
			'paused'       => $this->paused(),
			'failed'       => Index_Queue::get_instance()->failed_rows( self::FAILED_PREVIEW ),
			'generated_at' => time(),
		];
	}

	/**
	 * One space: whether its table and index survive, what it holds, what it covers.
	 *
	 * @param array $space            Space row.
	 * @param array $queue_counts     Queue counts for this space.
	 * @return array
	 */
	public function space_status( array $space, array $queue_counts = [] ) {
		$storage = $this->storage_estimate( $space );

		return [
			'id'         => (int) $space['id'],
			'key'        => (string) $space['key'],
			'provider'   => (string) $space['provider'],
			'model'      => (string) $space['model'],
			'modality'   => (string) $space['modality'],
			'dimensions' => (int) $space['dimensions'],
			'is_active'  => ! empty( $space['is_active'] ),
			'status'     => (string) $space['status'],
			'created_at' => (string) $space['created_at'],
			'table'      => (string) $space['table'],
			'has_index'  => $storage['available'] ? Space_Repository::get_instance()->has_vector_index( $space['table'] ) : null,
			'storage'    => $storage,
			'queue'      => [
				'pending' => isset( $queue_counts['pending'] ) ? (int) $queue_counts['pending'] : 0,
				'failed'  => isset( $queue_counts['failed'] ) ? (int) $queue_counts['failed'] : 0,
				'done'    => isset( $queue_counts['done'] ) ? (int) $queue_counts['done'] : 0,
			],
			'coverage'   => $this->coverage( $space ),
		];
	}

	/**
	 * Rows, text bytes and vector bytes for a space.
	 *
	 * The vector size is `chunks × dimensions × 4`, because MariaDB stores a VECTOR
	 * as float32. That is an estimate of the column, not of the table: indexes, the
	 * primary key and page overhead are not modelled, which is why the server's own
	 * `information_schema` figure is reported next to it when available.
	 *
	 * @param array $space Space row.
	 * @return array
	 */
	public function storage_estimate( array $space ) {
		global $wpdb;

		$empty = [
			'available'       => false,
			'chunks'          => 0,
			'items'           => 0,
			'content_bytes'   => 0,
			'vector_bytes'    => 0,
			'table_bytes'     => null,
			'error'           => '',
		];

		if ( empty( $space['table'] ) ) {
			$empty['error'] = __( 'The space has no storage table.', 'tainacan' );
			return $empty;
		}

		$table    = $space['table'];
		$previous = $wpdb->suppress_errors( true );

		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry.
			"SELECT COUNT(*) AS chunks, COUNT(DISTINCT item_id) AS items, COALESCE(SUM(LENGTH(content)), 0) AS content_bytes FROM {$table}",
			ARRAY_A
		);

		$error = $wpdb->last_error;

		if ( ! is_array( $row ) ) {
			$wpdb->suppress_errors( $previous );

			$empty['error'] = $error ? $error : __( 'The space table could not be read.', 'tainacan' );

			return $empty;
		}

		$chunks = (int) $row['chunks'];

		$measured = $wpdb->get_var( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry.
			$wpdb->prepare(
				'SELECT data_length + index_length FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = %s',
				$table
			)
		);

		$wpdb->suppress_errors( $previous );

		return [
			'available'     => true,
			'chunks'        => $chunks,
			'items'         => (int) $row['items'],
			'content_bytes' => (int) $row['content_bytes'],
			'vector_bytes'  => $chunks * (int) $space['dimensions'] * 4,
			'table_bytes'   => null === $measured ? null : (int) $measured,
			'error'         => '',
		];
	}

	/**
	 * Per collection: how many published items exist, how many are indexed.
	 *
	 * A collection with zero published items is omitted, because "0 of 0" is noise on
	 * a screen whose job is to point at gaps.
	 *
	 * @param array $space Space row.
	 * @return array
	 */
	public function coverage( array $space ) {
		global $wpdb;

		$published = $this->published_counts();

		if ( empty( $published ) ) {
			return [];
		}

		$indexed = [];

		if ( ! empty( $space['table'] ) ) {
			$previous = $wpdb->suppress_errors( true );
			$table    = $space['table'];

			$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from the registry.
				"SELECT collection_id, COUNT(DISTINCT item_id) AS indexed FROM {$table} GROUP BY collection_id",
				ARRAY_A
			);

			$wpdb->suppress_errors( $previous );

			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					$indexed[ (int) $row['collection_id'] ] = (int) $row['indexed'];
				}
			}
		}

		$collections = \Tainacan\Repositories\Collections::get_instance();
		$coverage    = [];

		foreach ( $published as $post_type => $total ) {
			$collection_id = (int) $collections->get_id_by_db_identifier( $post_type );

			if ( $collection_id <= 0 ) {
				continue;
			}

			$done = isset( $indexed[ $collection_id ] ) ? (int) $indexed[ $collection_id ] : 0;

			$coverage[] = [
				'collection_id' => $collection_id,
				'name'          => (string) get_the_title( $collection_id ),
				'post_type'     => (string) $post_type,
				'published'     => (int) $total,
				'indexed'       => min( $done, (int) $total ),
				'missing'       => max( 0, (int) $total - $done ),
			];
		}

		return $coverage;
	}

	/**
	 * Published item counts keyed by post type.
	 *
	 * @return array<string, int>
	 */
	protected function published_counts() {
		global $wpdb;

		$prefix = \Tainacan\Entities\Collection::$db_identifier_prefix;

		if ( ! is_string( $prefix ) || '' === $prefix ) {
			return [];
		}

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static query, prefix passed as a parameter.
			$wpdb->prepare(
				"SELECT post_type, COUNT(*) AS total FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type LIKE %s GROUP BY post_type",
				$wpdb->esc_like( $prefix ) . '%'
			),
			ARRAY_A
		);

		$counts = [];

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$counts[ (string) $row['post_type'] ] = (int) $row['total'];
			}
		}

		return $counts;
	}

	/**
	 * Queue counts per space.
	 *
	 * @return array<int, array<string, int>>
	 */
	protected function queue_by_space() {
		global $wpdb;

		$table = Index_Queue::get_instance()->table();

		$rows = $wpdb->get_results( "SELECT space_id, state, COUNT(*) AS total FROM {$table} GROUP BY space_id, state", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.

		$by_space = [];

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$by_space[ (int) $row['space_id'] ][ (string) $row['state'] ] = (int) $row['total'];
			}
		}

		return $by_space;
	}

	/**
	 * Paused provider+model pairs, with the time left.
	 *
	 * @return array
	 */
	public function paused() {
		$state = Provider_Circuit_Breaker::get_instance()->get_state();
		$now   = time();
		$paused = [];

		foreach ( $state as $key => $entry ) {
			$until = isset( $entry['paused_until'] ) ? (int) $entry['paused_until'] : 0;

			if ( $until <= $now ) {
				continue;
			}

			$paused[] = [
				'key'              => (string) $key,
				'seconds_remaining' => $until - $now,
				'error'            => isset( $entry['error'] ) ? (string) $entry['error'] : '',
			];
		}

		return $paused;
	}

	/**
	 * The environment verdict, trimmed for a panel.
	 *
	 * @return array
	 */
	protected function environment() {
		$verdict = Environment_Check::get_instance()->get_verdict();

		return [
			'status'         => isset( $verdict['status'] ) ? (string) $verdict['status'] : 'unknown',
			'db_version'     => isset( $verdict['db_version'] ) ? (string) $verdict['db_version'] : '',
			'max_dimensions' => isset( $verdict['max_dimensions'] ) ? (int) $verdict['max_dimensions'] : 0,
			'checked_at'     => isset( $verdict['checked_at'] ) ? (int) $verdict['checked_at'] : 0,
		];
	}
}
