<?php

namespace Tainacan\Tests;

/**
 * Tests for the migrations in src/migrations.php
 *
 * @package Test_Tainacan
 * @group migrations
 */
class Test_Migrations extends TAINACAN_UnitTestCase {

	/**
	 * Returns the column names of the bg process table, in the current database only.
	 */
	private function get_bg_process_columns() {
		global $wpdb;

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = %s AND table_name = %s",
				$wpdb->dbname,
				$wpdb->prefix . 'tnc_bg_process'
			)
		);
	}

	/**
	 * assure_bg_process_database() must recreate columns that are missing
	 * in the table of the current database.
	 */
	function test_assure_bg_process_database_recreates_missing_columns() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'tnc_bg_process';

		$this->assertContains( 'progress_label', $this->get_bg_process_columns() );
		$this->assertContains( 'progress_value', $this->get_bg_process_columns() );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- DDL in test, only the table prefix is interpolated.
		$wpdb->query( "ALTER TABLE $table_name DROP COLUMN progress_label, DROP COLUMN progress_value" );

		$columns = $this->get_bg_process_columns();
		$this->assertNotContains( 'progress_label', $columns, 'Test setup failed: column was not dropped' );
		$this->assertNotContains( 'progress_value', $columns, 'Test setup failed: column was not dropped' );

		\Tainacan\Migrations::assure_bg_process_database();

		$columns = $this->get_bg_process_columns();
		$this->assertContains( 'progress_label', $columns, 'progress_label column should be recreated by the migration' );
		$this->assertContains( 'progress_value', $columns, 'progress_value column should be recreated by the migration' );
		$this->assertContains( 'name', $columns );
		$this->assertContains( 'output', $columns );
		$this->assertContains( 'status', $columns );
	}

	/**
	 * Running assure_bg_process_database() on an up to date table must be a no-op
	 * (no duplicate column errors).
	 */
	function test_assure_bg_process_database_is_idempotent() {
		global $wpdb;

		$before = $this->get_bg_process_columns();

		// wpdb::query() resets last_error on every call, so record the statements instead.
		$alter_statements = [];
		$recorder = function( $query ) use ( &$alter_statements ) {
			if ( stripos( $query, 'ALTER TABLE' ) !== false ) {
				$alter_statements[] = $query;
			}
			return $query;
		};
		add_filter( 'query', $recorder );
		\Tainacan\Migrations::assure_bg_process_database();
		remove_filter( 'query', $recorder );

		$this->assertSame( [], $alter_statements, 'No ALTER TABLE should run when every column already exists' );
		$this->assertEqualsCanonicalizing( $before, $this->get_bg_process_columns() );
	}

	/**
	 * insert_meta_default_metadata_section() must only add the metadata_section_id
	 * meta to metadata that do not have one yet, never duplicating existing rows.
	 */
	function test_insert_meta_default_metadata_section_does_not_duplicate() {
		global $wpdb;

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'Migrations collection'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'                => 'With section',
				'collection'          => $collection,
				'metadata_type'       => 'Tainacan\Metadata_Types\Text',
				'status'              => 'publish',
				'metadata_section_id' => \Tainacan\Entities\Metadata_Section::$default_section_slug
			),
			true
		);

		$orphan = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Without section',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish'
			),
			true
		);

		// Simulate a metadatum created before metadata sections existed.
		delete_post_meta( $orphan->get_id(), 'metadata_section_id' );

		$this->assertCount( 1, get_post_meta( $metadatum->get_id(), 'metadata_section_id' ) );
		$this->assertCount( 0, get_post_meta( $orphan->get_id(), 'metadata_section_id' ) );

		\Tainacan\Migrations::insert_meta_default_metadata_section();
		// The migration writes straight to the database, so the object cache must be flushed before reading back.
		wp_cache_flush();

		$this->assertCount( 1, get_post_meta( $metadatum->get_id(), 'metadata_section_id' ), 'Metadatum that already had a section must not get a duplicate row' );
		$this->assertSame(
			[ \Tainacan\Entities\Metadata_Section::$default_section_slug ],
			get_post_meta( $orphan->get_id(), 'metadata_section_id' ),
			'Metadatum without a section must get exactly one row with the default section'
		);

		// No metadatum in the whole database may end up with more than one metadata_section_id row.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- only the postmeta table name is interpolated.
		$duplicates = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'metadata_section_id' GROUP BY post_id HAVING COUNT(*) > 1
			) AS dup"
		);
		$this->assertEquals( 0, $duplicates );
	}

	/**
	 * run_migrations() must run and record every public migration, and must never
	 * treat private helpers as migrations.
	 */
	function test_run_migrations_records_migrations_and_skips_helpers() {
		delete_option( '_migration_refresh_rewrite_rules_items' );
		$this->assertFalse( get_option( '_migration_refresh_rewrite_rules_items' ) );

		\Tainacan\Migrations::run_migrations();

		$this->assertEquals( 1, get_option( '_migration_refresh_rewrite_rules_items' ) );
		// The private helper must never be treated as a migration.
		$this->assertFalse( get_option( '_migration_column_exists' ) );
	}
}
