<?php

namespace Tainacan\Exporter;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Export the activity history in bounded batches.
 */
class Logs_Exporter extends Exporter {

	const FILE_KEY = 'logs-export.csv';
	const BATCH_SIZE = 100;

	protected $steps = [
		[
			'name' => 'Export Logs',
			'progress_label' => 'Exporting logs',
			'callback' => 'export_logs',
		],
	];

	public function __construct( $attributes = array() ) {
		parent::__construct( $attributes );
		$this->set_steps( [ [
			'name' => __( 'Export activities', 'tainacan' ),
			'progress_label' => __( 'Exporting activities', 'tainacan' ),
			'callback' => 'export_logs',
		] ] );
	}

	public function export_logs() {
		global $wpdb;

		if ( ! current_user_can( 'manage_tainacan' ) || ! current_user_can( 'tnc_rep_read_logs' ) ) {
			$this->abort();
			$this->add_error_log( __( 'You do not have permission to export logs.', 'tainacan' ) );
			return false;
		}

		$repository = \Tainacan\Repositories\Logs::get_instance();
		$legacy = ! defined( 'TAINACAN_USE_DEPRECATED_LOGS' ) || TAINACAN_USE_DEPRECATED_LOGS !== false;
		$state = $this->get_transient( 'logs_export_state' );
		if ( $state === null ) {
			$latest = $repository->fetch( [ 'posts_per_page' => 1, 'orderby' => 'ID', 'order' => 'DESC' ], 'OBJECT' );
			if ( $wpdb->last_error ) {
				throw new \RuntimeException( 'Unable to query the activity history.' );
			}
			$state = [
				'last_id' => 0,
				'max_id' => empty( $latest ) ? 0 : (int) $latest[0]->get_id(),
				'processed' => 0,
				'legacy' => $legacy,
			];
			$uploads = wp_upload_dir();
			if ( ! empty( $uploads['error'] ) || ! wp_mkdir_p( trailingslashit( $uploads['basedir'] ) . 'tainacan/exporter' ) ) {
				throw new \RuntimeException( 'Unable to create the exporter directory.' );
			}
			$state['bytes'] = $this->write_chunk( 0, $this->csv_row( [
				'ID', 'date', 'user_id', 'user_name', 'action', 'collection_id', 'item_id',
				'object_type', 'object_id', 'title', 'description', 'old_value', 'new_value',
			] ) );
			$this->add_transient( 'logs_export_state', $state );
		}
		if ( $state['legacy'] !== $legacy ) {
			throw new \RuntimeException( 'The log storage changed during the export.' );
		}
		if ( $state['max_id'] === 0 ) {
			return false;
		}

		$args = [ 'posts_per_page' => self::BATCH_SIZE, 'orderby' => 'ID', 'order' => 'ASC' ];
		if ( $legacy ) {
			// Scope the cursor to this query; other queries during hydration must remain unchanged.
			$args['tainacan_logs_export_cursor'] = true;
			$args['no_found_rows'] = true;
			$where = function ( $sql, $query ) use ( $wpdb, $state ) {
				if ( $query->get( 'tainacan_logs_export_cursor' ) ) {
					$sql .= $wpdb->prepare( " AND {$wpdb->posts}.ID > %d AND {$wpdb->posts}.ID <= %d", $state['last_id'], $state['max_id'] );
				}
				return $sql;
			};
			add_filter( 'posts_where', $where, 10, 2 );
			try {
				$logs = $repository->fetch( $args, 'OBJECT' );
			} finally {
				remove_filter( 'posts_where', $where, 10 );
			}
		} else {
			$args['id_after'] = $state['last_id'];
			$args['id_before'] = $state['max_id'];
			$logs = $repository->fetch( $args );
		}
		if ( $wpdb->last_error ) {
			throw new \RuntimeException( 'Unable to query the activity history.' );
		}
		if ( empty( $logs ) ) {
			return false;
		}

		$chunk = '';
		foreach ( $logs as $log ) {
			$chunk .= $this->csv_row( [
				$log->get_id(), $log->get_date(), $log->get_user_id(), $log->get_user_name(),
				$log->get_action(), $log->get_collection_id(), $log->get_item_id(),
				$log->get_object_type(), $log->get_object_id(), $log->get_title(),
				$log->get_description(), $log->get_old_value(), $log->get_new_value(),
			] );
			$state['last_id'] = (int) $log->get_id();
			$state['processed']++;
		}
		$state['bytes'] = $this->write_chunk( $state['bytes'], $chunk );
		$this->add_transient( 'logs_export_state', $state );
		return $state['last_id'] >= $state['max_id'] || count( $logs ) < self::BATCH_SIZE ? false : $state['processed'];
	}

	/**
	 * Resume at the persisted byte boundary, discarding an uncommitted retry tail.
	 */
	private function write_chunk( $offset, $chunk ) {
		$files = $this->get_output_files();
		if ( ! isset( $files[self::FILE_KEY] ) ) {
			$this->add_new_file( self::FILE_KEY );
			$files = $this->get_output_files();
		}
		if ( ! isset( $files[self::FILE_KEY]['filename'] ) ) {
			throw new \RuntimeException( 'Unable to initialize the CSV file.' );
		}
		$stream = fopen( $files[self::FILE_KEY]['filename'], 'c+b' );
		if ( $stream === false ) {
			throw new \RuntimeException( 'Unable to open the CSV file.' );
		}
		try {
			$stat = fstat( $stream );
			if ( ! $stat || $stat['size'] < $offset || ! ftruncate( $stream, $offset ) || fseek( $stream, $offset ) !== 0 ) {
				throw new \RuntimeException( 'Unable to restore the CSV checkpoint.' );
			}
			$written = 0;
			$length = strlen( $chunk );
			while ( $written < $length ) {
				$count = fwrite( $stream, substr( $chunk, $written ) );
				if ( $count === false || $count === 0 ) {
					throw new \RuntimeException( 'Unable to write the CSV file.' );
				}
				$written += $count;
			}
			if ( ! fflush( $stream ) ) {
				throw new \RuntimeException( 'Unable to flush the CSV file.' );
			}
			return $offset + $written;
		} finally {
			fclose( $stream );
		}
	}

	/** Format one row with standard CSV escaping and neutralize spreadsheet formulas. */
	public function csv_row( array $values ) {
		foreach ( $values as &$value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				if ( $value === false ) {
					throw new \RuntimeException( 'Unable to encode a log value.' );
				}
			} elseif ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} else {
				$value = (string) $value;
			}
			if ( preg_match( '/^(?:[=+@\t\r\n]|-|(\s+)[=+@-])/', $value ) ) {
				$value = "'" . $value;
			}
		}
		unset( $value );
		$stream = fopen( 'php://temp', 'w+' );
		if ( $stream === false ) {
			throw new \RuntimeException( 'Unable to create the CSV stream.' );
		}
		try {
			if ( fputcsv( $stream, $values, ',', '"', '' ) === false ) {
				throw new \RuntimeException( 'Unable to format a CSV row.' );
			}
			rewind( $stream );
			return stream_get_contents( $stream );
		} finally {
			fclose( $stream );
		}
	}

	public function get_output( $for_email = false ) {
		$files = $this->get_output_files();
		if ( ! isset( $files[self::FILE_KEY] ) ) {
			return '';
		}
		return esc_html__( 'Your CSV file is ready!', 'tainacan' ) . '<br/>'
			. $this->get_output_link( $files[self::FILE_KEY]['url'], $for_email );
	}

	public function process_item( $item, $metadata ) {
		return false;
	}
}
