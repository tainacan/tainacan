<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Decides whether this host can support the semantic search layer.
 *
 * The point of this class is that the admin screen must never render controls
 * that cannot work. The menu always registers; what it shows depends on a graded
 * verdict, and a failed check names itself and the version required rather than
 * leaving an operator with a disabled button and no reason.
 *
 * Probing design, learned by trying it rather than assuming:
 *
 * - Probes use TEMPORARY tables. A real CREATE TABLE causes an implicit commit,
 *   which would break the transaction rollback WordPress test cases rely on.
 * - MariaDB refuses a vector index on a temporary table
 *   ("ERROR 1796: Cannot create VECTOR index on temporary InnoDB table"), so the
 *   ANN index cannot be probed here. Index support is proven when the first real
 *   space table is created, where a failure names the configuration step. A
 *   diagnostic that claims to have checked the index would be lying.
 * - Each dimension is probed separately, because the accepted maximum is a
 *   server property and pinning a number in code would be wrong on some host.
 *
 * @since 1.5.0
 */
class Environment_Check {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Option holding the cached verdict.
	 *
	 * @var string
	 */
	const OPTION_CACHE = 'tainacan_rag_environment';

	/**
	 * How long a verdict stays usable, in seconds.
	 *
	 * @var int
	 */
	const CACHE_TTL = 3600;

	/**
	 * Dimensions probed to report what the server accepts.
	 *
	 * @var int[]
	 */
	const PROBE_DIMENSIONS = [ 384, 768, 1024, 1536, 3072 ];

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Cached verdict, recomputed when stale or when the server changes.
	 *
	 * @param bool $force Recompute regardless of the cache.
	 * @return array
	 */
	public function get_verdict( $force = false ) {
		$cached = get_option( self::OPTION_CACHE, [] );

		if ( ! $force && is_array( $cached ) && ! empty( $cached['checked_at'] ) ) {
			$fresh  = ( time() - (int) $cached['checked_at'] ) < self::CACHE_TTL;
			$same_db = isset( $cached['db_version'] ) && $cached['db_version'] === $this->get_db_version();

			if ( $fresh && $same_db ) {
				return $cached;
			}
		}

		$verdict = $this->run_checks();

		update_option( self::OPTION_CACHE, $verdict, false );

		return $verdict;
	}

	/**
	 * Run every check and grade the result.
	 *
	 * @return array
	 */
	public function run_checks() {
		$db_version   = $this->get_db_version();
		$is_maria     = false !== stripos( $db_version, 'mariadb' );
		$abilities_ok = function_exists( 'wp_register_ability' );

		$vector_type = $is_maria ? $this->probe_dimensions( [ 3 ] ) : false;

		$checks = [
			[
				'key'         => 'abilities_api',
				'label'       => __( 'WordPress Abilities API', 'tainacan' ),
				'ok'          => $abilities_ok,
				'blocking'    => true,
				'detail'      => $abilities_ok
					? __( 'Available.', 'tainacan' )
					: sprintf(
						/* translators: %s: detected WordPress version */
						__( 'Not available on WordPress %s.', 'tainacan' ),
						get_bloginfo( 'version' )
					),
				'requirement' => __( 'WordPress 6.9 or newer.', 'tainacan' ),
			],
			[
				'key'         => 'database',
				'label'       => __( 'Database engine', 'tainacan' ),
				'ok'          => $is_maria,
				'blocking'    => true,
				'detail'      => '' !== $db_version
					? $db_version
					: __( 'The server version could not be read.', 'tainacan' ),
				'requirement' => __( 'MariaDB 11.7 or newer. MySQL has no VECTOR type.', 'tainacan' ),
			],
			[
				'key'         => 'vector_storage',
				'label'       => __( 'Vector storage and distance functions', 'tainacan' ),
				'ok'          => (bool) $vector_type,
				'blocking'    => true,
				'detail'      => $vector_type
					? __( 'A temporary VECTOR table accepted rows and VEC_DISTANCE_COSINE ranked them.', 'tainacan' )
					: __( 'The server refused a VECTOR column, or the distance functions are missing.', 'tainacan' ),
				'requirement' => __( 'MariaDB 11.7 or newer, with VEC_FromText and VEC_DISTANCE_COSINE.', 'tainacan' ),
			],
		];

		$max_dimensions = $vector_type ? $this->probe_dimensions( self::PROBE_DIMENSIONS ) : 0;

		$checks[] = [
			'key'         => 'max_dimensions',
			'label'       => __( 'Largest embedding dimension probed', 'tainacan' ),
			'ok'          => $max_dimensions > 0,
			'blocking'    => false,
			'detail'      => $max_dimensions > 0
				? sprintf(
					/* translators: %d: number of dimensions */
					__( '%d dimensions accepted.', 'tainacan' ),
					$max_dimensions
				)
				: __( 'No probed dimension was accepted.', 'tainacan' ),
			'requirement' => __( 'Informational: any positive value means vectors can be stored.', 'tainacan' ),
		];

		$checks[] = [
			'key'         => 'vector_index',
			'label'       => __( 'ANN index', 'tainacan' ),
			'ok'          => null,
			'blocking'    => false,
			'detail'      => __( 'Not probed: MariaDB refuses a vector index on a temporary table. It is verified when the first space table is created.', 'tainacan' ),
			'requirement' => __( 'MariaDB 11.7 or newer.', 'tainacan' ),
		];

		$blocking_failed = array_filter(
			$checks,
			static function ( $check ) {
				return ! empty( $check['blocking'] ) && true !== $check['ok'];
			}
		);

		if ( ! $abilities_ok ) {
			$status = 'unsupported';
		} elseif ( empty( $blocking_failed ) ) {
			$status = 'ready';
		} else {
			$status = 'degraded';
		}

		return [
			'status'         => $status,
			'checks'         => $checks,
			'db_version'     => $db_version,
			'max_dimensions' => $max_dimensions,
			'checked_at'     => time(),
		];
	}

	/**
	 * Ability-facing entry point.
	 *
	 * get_verdict() takes a boolean force flag, and an ability callback receives the
	 * input array, so an array would have read as truthy and forced a recompute on
	 * every call. This wrapper is why the ability has an explicit refresh flag.
	 *
	 * @param mixed $input Ability input.
	 * @return array
	 */
	public function for_ability( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		return $this->get_verdict( ! empty( $input['refresh'] ) );
	}

	/**
	 * Whether the host can run the semantic layer.
	 *
	 * @param bool $force Recompute regardless of the cache.
	 * @return bool
	 */
	public function is_ready( $force = false ) {
		$verdict = $this->get_verdict( $force );

		return isset( $verdict['status'] ) && 'ready' === $verdict['status'];
	}

	/**
	 * Read the database server version.
	 *
	 * @return string
	 */
	public function get_db_version() {
		global $wpdb;

		$previous = $wpdb->suppress_errors( true );
		$version  = $wpdb->get_var( 'SELECT VERSION()' );
		$wpdb->suppress_errors( $previous );

		return is_string( $version ) ? $version : '';
	}

	/**
	 * Largest probed dimension the server accepts, or 0 when none work.
	 *
	 * @param int[] $dimensions Dimensions to try, in ascending order.
	 * @return int
	 */
	private function probe_dimensions( array $dimensions ) {
		sort( $dimensions );

		$largest = 0;

		foreach ( $dimensions as $dimensions_value ) {
			if ( $this->probe_vector_table( (int) $dimensions_value ) ) {
				$largest = (int) $dimensions_value;
			}
		}

		return $largest;
	}

	/**
	 * Create a temporary vector table, write rows, rank them by distance, drop it.
	 *
	 * This single probe proves the VECTOR type, the VEC_FromText cast and
	 * VEC_DISTANCE_COSINE ordering in one pass, which is why the checks table
	 * reports them together.
	 *
	 * @param int $dimensions Column dimension.
	 * @return bool
	 */
	private function probe_vector_table( $dimensions ) {
		global $wpdb;

		$table    = '_tainacan_vec_probe_' . (int) $dimensions;
		$previous = $wpdb->suppress_errors( true );

		$created = $wpdb->query(
			"CREATE TEMPORARY TABLE {$table} (id INT PRIMARY KEY, v VECTOR(" . (int) $dimensions . ") NOT NULL)"
		);

		if ( false === $created ) {
			$wpdb->suppress_errors( $previous );

			return false;
		}

		$inserted = $wpdb->query(
			"INSERT INTO {$table} VALUES (1, VEC_FromText('[1,0,0]')), (2, VEC_FromText('[0,1,0]'))"
		);

		if ( false === $inserted ) {
			$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS {$table}" );
			$wpdb->suppress_errors( $previous );

			return false;
		}

		$nearest = $wpdb->get_var(
			"SELECT id FROM {$table} ORDER BY VEC_DISTANCE_COSINE(v, VEC_FromText('[1,0,0]')) LIMIT 1"
		);

		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS {$table}" );
		$wpdb->suppress_errors( $previous );

		// The vector closest to [1,0,0] is the row whose id is 1.
		return 1 === (int) $nearest;
	}
}
