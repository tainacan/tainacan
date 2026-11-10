<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * The queue of items waiting to be embedded.
 *
 * Embedding never happens inline on save. A provider round trip in a save request
 * would block the editor and time out on a batch import, so a change is recorded
 * here and a worker drains it.
 *
 * The queue is per space, not per item: two spaces mean two embeddings of the same
 * item, and they can fail independently.
 *
 * @since 1.5.0
 */
class Index_Queue {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Pending state.
	 *
	 * @var string
	 */
	const STATE_PENDING = 'pending';

	/**
	 * Done state, kept briefly for observability.
	 *
	 * @var string
	 */
	const STATE_DONE = 'done';

	/**
	 * Failed state.
	 *
	 * @var string
	 */
	const STATE_FAILED = 'failed';

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Queue table name.
	 *
	 * @return string
	 */
	public function table() {
		global $wpdb;

		return $wpdb->prefix . 'tainacan_vec_queue';
	}

	/**
	 * Create the queue table. Called once from a migration.
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;

		$table           = $this->table();
		$charset_collate = $wpdb->get_charset_collate();

		$query = "CREATE TABLE IF NOT EXISTS {$table} (
			id bigint(20) unsigned NOT NULL auto_increment,
			space_id bigint(20) unsigned NOT NULL,
			item_id bigint(20) unsigned NOT NULL,
			state varchar(16) NOT NULL default 'pending',
			attempts smallint(5) unsigned NOT NULL default 0,
			available_at datetime NOT NULL,
			last_error text NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY space_state (space_id, state, available_at),
			KEY item (item_id)
		) {$charset_collate};\n";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Static DDL, only prefix and charset are interpolated.
		$wpdb->query( $query );
	}

	/**
	 * Record that an item needs embedding in a space.
	 *
	 * Idempotent: an item already pending is not queued twice, so a burst of
	 * metadata saves does not produce a burst of embedding calls.
	 *
	 * @param int $space_id Space id.
	 * @param int $item_id  Item id.
	 * @return bool
	 */
	public function enqueue( $space_id, $item_id ) {
		global $wpdb;

		$space_id = (int) $space_id;
		$item_id  = (int) $item_id;

		if ( $space_id <= 0 || $item_id <= 0 ) {
			return false;
		}

		$table = $this->table();

		$existing = $wpdb->get_var( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE space_id = %d AND item_id = %d AND state = %s LIMIT 1",
				$space_id,
				$item_id,
				self::STATE_PENDING
			)
		);

		if ( $existing ) {
			return true;
		}

		$now = gmdate( 'Y-m-d H:i:s' );

		$inserted = $wpdb->insert(
			$table,
			[
				'space_id'     => $space_id,
				'item_id'      => $item_id,
				'state'        => self::STATE_PENDING,
				'attempts'     => 0,
				'available_at' => $now,
				'created_at'   => $now,
			],
			[ '%d', '%d', '%s', '%d', '%s', '%s' ]
		);

		return (bool) $inserted;
	}

	/**
	 * Take up to $limit due rows.
	 *
	 * @param int $limit Maximum rows.
	 * @return array
	 */
	public function reserve( $limit = 10 ) {
		global $wpdb;

		$table = $this->table();
		$limit = max( 1, (int) $limit );

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE state = %s AND available_at <= %s ORDER BY id ASC LIMIT %d",
				self::STATE_PENDING,
				gmdate( 'Y-m-d H:i:s' ),
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Mark a row done.
	 *
	 * @param int $id Row id.
	 * @return void
	 */
	public function mark_done( $id ) {
		global $wpdb;

		$wpdb->update(
			$this->table(),
			[
				'state'      => self::STATE_DONE,
				'last_error' => null,
			],
			[ 'id' => (int) $id ],
			[ '%s', '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Mark a row failed, backing off so a broken provider cannot hot-loop.
	 *
	 * @param int    $id    Row id.
	 * @param string $error Failure message.
	 * @return void
	 */
	public function mark_failed( $id, $error = '' ) {
		global $wpdb;

		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.
			$wpdb->prepare( 'SELECT * FROM ' . $this->table() . ' WHERE id = %d', (int) $id ),
			ARRAY_A
		);

		$attempts = is_array( $row ) && isset( $row['attempts'] ) ? (int) $row['attempts'] + 1 : 1;

		// Exponential minutes: 1, 2, 4, 8, capped at an hour.
		$delay = min( 60, (int) pow( 2, min( 6, $attempts - 1 ) ) );
		$next  = gmdate( 'Y-m-d H:i:s', time() + ( $delay * MINUTE_IN_SECONDS ) );

		$wpdb->update(
			$this->table(),
			[
				'state'        => self::STATE_FAILED,
				'attempts'     => $attempts,
				'available_at' => $next,
				'last_error'   => substr( (string) $error, 0, 1000 ),
			],
			[ 'id' => (int) $id ],
			[ '%s', '%d', '%s', '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Counts by state.
	 *
	 * @return array<string, int>
	 */
	public function counts() {
		global $wpdb;

		$table = $this->table();
		$rows  = $wpdb->get_results( "SELECT state, COUNT(*) AS total FROM {$table} GROUP BY state", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.

		$counts = [
			self::STATE_PENDING => 0,
			self::STATE_DONE    => 0,
			self::STATE_FAILED  => 0,
		];

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$counts[ $row['state'] ] = (int) $row['total'];
			}
		}
	
		return $counts;
	}

	/**
	 * The most recent failures, with why they failed.
	 *
	 * A count alone tells an operator that something is wrong; the error text is what
	 * tells them whether to fix a key, a model name or a container. Capped because
	 * this feeds a screen, not an export.
	 *
	 * @param int $limit Maximum rows.
	 * @return array
	 */
	public function failed_rows( $limit = 20 ) {
		global $wpdb;

		$table = $this->table();
		$limit = max( 1, min( 100, (int) $limit ) );

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.
			$wpdb->prepare(
				"SELECT id, space_id, item_id, attempts, available_at, last_error FROM {$table} WHERE state = %s ORDER BY id DESC LIMIT %d",
				self::STATE_FAILED,
				$limit
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return [];
		}

		return array_map(
			static function ( $row ) {
				return [
					'id'           => (int) $row['id'],
					'space_id'     => (int) $row['space_id'],
					'item_id'      => (int) $row['item_id'],
					'attempts'     => (int) $row['attempts'],
					'available_at' => (string) $row['available_at'],
					'error'        => (string) $row['last_error'],
				];
			},
			$rows
		);
	}

	/**
	 * Put failed rows back in the queue.
	 *
	 * The attempt counter is deliberately kept: a row that has already failed five
	 * times should not look new, and its backoff stays honest if it fails again. Only
	 * the state and the availability change, which is what makes this safe to press
	 * twice — the second press finds nothing left in `failed`.
	 *
	 * @param int $limit Maximum rows to requeue.
	 * @return int Rows requeued.
	 */
	public function retry_failed( $limit = 50 ) {
		global $wpdb;

		$table = $this->table();
		$limit = max( 1, min( 500, (int) $limit ) );
		$now   = gmdate( 'Y-m-d H:i:s' );

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE state = %s ORDER BY id ASC LIMIT %d",
				self::STATE_FAILED,
				$limit
			)
		);

		if ( empty( $ids ) ) {
			return 0;
		}

		$ids          = array_map( 'intval', $ids );
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		$updated = $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix, placeholders from the id count.
			$wpdb->prepare(
				"UPDATE {$table} SET state = %s, available_at = %s WHERE id IN ({$placeholders})",
				array_merge( [ self::STATE_PENDING, $now ], $ids )
			)
		);

		return false === $updated ? 0 : (int) $updated;
	}

	/**
		* Remove every queue row for a space.
		*
		* Called when a space is deleted. Without it the rows outlive their space, fail
		* with "space no longer exists", and cannot be requeued into anything — a queue
		* that fills with work that can never succeed. Found by clicking the console,
		* which listed exactly that.
		*
		* @param int $space_id Space id.
		* @return int Rows deleted.
		*/
	public function delete_for_space( $space_id ) {
		global $wpdb;

		$space_id = (int) $space_id;

		if ( $space_id <= 0 ) {
			return 0;
		}

		$table = $this->table();

		$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from prefix.
			$wpdb->prepare( "DELETE FROM {$table} WHERE space_id = %d", $space_id )
		);

		return false === $deleted ? 0 : (int) $deleted;
	}
}
