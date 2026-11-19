<?php
namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use WP_CLI;

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
		$migration  = new Helpers\Logs_Wp_Posts_Migration();
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
		$migration  = new Helpers\Logs_Wp_Posts_Migration();
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
