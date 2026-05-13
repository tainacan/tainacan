<?php
namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use WP_CLI;

/**
 * Handles migration of legacy Tainacan logs from wp_posts/wp_postmeta
 * to the dedicated tainacan_logs table.
 *
 * Designed to be called from both WP-CLI commands and Action Scheduler jobs.
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

		$in_sql = implode( ',', array_map( 'absint', $ids ) );

		// 2. Delete postmeta first to avoid orphaned rows.
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($in_sql)" );

		// 3. Delete the wp_posts records.
		$deleted = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID IN ($in_sql)" );

		return [ 'deleted' => (int) $deleted, 'pending' => $this->count_purgeable() ];
	}
}

/**
 * Manages Tainacan activity log data migration from the legacy wp_posts structure.
 *
 * wp tainacan logs migrate          # migra os logs do wp_posts → tainacan_logs
 * wp tainacan logs migrate --dry-run
 * wp tainacan logs migrate --batch-size=100 --yes
 * 
 * wp tainacan logs purge-deprecated            # apaga os registros já migrados do wp_posts
 * wp tainacan logs purge-deprecated --dry-run
 * wp tainacan logs purge-deprecated --batch-size=200 --yes
 */
class Cli_Logs {

	/**
	 * Migrate Tainacan activity logs from the legacy wp_posts structure to the dedicated tainacan_logs table.
	 *
	 * Safe to run multiple times. Already-migrated records are skipped automatically
	 * (tracked via _wp_posts_log_migration_ref).
	 *
	 * ## OPTIONS
	 *
	 * [--batch-size=<number>]
	 * : Number of logs to process per batch. Default: 50.
	 *
	 * [--dry-run]
	 * : Show how many logs are pending migration without making any changes.
	 *
	 * [--yes]
	 * : Skip the confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tainacan logs migrate
	 *     wp tainacan logs migrate --batch-size=100 --yes
	 *     wp tainacan logs migrate --dry-run
	 */
	public function migrate( $args, $assoc_args ) {
		$migration  = new Logs_Wp_Posts_Migration();
		$dry_run    = isset( $assoc_args['dry-run'] );
		$batch_size = max( 1, absint( $assoc_args['batch-size'] ?? 50 ) );

		$pending = $migration->count_pending();

		if ( 0 === $pending ) {
			\WP_CLI::success( 'No legacy logs found. Nothing to migrate.' );
			return;
		}

		\WP_CLI::line( sprintf( 'Found %d log(s) pending migration.', $pending ) );

		if ( $dry_run ) {
			\WP_CLI::warning( 'Dry-run mode: no changes were made. Remove --dry-run to execute.' );
			return;
		}

		\WP_CLI::warning( 'It is strongly recommended you do a backup before running this command.' );
		\WP_CLI::confirm( sprintf( 'Migrate %d log(s) in batches of %d?', $pending, $batch_size ), $assoc_args );

		$total_migrated = 0;
		$progress       = \WP_CLI\Utils\make_progress_bar( 'Migrating logs', $pending );

		do {
			$result          = $migration->run_batch( $batch_size );
			$total_migrated += $result['migrated'];
			$progress->tick( $result['migrated'] );
		} while ( $result['pending'] > 0 && $result['migrated'] > 0 );

		$progress->finish();
		\WP_CLI::success( sprintf( 'Migration complete. %d log(s) migrated.', $total_migrated ) );
	}

	/**
	 * Delete legacy tainacan-log records from wp_posts/wp_postmeta that have already been migrated to tainacan_logs.
	 *
	 * Only records whose wp_posts ID is referenced in tainacan_logs._wp_posts_log_migration_ref
	 * are removed. Non-migrated logs are never touched.
	 * Safe to run multiple times.
	 *
	 * ## OPTIONS
	 *
	 * [--batch-size=<number>]
	 * : Number of records to delete per batch. Default: 50.
	 *
	 * [--dry-run]
	 * : Show how many records are eligible for deletion without making any changes.
	 *
	 * [--yes]
	 * : Skip the confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tainacan logs purge-deprecated
	 *     wp tainacan logs purge-deprecated --batch-size=200 --yes
	 *     wp tainacan logs purge-deprecated --dry-run
	 */
	public function purge_deprecated( $args, $assoc_args ) {
		$migration  = new Logs_Wp_Posts_Migration();
		$dry_run    = isset( $assoc_args['dry-run'] );
		$batch_size = max( 1, absint( $assoc_args['batch-size'] ?? 50 ) );

		$purgeable = $migration->count_purgeable();

		if ( 0 === $purgeable ) {
			\WP_CLI::success( 'No migrated legacy logs found. Nothing to purge.' );
			return;
		}

		\WP_CLI::line( sprintf( 'Found %d migrated log(s) in wp_posts eligible for deletion.', $purgeable ) );

		if ( $dry_run ) {
			\WP_CLI::warning( 'Dry-run mode: no changes were made. Remove --dry-run to execute.' );
			return;
		}

		\WP_CLI::warning( 'This will permanently delete records from wp_posts and wp_postmeta. It is strongly recommended you do a backup before running this command.' );
		\WP_CLI::confirm( sprintf( 'Delete %d legacy log record(s) from wp_posts in batches of %d?', $purgeable, $batch_size ), $assoc_args );

		$total_deleted = 0;
		$progress      = \WP_CLI\Utils\make_progress_bar( 'Purging legacy logs', $purgeable );

		do {
			$result        = $migration->purge_batch( $batch_size );
			$total_deleted += $result['deleted'];
			$progress->tick( $result['deleted'] );
		} while ( $result['pending'] > 0 && $result['deleted'] > 0 );

		$progress->finish();
		\WP_CLI::success( sprintf( 'Purge complete. %d legacy log record(s) deleted from wp_posts.', $total_deleted ) );
	}
}
