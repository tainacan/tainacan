<?php
namespace Tainacan\Helpers;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Repositories;

/**
 * Handles migration of legacy Tainacan logs from wp_posts/wp_postmeta
 * to the dedicated tainacan_logs table.
 *
 * Designed to be called from both WP-CLI commands (Cli_Logs) and background
 * processes (GenericProcess\Management_Tools_Process).
 * Uses _wp_posts_log_migration_ref as an idempotency key so both operations
 * can be run multiple times safely without side effects.
 * 
 */
class Logs_Wp_Posts_Migration {

	/**
	 * Count how many legacy tainacan-log posts have not yet been migrated.
	 *
	 * @return int
	 */
	public function count_pending(): int {
		global $wpdb;
		$table = Repositories\Logs::get_instance()->get_table_name();
		return (int) $wpdb->get_var(
			"SELECT COUNT(p.ID) FROM {$wpdb->posts} p
			 LEFT JOIN {$table} l ON l._wp_posts_log_migration_ref = p.ID
			 WHERE p.post_type = 'tainacan-log' AND l.ID IS NULL"
		);
	}

	/**
	 * Migrate a single batch of legacy logs to tainacan_logs.
	 *
	 * Uses LEFT JOIN on _wp_posts_log_migration_ref so already-migrated
	 * records are automatically excluded — safe to call repeatedly.
	 *
	 * @param int $batch_size Number of records to process in this call.
	 * @return array { migrated: int, pending: int }
	 */
	public function run_batch( int $batch_size = 50 ): array {
		global $wpdb;
		$table = Repositories\Logs::get_instance()->get_table_name();

		// 1. Fetch a batch of not-yet-migrated wp_posts logs.
		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_content, p.post_date, p.post_author, p.post_name
			 FROM {$wpdb->posts} p
			 LEFT JOIN {$table} l ON l._wp_posts_log_migration_ref = p.ID
			 WHERE p.post_type = 'tainacan-log' AND l.ID IS NULL
			 LIMIT %d",
			$batch_size
		) );

		if ( empty( $posts ) ) {
			return [ 'migrated' => 0, 'pending' => 0 ];
		}

		// 2. Fetch all relevant postmeta for the batch in one query.
		$ids    = array_column( $posts, 'ID' );
		$in_sql = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$metas  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
				 WHERE post_id IN ($in_sql)
				 AND meta_key IN ('item_id','collection_id','object_id','object_type','old_value','new_value','action')",
				...$ids
			)
		);

		// 3. Index postmeta by post_id for O(1) lookup.
		$meta_map = [];
		foreach ( $metas as $meta ) {
			$meta_map[ $meta->post_id ][ $meta->meta_key ] = $meta->meta_value;
		}

		// 4. Insert each post into tainacan_logs with the migration reference.
		$migrated = 0;
		foreach ( $posts as $post ) {
			$m      = $meta_map[ $post->ID ] ?? [];
			$result = $wpdb->insert(
				$table,
				[
					'title'                       => $post->post_title,
					'date'                        => $post->post_date,
					'description'                 => $post->post_content,
					'slug'                        => $post->post_name ?: uniqid( 'tainacan-log-' ),
					'user_id'                     => absint( $post->post_author ),
					'item_id'                     => absint( $m['item_id'] ?? 0 ),
					'collection_id'               => $m['collection_id'] ?? '',
					'object_id'                   => $m['object_id'] ?? '',
					'object_type'                 => $m['object_type'] ?? '',
					'old_value'                   => $m['old_value'] ?? null,
					'new_value'                   => $m['new_value'] ?? null,
					'action'                      => $m['action'] ?? '',
					'user_edit_lastr'             => absint( $post->post_author ),
					'_wp_posts_log_migration_ref' => absint( $post->ID ),
				],
				[ '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ]
			);

			if ( false !== $result ) {
				$migrated++;
			}
		}

		return [ 'migrated' => $migrated, 'pending' => $this->count_pending() ];
	}

	/**
	 * Count how many already-migrated wp_posts log records are eligible for purging.
	 *
	 * @return int
	 */
	public function count_purgeable(): int {
		global $wpdb;
		$table = Repositories\Logs::get_instance()->get_table_name();
		return (int) $wpdb->get_var(
			"SELECT COUNT(p.ID) FROM {$wpdb->posts} p
			 INNER JOIN {$table} l ON l._wp_posts_log_migration_ref = p.ID
			 WHERE p.post_type = 'tainacan-log'"
		);
	}

	/**
	 * Delete a batch of already-migrated tainacan-log records from wp_posts and wp_postmeta.
	 *
	 * Only removes records whose ID is present in tainacan_logs._wp_posts_log_migration_ref,
	 * so non-migrated logs are never touched. Safe to call repeatedly.
	 *
	 * @param int $batch_size Number of records to delete in this call.
	 * @return array { deleted: int, pending: int }
	 */
	public function purge_batch( int $batch_size = 50 ): array {
		global $wpdb;
		$table = Repositories\Logs::get_instance()->get_table_name();

		// 1. Fetch a batch of migrated post IDs to purge.
		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$table} l ON l._wp_posts_log_migration_ref = p.ID
			 WHERE p.post_type = 'tainacan-log'
			 LIMIT %d",
			$batch_size
		) );

		if ( empty( $ids ) ) {
			return [ 'deleted' => 0, 'pending' => 0 ];
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$ids          = array_map( 'absint', $ids );

		// 2. Delete postmeta first to avoid orphaned rows.
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $placeholders is a generated list of %d tokens.
			$ids
		) );

		// 3. Delete the wp_posts records.
		$deleted = $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->posts} WHERE ID IN ($placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $placeholders is a generated list of %d tokens.
			$ids
		) );

		return [ 'deleted' => (int) $deleted, 'pending' => $this->count_purgeable() ];
	}
}
