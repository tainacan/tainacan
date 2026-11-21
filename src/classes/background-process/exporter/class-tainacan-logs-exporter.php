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

	/**
	 * Returns the selected output format.
	 *
	 * Existing sessions without this option continue using CSV.
	 *
	 * @return string
	 */
	private function get_export_format() {
		$format = $this->get_option( 'format' );

		return in_array( $format, [ 'csv', 'json' ], true )
			? $format
			: 'csv';
	}

	/**
	 * Returns the output file key for the selected format.
	 *
	 * @param string $format Output format.
	 * @return string
	 */
	private function get_export_file_key( $format ) {
		return $format === 'json'
			? 'logs-export.json'
			: self::FILE_KEY;
	}

	/**
	 * Displays the activity export options.
	 *
	 * @return string
	 */
	public function options_form() {
		$format = $this->get_export_format();

		ob_start();
		?>
		<div class="field">
			<label class="label" for="tainacan-logs-export-format">
				<?php esc_html_e( 'File format', 'tainacan' ); ?>
			</label>

			<div class="control">
				<div class="select is-fullwidth">
					<select
						id="tainacan-logs-export-format"
						name="format">
						<option
							value="csv"
							<?php selected( $format, 'csv' ); ?>>
							CSV
						</option>
						<option
							value="json"
							<?php selected( $format, 'json' ); ?>>
							JSON
						</option>
					</select>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	public function export_logs() {
		global $wpdb;

		if (
			! current_user_can( 'manage_tainacan' )
			|| ! current_user_can( 'tnc_rep_read_logs' )
		) {
			$this->abort();
			$this->add_error_log(
				__( 'You do not have permission to export logs.', 'tainacan' )
			);
			return false;
		}

		$repository = \Tainacan\Repositories\Logs::get_instance();
		$legacy = ! defined( 'TAINACAN_USE_DEPRECATED_LOGS' )
			|| TAINACAN_USE_DEPRECATED_LOGS !== false;

		$columns = [
			'ID', 'date', 'user_id', 'user_name', 'action',
			'collection_id', 'item_id', 'object_type', 'object_id',
			'title', 'description', 'old_value', 'new_value',
		];

		$state = $this->get_transient( 'logs_export_state' );

		if ( $state === null ) {
			$latest = $repository->fetch(
				[
					'posts_per_page' => 1,
					'orderby' => 'ID',
					'order' => 'DESC',
				],
				'OBJECT'
			);

			if ( $wpdb->last_error ) {
				throw new \RuntimeException(
					'Unable to query the activity history.'
				);
			}

			$state = [
				'last_id' => 0,
				'max_id' => empty( $latest )
					? 0
					: (int) $latest[0]->get_id(),
				'processed' => 0,
				'legacy' => $legacy,
				'format' => $this->get_export_format(),
			];

			$uploads = wp_upload_dir();

			if (
				! empty( $uploads['error'] )
				|| ! wp_mkdir_p(
					trailingslashit( $uploads['basedir'] )
					. 'tainacan/exporter'
				)
			) {
				throw new \RuntimeException(
					'Unable to create the exporter directory.'
				);
			}

			$file_key = $this->get_export_file_key( $state['format'] );

			if ( $state['format'] === 'json' ) {
				// Keep a valid JSON array, even with no activities.
				$written = $this->write_chunk(
					0,
					"[\n\n]\n",
					$file_key
				);

				// Next batch overwrites the closing "\n]\n".
				$state['bytes'] = $written - 3;
			} else {
				$state['bytes'] = $this->write_chunk(
					0,
					$this->csv_row( $columns ),
					$file_key
				);
			}

			$this->add_transient( 'logs_export_state', $state );
		}

		// Sessions created before format selection always used CSV.
		$format = isset( $state['format'] )
			? $state['format']
			: 'csv';

		if ( ! in_array( $format, [ 'csv', 'json' ], true ) ) {
			throw new \RuntimeException(
				'Invalid activity export format.'
			);
		}

		$file_key = $this->get_export_file_key( $format );

		if ( $state['legacy'] !== $legacy ) {
			throw new \RuntimeException(
				'The log storage changed during the export.'
			);
		}

		if ( $state['max_id'] === 0 ) {
			return false;
		}

		$args = [
			'posts_per_page' => self::BATCH_SIZE,
			'orderby' => 'ID',
			'order' => 'ASC',
		];

		if ( $legacy ) {
			$args['tainacan_logs_export_cursor'] = true;
			$args['no_found_rows'] = true;

			$where = function ( $sql, $query ) use ( $wpdb, $state ) {
				if ( $query->get( 'tainacan_logs_export_cursor' ) ) {
					$sql .= $wpdb->prepare(
						" AND {$wpdb->posts}.ID > %d AND {$wpdb->posts}.ID <= %d",
						$state['last_id'],
						$state['max_id']
					);
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
			throw new \RuntimeException(
				'Unable to query the activity history.'
			);
		}

		if ( empty( $logs ) ) {
			return false;
		}

		$chunk = '';

		foreach ( $logs as $log ) {
			$values = [
				$log->get_id(),
				$log->get_date(),
				$log->get_user_id(),
				$log->get_user_name(),
				$log->get_action(),
				$log->get_collection_id(),
				$log->get_item_id(),
				$log->get_object_type(),
				$log->get_object_id(),
				$log->get_title(),
				$log->get_description(),
				$log->get_old_value(),
				$log->get_new_value(),
			];

			if ( $format === 'json' ) {
				$record = array_combine( $columns, $values );

				$json = wp_json_encode(
					$record,
					JSON_PRETTY_PRINT
					| JSON_UNESCAPED_UNICODE
					| JSON_UNESCAPED_SLASHES
				);

				if ( $json === false ) {
					throw new \RuntimeException(
						'Unable to encode an activity as JSON.'
					);
				}

				if ( $state['processed'] > 0 ) {
					$chunk .= ",\n";
				}

				$chunk .= $json;
			} else {
				$chunk .= $this->csv_row( $values );
			}

			$state['last_id'] = (int) $log->get_id();
			$state['processed']++;
		}

		if ( $format === 'json' ) {
			$written = $this->write_chunk(
				$state['bytes'],
				$chunk . "\n]\n",
				$file_key
			);

			$state['bytes'] = $written - 3;
		} else {
			$state['bytes'] = $this->write_chunk(
				$state['bytes'],
				$chunk,
				$file_key
			);
		}

		$this->add_transient( 'logs_export_state', $state );

		return $state['last_id'] >= $state['max_id']
			|| count( $logs ) < self::BATCH_SIZE
			? false
			: $state['processed'];
	}

	/**
	 * Resume at the persisted byte boundary, discarding an uncommitted retry tail.
	 */
	private function write_chunk( $offset, $chunk, $file_key = self::FILE_KEY ) {
		$files = $this->get_output_files();

		if ( ! isset( $files[$file_key] ) ) {
			$this->add_new_file( $file_key );
			$files = $this->get_output_files();
		}

		if ( ! isset( $files[$file_key]['filename'] ) ) {
			throw new \RuntimeException(
				'Unable to initialize the export file.'
			);
		}

		$stream = fopen( $files[$file_key]['filename'], 'c+b' );

		if ( $stream === false ) {
			throw new \RuntimeException(
				'Unable to open the export file.'
			);
		}

		try {
			$stat = fstat( $stream );

			if (
				! $stat
				|| $stat['size'] < $offset
				|| ! ftruncate( $stream, $offset )
				|| fseek( $stream, $offset ) !== 0
			) {
				throw new \RuntimeException(
					'Unable to restore the export checkpoint.'
				);
			}

			$written = 0;
			$length = strlen( $chunk );

			while ( $written < $length ) {
				$count = fwrite( $stream, substr( $chunk, $written ) );

				if ( $count === false || $count === 0 ) {
					throw new \RuntimeException(
						'Unable to write the export file.'
					);
				}

				$written += $count;
			}

			if ( ! fflush( $stream ) ) {
				throw new \RuntimeException(
					'Unable to flush the export file.'
				);
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

		if ( isset( $files['logs-export.json'] ) ) {
			$file = $files['logs-export.json'];
		} elseif ( isset( $files[self::FILE_KEY] ) ) {
			$file = $files[self::FILE_KEY];
		} else {
			return '';
		}

		return esc_html__(
			'Your activity export file is ready!',
			'tainacan'
		) . '<br/>'
			. $this->get_output_link( $file['url'], $for_email );
	}

	public function process_item( $item, $metadata ) {
		return false;
	}
}
