<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Registry of vector spaces, and the owner of the per-space chunk tables.
 *
 * A "space" is provider + model + modality + dimensions. That is the unit
 * everything else keys on, because vectors from different models are not
 * comparable even when their dimensions match: a query belongs to exactly one
 * space, and mixing them returns nonsense that looks like a ranking.
 *
 * Storage shape and why it is not one table with many vector columns:
 *
 * - VECTOR(n) needs a literal dimension in the column type, so a shared table
 *   cannot hold arbitrary dimensions without either null-padded columns for a
 *   fixed catalogue of sizes or a per-dimension table anyway.
 * - one table per space keeps the ANN index optimal, makes dropping a space a
 *   single DROP, and makes a provider switch a rebuild of one table rather than a
 *   migration of a shared one.
 *
 * The ANN index cannot be probed in the diagnostics (MariaDB refuses a vector
 * index on a temporary table), so this class is where it is actually proven: if
 * the index cannot be created, table creation fails and the caller gets the
 * server's own message.
 *
 * @since 1.5.0
 */
class Space_Repository {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Supported modalities.
	 *
	 * @var string[]
	 */
	const MODALITIES = [ 'text', 'image' ];

	/**
	 * Suffix for per-space chunk tables.
	 *
	 * @var string
	 */
	const TABLE_PREFIX = 'tainacan_vec_';

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Registry table name.
	 *
	 * @return string
	 */
	public function registry_table() {
		global $wpdb;

		return $wpdb->prefix . 'tainacan_vec_spaces';
	}

	/**
	 * Create the registry table. Called once from a migration.
	 *
	 * @return void
	 */
	public function create_registry_table() {
		global $wpdb;

		$table           = $this->registry_table();
		$charset_collate = $wpdb->get_charset_collate();

		$query = "CREATE TABLE IF NOT EXISTS {$table} (
			id bigint(20) unsigned NOT NULL auto_increment,
			space_key varchar(191) NOT NULL,
			provider varchar(64) NOT NULL,
			model varchar(128) NOT NULL,
			modality varchar(16) NOT NULL,
			dimensions smallint(5) unsigned NOT NULL,
			table_name varchar(64) NOT NULL,
			is_active tinyint(1) NOT NULL default 0,
			status varchar(16) NOT NULL default 'ready',
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY space_key (space_key),
			KEY active (modality, is_active)
		) {$charset_collate};\n";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Static DDL, only the prefix and charset are interpolated.
		$wpdb->query( $query );
	}

	/**
	 * Stable key for a space.
	 *
	 * @param string $provider   Provider id.
	 * @param string $model      Model name.
	 * @param string $modality   text or image.
	 * @param int    $dimensions Dimensions.
	 * @return string
	 */
	public function space_key( $provider, $model, $modality, $dimensions ) {
		return sprintf( '%s:%s:%s:%d', $provider, $model, $modality, (int) $dimensions );
	}

	/**
	 * Table name for a space, safe for DDL.
	 *
	 * The name is derived from the key through a strict sanitiser and truncated to
	 * fit MySQL's 64 character identifier limit, because a caller-supplied string
	 * must never reach DDL.
	 *
	 * @param string $provider   Provider id.
	 * @param string $model      Model name.
	 * @param string $modality   text or image.
	 * @param int    $dimensions Dimensions.
	 * @return string
	 */
	public function table_name_for( $provider, $model, $modality, $dimensions ) {
		global $wpdb;

		$slug = strtolower( $provider . '_' . $model . '_' . $modality );
		$slug = preg_replace( '/[^a-z0-9]+/', '_', $slug );
		$slug = trim( (string) $slug, '_' );

		$suffix = $wpdb->prefix . self::TABLE_PREFIX;
		$budget = 64 - strlen( $suffix ) - 6; // room for "_12345"

		if ( strlen( $slug ) > $budget ) {
			$slug = substr( $slug, 0, max( 1, $budget ) );
		}

		return $suffix . $slug . '_' . (int) $dimensions;
	}

	/**
	 * The CREATE TABLE statement for a space.
	 *
	 * Pure string building so the shape is testable without DDL.
	 *
	 * @param string $table      Table name.
	 * @param int    $dimensions Column dimension.
	 * @return string
	 */
	public function build_create_table_sql( $table, $dimensions ) {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL auto_increment,
			item_id bigint(20) unsigned NOT NULL,
			collection_id bigint(20) unsigned NOT NULL,
			metadatum_id bigint(20) unsigned NULL,
			source varchar(16) NOT NULL,
			chunk_index int(10) unsigned NOT NULL default 0,
			content mediumtext NOT NULL,
			content_hash char(40) NOT NULL,
			attachment_id bigint(20) unsigned NULL,
			embedding VECTOR(" . (int) $dimensions . ") NOT NULL,
			indexed_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY item_source (item_id, source),
			KEY coll (collection_id),
			KEY content_hash (content_hash),
			VECTOR INDEX vec_idx (embedding)
		) ENGINE=InnoDB {$charset_collate};\n";
	}

	/**
	 * Create a space and its table.
	 *
	 * @param string $provider   Provider id.
	 * @param string $model      Model name.
	 * @param string $modality   text or image.
	 * @param int    $dimensions Dimensions.
	 * @return array|\WP_Error
	 */
	public function create_space( $provider, $model, $modality, $dimensions ) {
		global $wpdb;

		$provider   = trim( (string) $provider );
		$model      = trim( (string) $model );
		$modality   = strtolower( trim( (string) $modality ) );
		$dimensions = (int) $dimensions;

		if ( '' === $provider || '' === $model ) {
			return new \WP_Error(
				'tainacan_rag_missing_provider',
				__( 'A provider and a model are required.', 'tainacan' )
			);
		}

		if ( ! in_array( $modality, self::MODALITIES, true ) ) {
			return new \WP_Error(
				'tainacan_rag_invalid_modality',
				__( 'modality must be text or image.', 'tainacan' )
			);
		}

		if ( $dimensions < 1 ) {
			return new \WP_Error(
				'tainacan_rag_invalid_dimensions',
				__( 'dimensions must be a positive integer.', 'tainacan' )
			);
		}

		$verdict = Environment_Check::get_instance()->get_verdict();

		if ( ! isset( $verdict['status'] ) || 'ready' !== $verdict['status'] ) {
			return new \WP_Error(
				'tainacan_rag_environment_not_ready',
				__( 'This host cannot store vectors. Check the environment report first.', 'tainacan' ),
				[ 'verdict' => $verdict ]
			);
		}

		$max = isset( $verdict['max_dimensions'] ) ? (int) $verdict['max_dimensions'] : 0;

		if ( $max > 0 && $dimensions > $max ) {
			return new \WP_Error(
				'tainacan_rag_dimensions_exceeded',
				sprintf(
					/* translators: 1: requested dimensions, 2: largest accepted */
					__( 'This server rejected %1$d dimensions; the largest it accepted when probed was %2$d.', 'tainacan' ),
					$dimensions,
					$max
				),
				[
					'dimensions'     => $dimensions,
					'max_dimensions' => $max,
				]
			);
		}

		$key = $this->space_key( $provider, $model, $modality, $dimensions );

		$existing = $this->get_space_by_key( $key );

		if ( $existing ) {
			return $existing;
		}

		$table    = $this->table_name_for( $provider, $model, $modality, $dimensions );
		$previous = $wpdb->suppress_errors( true );
		$created  = $wpdb->query( $this->build_create_table_sql( $table, $dimensions ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Built by build_create_table_sql from sanitised names.
		$error    = $wpdb->last_error;
		$wpdb->suppress_errors( $previous );

		if ( false === $created ) {
			return new \WP_Error(
				'tainacan_rag_table_failed',
				sprintf(
					/* translators: %s: database error message */
					__( 'The vector table could not be created: %s', 'tainacan' ),
					$error
				),
				[
					'table' => $table,
					'error' => $error,
				]
			);
		}

		$inserted = $wpdb->insert(
			$this->registry_table(),
			[
				'space_key'  => $key,
				'provider'   => $provider,
				'model'      => $model,
				'modality'   => $modality,
				'dimensions' => $dimensions,
				'table_name' => $table,
				'is_active'  => 0,
				'status'     => 'ready',
				'created_at' => gmdate( 'Y-m-d H:i:s' ),
			],
			[ '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' ]
		);

		if ( ! $inserted ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Sanitised table name.

			return new \WP_Error(
				'tainacan_rag_registry_insert_failed',
				__( 'The space table was created but could not be registered, so it was removed again.', 'tainacan' )
			);
		}

		return $this->get_space( (int) $wpdb->insert_id );
	}

	/**
	 * All registered spaces.
	 *
	 * @return array
	 */
	public function get_spaces() {
		global $wpdb;

		$table = $this->registry_table();
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY modality ASC, id ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Registry table name from prefix.

		return is_array( $rows ) ? array_map( [ $this, 'prepare_row' ], $rows ) : [];
	}

	/**
	 * One space by id.
	 *
	 * @param int $id Space id.
	 * @return array|null
	 */
	public function get_space( $id ) {
		global $wpdb;

		$table = $this->registry_table();
		$row   = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Registry table name from prefix.
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ),
			ARRAY_A
		);

		return is_array( $row ) ? $this->prepare_row( $row ) : null;
	}

	/**
	 * One space by key.
	 *
	 * @param string $key Space key.
	 * @return array|null
	 */
	public function get_space_by_key( $key ) {
		global $wpdb;

		$table = $this->registry_table();
		$row   = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Registry table name from prefix.
			$wpdb->prepare( "SELECT * FROM {$table} WHERE space_key = %s", (string) $key ),
			ARRAY_A
		);

		return is_array( $row ) ? $this->prepare_row( $row ) : null;
	}

	/**
	 * The active space for a modality.
	 *
	 * @param string $modality text or image.
	 * @return array|null
	 */
	public function get_active( $modality = 'text' ) {
		global $wpdb;

		$table = $this->registry_table();
		$row   = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Registry table name from prefix.
			$wpdb->prepare( "SELECT * FROM {$table} WHERE modality = %s AND is_active = 1 LIMIT 1", (string) $modality ),
			ARRAY_A
		);

		return is_array( $row ) ? $this->prepare_row( $row ) : null;
	}

	/**
	 * Make one space the active one for its modality.
	 *
	 * Exactly one space per modality is active, which is what makes "the chosen
	 * sizing is the same for every collection" true.
	 *
	 * @param int $id Space id.
	 * @return array|\WP_Error
	 */
	public function activate( $id ) {
		global $wpdb;

		$space = $this->get_space( $id );

		if ( ! $space ) {
			return new \WP_Error(
				'tainacan_rag_space_not_found',
				__( 'No space exists with that id.', 'tainacan' )
			);
		}

		$table = $this->registry_table();

		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Registry table name from prefix.
			$wpdb->prepare( "UPDATE {$table} SET is_active = 0 WHERE modality = %s", $space['modality'] )
		);

		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Registry table name from prefix.
			$wpdb->prepare( "UPDATE {$table} SET is_active = 1 WHERE id = %d", (int) $id )
		);

		return $this->get_space( $id );
	}

	/**
	 * Delete a space and its table.
	 *
	 * @param int $id Space id.
	 * @return bool|\WP_Error
	 */
	public function delete_space( $id ) {
		global $wpdb;

		$space = $this->get_space( $id );

		if ( ! $space ) {
			return new \WP_Error(
				'tainacan_rag_space_not_found',
				__( 'No space exists with that id.', 'tainacan' )
			);
		}

		// prepare_row() exposes the column as "table"; reading "table_name" here
		// produced an empty identifier and a silent no-op drop.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $space['table'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name came from the registry, built by table_name_for().

		$wpdb->delete( $this->registry_table(), [ 'id' => (int) $id ], [ '%d' ] );

		/*
		 * Queue rows would otherwise outlive the space, fail with "space no longer
		 * exists", and sit in `failed` forever because there is nothing to requeue
		 * them into. The console showed exactly that before this line existed.
		 */
		Index_Queue::get_instance()->delete_for_space( (int) $id );

		return true;
	}

	/**
	 * List spaces for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array
	 */
	public function for_ability_list( $input = [] ) {
		return [
			'spaces'  => $this->get_spaces(),
			'active'  => [
				'text'  => $this->get_active( 'text' ),
				'image' => $this->get_active( 'image' ),
			],
		];
	}

	/**
	 * Create a space for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_create( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		return $this->create_space(
			isset( $input['provider'] ) ? $input['provider'] : '',
			isset( $input['model'] ) ? $input['model'] : '',
			isset( $input['modality'] ) ? $input['modality'] : 'text',
			isset( $input['dimensions'] ) ? (int) $input['dimensions'] : 0
		);
	}

	/**
	 * Activate a space for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_activate( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		return $this->activate( isset( $input['space_id'] ) ? (int) $input['space_id'] : 0 );
	}

	/**
	 * Delete a space for an ability callback.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function for_ability_delete( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		$deleted = $this->delete_space( isset( $input['space_id'] ) ? (int) $input['space_id'] : 0 );

		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		return [ 'deleted' => true ];
	}

	/**
	 * Whether a space table actually has a vector index.
	 *
	 * MariaDB normalises `VECTOR INDEX x (col)` into `VECTOR KEY \`x\` (\`col\`)` in
	 * SHOW CREATE TABLE, so substring matching for "VECTOR INDEX" reports a false
	 * negative on a table that does have one. INFORMATION_SCHEMA reports
	 * INDEX_TYPE = 'VECTOR', which is the reliable check.
	 *
	 * @param string $table Table name from the registry.
	 * @return bool
	 */
	public function has_vector_index( $table ) {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = DATABASE() AND table_name = %s AND index_type = 'VECTOR'",
				(string) $table
			)
		);

		return (int) $count > 0;
	}

	/**
	 * Normalise a registry row.
	 *
	 * @param array $row Raw row.
	 * @return array
	 */
	private function prepare_row( array $row ) {
		return [
			'id'         => (int) $row['id'],
			'key'        => (string) $row['space_key'],
			'provider'   => (string) $row['provider'],
			'model'      => (string) $row['model'],
			'modality'   => (string) $row['modality'],
			'dimensions' => (int) $row['dimensions'],
			'table'      => (string) $row['table_name'],
			'is_active'  => ! empty( $row['is_active'] ),
			'status'     => (string) $row['status'],
			'created_at' => (string) $row['created_at'],
		];
	}
}
