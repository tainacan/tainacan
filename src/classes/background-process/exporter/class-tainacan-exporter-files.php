<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Exporter_Files {
	use \Tainacan\Traits\Singleton_Instance;

	const CRON_HOOK = 'tainacan_cleanup_expired_exporter_files';

	/**
	 * Registers the exporter files lifecycle hooks.
	 */
	private function init()	{
		add_action( 'init', array( $this, 'schedule_cleanup' ) );
		add_action( self::CRON_HOOK, array( $this, 'delete_expired_files' ) );
	}

	/**
	 * Returns the exporter file availability period in days.
	 *
	 * @return int
	 */
	public function get_expiration_days() {
		$expiration_days = absint(
			get_option(
				'tainacan_option_exporter_files_expiration_days',
				7
			)
		);
		$expiration_days = min( 30, max( 1, $expiration_days ) );

		$expiration_days = absint(
			apply_filters(
				'tainacan-exporter-files-expiration-days',
				$expiration_days
			)
		);
		$expiration_days = min( 30, max( 1, $expiration_days ) );

		if ( defined( 'TAINACAN_EXPORTER_FILES_EXPIRATION' ) ) {
			$expiration_days = absint( TAINACAN_EXPORTER_FILES_EXPIRATION );
			$expiration_days = min( 30, max( 1, $expiration_days ) );
		}

		return $expiration_days;
	}

	/**
	 * Returns the exporter file availability period in seconds.
	 *
	 * @return int
	 */
	public function get_expiration_period() {
		return $this->get_expiration_days() * DAY_IN_SECONDS;
	}

	/**
	 * Associates exporter output files with a background process and starts
	 * their expiration period.
	 *
	 * @param array $output_files Exporter output files.
	 * @param int   $process_id   Background process ID.
	 *
	 * @return array
	 */
	public function prepare_output_files( $output_files, $process_id ) {
		if ( ! is_array( $output_files ) ) {
			return array();
		}

		$process_id = absint( $process_id );

		if ( empty( $process_id ) ) {
			return $output_files;
		}

		$expires_at = time() + $this->get_expiration_period();

		foreach ( $output_files as $key => $file ) {
			if ( ! is_array( $file ) ) {
				continue;
			}

			$guid = $this->get_file_guid( $file );

			if ( empty( $guid ) ) {
				continue;
			}

			$output_files[ $key ]['guid'] = $guid;

			/*
			 * Do not extend an existing expiration period if this method
			 * runs more than once for the same completed process.
			 */
			if ( empty( $file['expires_at'] ) ) {
				$output_files[ $key ]['expires_at'] = $expires_at;
			}

			$output_files[ $key ]['url'] = $this->get_download_url(
				$guid,
				$process_id
			);
		}

		return $output_files;
	}

	/**
	 * Gets the identifier of an exporter output file.
	 *
	 * New records store the GUID explicitly. Legacy records can recover it
	 * from the absolute filename or from the previous download URL.
	 *
	 * @param array $file Exporter file information.
	 *
	 * @return string|null
	 */
	private function get_file_guid( $file ) {
		if ( ! is_array( $file ) ) {
			return null;
		}

		if ( ! empty( $file['guid'] ) && is_string( $file['guid'] ) ) {
			$guid = ltrim( wp_normalize_path( $file['guid'] ), '/' );

			return strpos( $guid, 'exporter/' ) === 0
				? $guid
				: null;
		}

		if ( ! empty( $file['filename'] ) && is_string( $file['filename'] ) ) {
			$upload_dir   = wp_upload_dir();
			$tainacan_dir = wp_normalize_path(
				trailingslashit( $upload_dir['basedir'] ) . 'tainacan/'
			);
			$filename     = wp_normalize_path( $file['filename'] );

			if ( strpos( $filename, $tainacan_dir ) === 0 ) {
				$guid = ltrim(
					substr( $filename, strlen( $tainacan_dir ) ),
					'/'
				);

				if ( strpos( $guid, 'exporter/' ) === 0 ) {
					return $guid;
				}
			}
		}

		if ( ! empty( $file['url'] ) && is_string( $file['url'] ) ) {
			$query = wp_parse_url( $file['url'], PHP_URL_QUERY );

			if ( is_string( $query ) ) {
				parse_str( $query, $parameters );

				if (
					! empty( $parameters['guid'] ) &&
					is_string( $parameters['guid'] )
				) {
					$guid = ltrim(
						wp_normalize_path( $parameters['guid'] ),
						'/'
					);

					if ( strpos( $guid, 'exporter/' ) === 0 ) {
						return $guid;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Determines whether an exporter file is no longer available.
	 *
	 * Legacy files without an expiration timestamp are not considered
	 * expired automatically.
	 *
	 * @param array $file Exporter file information.
	 *
	 * @return bool
	 */
	private function is_expired( $file ) {
		if ( ! is_array( $file ) ) {
			return false;
		}

		if ( ! empty( $file['deleted_at'] ) ) {
			return true;
		}

		if ( empty( $file['expires_at'] ) ) {
			return false;
		}

		return time() >= (int) $file['expires_at'];
	}

	/**
	 * Builds an authenticated exporter download URL.
	 *
	 * The nonce placeholder is replaced when the REST response is prepared.
	 *
	 * @param string $guid       File identifier.
	 * @param int    $process_id Background process ID.
	 *
	 * @return string|null
	 */
	private function get_download_url( $guid, $process_id ) {
		$guid       = ltrim( wp_normalize_path( $guid ), '/' );
		$process_id = absint( $process_id );

		if (
			empty( $guid ) ||
			strpos( $guid, 'exporter/' ) !== 0 ||
			empty( $process_id )
		) {
			return null;
		}

		return esc_url_raw( rest_url() )
			. 'tainacan/v2/bg-processes/file?guid='
			. rawurlencode( $guid )
			. '&process_id='
			. $process_id
			. '&_wpnonce=[nonce]';
	}

	/**
	 * Schedules the daily cleanup of expired exporter files.
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * Removes the scheduled cleanup when Tainacan is deactivated.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Adds exporter file expiration information to a background process response.
	 *
	 * @param object $process Background process database row.
	 *
	 * @return object
	 */
	public function prepare_process_for_response( $process ) {
		if (
			! is_object( $process ) ||
			empty( $process->ID ) ||
			empty( $process->action ) ||
			'exporter' !== $process->action
		) {
			return $process;
		}

		$data = maybe_unserialize( $process->data );

		if (
			! is_array( $data ) ||
			empty( $data['output_files'] ) ||
			! is_array( $data['output_files'] )
		) {
			return $process;
		}

		$exporter_files = array();
		$expiration_timestamps = array();
		$expired_files = 0;
		$can_download = current_user_can( 'manage_tainacan' );

		foreach ( $data['output_files'] as $key => $file ) {
			if ( ! is_array( $file ) ) {
				continue;
			}

			$guid = $this->get_file_guid( $file );

			if ( empty( $guid ) ) {
				continue;
			}

			$expires_at = ! empty( $file['expires_at'] )
				? (int) $file['expires_at']
				: null;
			$expired    = $this->is_expired( $file );

			if ( null !== $expires_at ) {
				$expiration_timestamps[] = $expires_at;
			}

			if ( $expired ) {
				$expired_files++;
			}

			$download_url = ( $expired || ! $can_download )
				? null
				: $this->get_download_url( $guid, $process->ID );

			/*
			 * Refresh legacy and current links with the process reference
			 * whenever the API response is prepared.
			 */
			if (
				! empty( $file['url'] ) &&
				is_string( $process->output )
			) {
				$process->output = str_replace(
					$file['url'],
					$download_url ?: '#',
					$process->output
				);
			}

			$exporter_files[] = array(
				'key'        => $key,
				'url'        => $download_url,
				'expires_at' => null !== $expires_at
					? wp_date( 'Y-m-d H:i:s', $expires_at, wp_timezone() )
					: null,
				'expired'    => $expired,
			);
		}

		if ( empty( $exporter_files ) ) {
			return $process;
		}

		$process->exporter_files                 = $exporter_files;
		$process->output_files_download_allowed = $can_download;

		$process->output_files_expires_at = ! empty( $expiration_timestamps )
			? wp_date(
				'Y-m-d H:i:s',
				min( $expiration_timestamps ),
				wp_timezone()
			)
			: null;

		$process->output_files_expired = count( $exporter_files ) === $expired_files;

		return $process;
	}

	/**
	 * Finds and validates an exporter file associated with a process.
	 *
	 * @param int    $process_id Background process ID.
	 * @param string $guid       File identifier.
	 *
	 * @return array|\WP_Error
	 */
	public function get_process_file( $process_id, $guid ) {
		global $wpdb;

		$process_id     = absint( $process_id );
		$requested_guid = ltrim( wp_normalize_path( $guid ), '/' );

		if ( empty( $process_id ) || empty( $requested_guid ) ) {
			return new \WP_Error(
				'invalid_exporter_file_request',
				__( 'Invalid exporter file request.', 'tainacan' ),
				array( 'status' => 400 )
			);
		}

		$table   = $wpdb->prefix . 'tnc_bg_process';
		$process = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE ID = %d AND action = %s LIMIT 1",
				$process_id,
				'exporter'
			)
		);

		if ( ! $process ) {
			return new \WP_Error(
				'exporter_process_not_found',
				__( 'Exporter process not found.', 'tainacan' ),
				array( 'status' => 404 )
			);
		}

		if ( ! current_user_can( 'manage_tainacan' ) ) {
			return new \WP_Error(
				'exporter_file_forbidden',
				__( 'You are not allowed to access this exporter file.', 'tainacan' ),
				array( 'status' => 403 )
			);
		}

		$data = maybe_unserialize( $process->data );

		if (
			! is_array( $data ) ||
			empty( $data['output_files'] ) ||
			! is_array( $data['output_files'] )
		) {
			return new \WP_Error(
				'exporter_file_not_found',
				__( 'Exporter file not found.', 'tainacan' ),
				array( 'status' => 404 )
			);
		}

		foreach ( $data['output_files'] as $file ) {
			if ( ! is_array( $file ) ) {
				continue;
			}

			$file_guid = $this->get_file_guid( $file );

			if (
				empty( $file_guid ) ||
				! hash_equals( $file_guid, $requested_guid )
			) {
				continue;
			}

			if ( $this->is_expired( $file ) ) {
				return new \WP_Error(
					'exporter_file_expired',
					__( 'This exporter file has expired.', 'tainacan' ),
					array( 'status' => 410 )
				);
			}

			$file_path = $this->get_file_path( $requested_guid );

			if ( is_wp_error( $file_path ) ) {
				return $file_path;
			}

			return array(
				'path'    => $file_path,
				'file'    => $file,
				'process' => $process,
			);
		}

		return new \WP_Error(
			'exporter_file_not_associated',
			__(
				'The requested file does not belong to this exporter process.',
				'tainacan'
			),
			array( 'status' => 404 )
		);
	}

	/**
	 * Resolves a file path inside the Tainacan uploads directory.
	 *
	 * @param string $guid File identifier.
	 *
	 * @return string|\WP_Error
	 */
	public function get_file_path( $guid ) {
		$upload_dir   = wp_upload_dir();
		$tainacan_dir = realpath(
			trailingslashit( $upload_dir['basedir'] ) . 'tainacan'
		);

		if ( false === $tainacan_dir ) {
			return new \WP_Error(
				'tainacan_upload_directory_not_found',
				__( 'Tainacan upload directory not found.', 'tainacan' ),
				array( 'status' => 404 )
			);
		}

		$tainacan_dir = wp_normalize_path( $tainacan_dir );
		$file_path    = realpath(
			trailingslashit( $tainacan_dir ) . ltrim( $guid, '/' )
		);

		if ( false === $file_path ) {
			return new \WP_Error(
				'exporter_file_not_found',
				__( 'Exporter file not found.', 'tainacan' ),
				array( 'status' => 404 )
			);
		}

		$file_path = wp_normalize_path( $file_path );

		if (
			strpos(
				$file_path,
				trailingslashit( $tainacan_dir )
			) !== 0
		) {
			return new \WP_Error(
				'unauthorized_file_path',
				__( 'Unauthorized file path.', 'tainacan' ),
				array( 'status' => 403 )
			);
		}

		return $file_path;
	}

	/**
	 * Deletes expired exporter files.
	 *
	 * Processes are read in batches to avoid loading the complete history
	 * into memory at once.
	 *
	 * @return int Number of files removed.
	 */
	public function delete_expired_files() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';
		$last_id = 0;
		$batch_size = 100;
		$deleted_files = 0;

		do {
			$processes = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, data
					FROM {$table}
					WHERE ID > %d
					AND action = %s
					AND done = 1
					ORDER BY ID ASC
					LIMIT %d",
					$last_id,
					'exporter',
					$batch_size
				)
			);

			foreach ( $processes as $process ) {
				$last_id = (int) $process->ID;
				$data = maybe_unserialize( $process->data );

				if (
					! is_array( $data ) ||
					empty( $data['output_files'] ) ||
					! is_array( $data['output_files'] )
				) {
					continue;
				}

				$process_changed = false;

				foreach ( $data['output_files'] as $key => $file ) {
					if (
						! is_array( $file ) ||
						! empty( $file['deleted_at'] ) ||
						! $this->is_expired( $file )
					) {
						continue;
					}

					$guid = $this->get_file_guid( $file );

					if ( empty( $guid ) ) {
						continue;
					}

					$file_path    = $this->get_file_path( $guid );
					$file_deleted = false;

					if ( is_wp_error( $file_path ) ) {
						/*
						 * A missing file no longer needs another removal
						 * attempt. Other errors may be temporary and should
						 * be retried by the next cleanup.
						 */
						if (
							'exporter_file_not_found' ===
							$file_path->get_error_code()
						) {
							$file_deleted = true;
						}
					} else {
						wp_delete_file( $file_path );

						if ( ! file_exists( $file_path ) ) {
							$deleted_files++;
							$file_deleted = true;
						}
					}

					if ( ! $file_deleted ) {
						continue;
					}

					$data['output_files'][ $key ]['deleted_at'] = time();
					$process_changed = true;
				}

				if ( $process_changed ) {
					$wpdb->update(
						$table,
						array(
							'data' => maybe_serialize( $data ),
						),
						array(
							'ID' => $process->ID,
						),
						array( '%s' ),
						array( '%d' )
					);
				}
			}
		} while ( count( $processes ) === $batch_size );

		return $deleted_files;
	}

	/**
	 * Deletes all exporter files associated with a process.
	 *
	 * Used before the background-process record itself is deleted.
	 *
	 * @param object $process Background process database row.
	 *
	 * @return int Number of files removed.
	 */
	public function delete_process_files( $process ) {
		if (
			! is_object( $process ) ||
			empty( $process->action ) ||
			'exporter' !== $process->action
		) {
			return 0;
		}

		$data = maybe_unserialize( $process->data );

		if (
			! is_array( $data ) ||
			empty( $data['output_files'] ) ||
			! is_array( $data['output_files'] )
		) {
			return 0;
		}

		$deleted_files = 0;

		foreach ( $data['output_files'] as $file ) {
			if ( ! is_array( $file ) ) {
				continue;
			}

			$guid = $this->get_file_guid( $file );

			if ( empty( $guid ) ) {
				continue;
			}

			$file_path = $this->get_file_path( $guid );

			if ( is_wp_error( $file_path ) ) {
				continue;
			}

			wp_delete_file( $file_path );

			if ( ! file_exists( $file_path ) ) {
				$deleted_files++;
			}
		}

		return $deleted_files;
	}
}
