<?php

namespace Tainacan;

class System_Check {
	
	private $min_php_version = '5.6';

	private $mysql_min_version_check;
	private $mysql_rec_version_check;

	public  $mariadb                        = false;
	private $mysql_server_version           = null;
	private $health_check_mysql_rec_version = '5.0';
	private $health_check_mysql_min_version = '5.0';

	public function __construct() {
		$this->init();
	}

	public function init() {
		$this->prepare_sql_data();
	}
	
	public function admin_page() {
		include('admin-page.php');
	}
	
	public function test_php_version() {
		$testphpmin = version_compare( $this->min_php_version, PHP_VERSION, '<=' );
		$testphprec = version_compare( 7, PHP_VERSION, '<=' );
		
		if ($testphprec) {
			$class = 'good';
			$text = PHP_VERSION;
		} elseif ($testphpmin)  {
			$class = 'warning';
			$text = PHP_VERSION . ' - ' . __('Version supported, but you should consider upgrade to PHP 7', 'tainacan') . ': ';
		} else {
			$class = 'error';
			$text = PHP_VERSION . ' - ' . __('This PHP Version is not supported. Please upgrade to PHP 5.6 or higher!', 'tainacan');
		}
		
		printf( '<span class="%1$s"></span> %2$s', esc_attr( $class ), esc_html( $text ) );
		
	}

	private function prepare_sql_data() {
		global $wpdb;

		if ( method_exists( $wpdb, 'db_version' ) ) {
			if ( $wpdb->use_mysqli ) {
				// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_server_info
				$mysql_server_type = mysqli_get_server_info( $wpdb->dbh );
			} else {
				// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysql_get_server_info
				$mysql_server_type = mysql_get_server_info( $wpdb->dbh );
			}

			$this->mysql_server_version = $wpdb->get_var( 'SELECT VERSION()' );
		}

		if ( stristr( $mysql_server_type, 'mariadb' ) ) {
			$this->mariadb                        = true;
			$this->health_check_mysql_rec_version = '10.0';
			$this->health_check_mysql_min_version = '10.0';
		}

		$this->mysql_min_version_check = version_compare( $this->health_check_mysql_min_version, $this->mysql_server_version, '<=' );
		$this->mysql_rec_version_check = version_compare( $this->health_check_mysql_rec_version, $this->mysql_server_version, '<=' );
	}
	
	public function check_permalink_settings() {
		
		$settings = get_option( 'permalink_structure' );
		
		if ( empty($settings) ) {
			$class = 'error';
			$text =  sprintf(
				__('Tainacan requires your Permalink settings to be configured. Please visit %1$sPermalink settings%2$s and configure it.', 'tainacan'),
				'<a href="'.admin_url('options-permalink.php').'">',
				'</a>'
			);
		} else {
			
			$class = 'good';
			$text = 'Ok';
			
		}
		
		printf( '<span class="%1$s"></span> %2$s', esc_attr( $class ), $text );
		
	}
	
	public function check_php_timeout() {
		$time = ini_get('max_execution_time');
		$min = 30;
		$rec = 240;
		
		
		if ( $time < $min ) {
			$class = 'error';
			$text =  sprintf(
				__('Your current configuration is %ds. This is too little. Please increase it to at least 30s', 'tainacan'),
				$time
			);
		} elseif ( $time < $rec ) {
			$class = 'warning';
			$text =  sprintf(
				__('Your current configuration is %d seconds. This is fine, but you should consider increase it to at least 240 seconds if possible', 'tainacan'),
				$time
			);
		} else {
			$class = 'good';
			$text =  sprintf(
				__('Your current configuration is %ds. This is excellent.', 'tainacan'),
				$time
			);
		}
		
		printf( '<span class="%1$s"></span> %2$s', esc_attr( $class ), $text );
		
	}
	
	public function check_upload_permission() {
		
		$upload_dir = wp_upload_dir(); 
		$writable = is_writable($upload_dir['basedir']);
		
		if ( ! $writable ) {
			$class = 'error';
			$text =  sprintf(
				__('Your upload folder is not writable by the server. You must fix your folder permissions.', 'tainacan')
			);
		} else {
			$class = 'good';
			$text =  sprintf(
				__('Your upload folder is writable!', 'tainacan')
			);
		}
		
		printf( '<span class="%1$s"></span> %2$s', esc_attr( $class ), $text );
		
	}
	
	public function check_max_upload_size() {
		$upload_max_size = ini_get('upload_max_filesize');
		echo $upload_max_size;
	}

	

	/**
	 * Tests for WordPress version and outputs it.
	 *
	 * @return void It is an AJAX call.
	 */
	public function test_wordpress_version() {
		$core_current_version = get_bloginfo( 'version' );
		$core_updates         = get_core_updates();

		// Prepare for a class and text for later use.
		$text  = '';
		$class = '';

		if ( version_compare($core_current_version, '4.8') < 0 ) {
			
			$class = 'error';
			$text =  sprintf(
				__('Tainacan requires WordPress 4.8 or newer! Your version is %s. Please upgrade.'),
				$core_current_version
			);
			
		} elseif ( ! is_array( $core_updates ) ) {
			$class = 'warning';
			$text  = sprintf(
				// translators: %s: Your current version of WordPress.
				__( '%s - We were unable to check if any new versions are available.', 'health-check' ),
				$core_current_version
			);
		} else {
			foreach ( $core_updates as $core => $update ) {
				if ( 'upgrade' === $update->response ) {
					$current_version = explode( '.', $core_current_version );
					$new_version     = explode( '.', $update->version );

					$current_major = $current_version[0] . '.' . $current_version[1];
					$new_major     = $new_version[0] . '.' . $new_version[1];

					if ( $current_major !== $new_major ) {
						// This is a major version mismatch.
						$class = 'warning';
						$text  = sprintf(
							// translators: %1$s: Your current version of WordPress. %2$s The latest version of WordPress available.
							__( 'You are running WordPress %1$s. This version is supported, but you should always keep you WordPress installation updated. ( Latest version is %2$s )', 'tainacan' ),
							$core_current_version,
							$update->version
						);
					} else {
						// This is a minor version, sometimes considered more critical.
						$class = 'error';
						$text  = sprintf(
							// translators: %1$s: Your current version of WordPress. %2$s The latest version of WordPress available.
							__( 'You are running WordPress %1$s. ( Latest version is %2$s ) - We strongly urge you to update, as minor updates are often security related.', 'tainacan' ),
							$core_current_version,
							$update->version
						);
					}
				} else {
					$class = 'good';
					$text  = $core_current_version;
				}
			}
		}

		printf( '<span class="%1$s"></span> %2$s', esc_attr( $class ), esc_html( $text ) );
	}

	
	public function child_test_php_extension_availability( $extension = null, $function = null ) {
		// If no extension or function is passed, claim to fail testing, as we have nothing to test against.
		if ( null === $extension && null === $function ) {
			return false;
		}

		$available = true;

		if ( null !== $extension && ! extension_loaded( $extension ) ) {
			$available = false;
		}

		if ( null !== $function && ! function_exists( $function ) ) {
			$available = false;
		}

		return $available;
	}

	public function test_php_extensions() {
		/*
		 * An array representing all the modules we wish to test for.
		 *
		 * array $modules {
		 *     An associated array of modules to test for.
		 *
		 *     array $module {
		 *         An associated array of module properties used during testing.
		 *         One of either `$function` or `$extension` must be provided, or they will fail by default.
		 *
		 *         string $function     Optional. A function name to test for the existence of.
		 *         string $extension    Optional. An extension to check if is loaded in PHP.
		 *         bool   $required     Is this a required feature or not.
		 *         string $fallback_for Optional. The module this module replaces as a fallback.
		 *     }
		 * }
		 */
		$modules = array(
			// 'bcmath'    => array(
			// 	'function' => 'bcadd',
			// 	'required' => false,
			// ),
			'curl'      => array(
				'function' => 'curl_version',
				'required' => false,
			),
			'exif'      => array(
				'function' => 'exif_read_data',
				'required' => false,
				'message' => __('This is used to read metadata from photos.', 'tainacan')
			),
			// 'filter'    => array(
			// 	'function' => 'filter_list',
			// 	'required' => false,
			// ),
			// 'fileinfo'  => array(
			// 	'function' => 'finfo_file',
			// 	'required' => false,
			// ),
			// 'mod_xml'   => array(
			// 	'extension' => 'libxml',
			// 	'required'  => false,
			// ),
			// 'mysqli'    => array(
			// 	'function' => 'mysqli_connect',
			// 	'required' => false,
			// ),
			// 'libsodium' => array(
			// 	'function' => 'sodium_compare',
			// 	'required' => false,
			// ),
			// 'openssl'   => array(
			// 	'function' => 'openssl_encrypt',
			// 	'required' => false,
			// ),
			// 'pcre'      => array(
			// 	'function' => 'preg_match',
			// 	'required' => false,
			// ),
			'imagick'   => array(
				'extension' => 'imagick',
				'required'  => false,
				'message' => __('This is used, among other things, to automatically extract the first page of PDFs to use as a thumbnail.', 'tainacan')
			),
			'gd'        => array(
				'extension'    => 'gd',
				'required'     => false,
				'fallback_for' => 'imagick',
				'message' => __('This is used for image processing, such as resizing and cropping images.', 'tainacan')
			),
			// 'mcrypt'    => array(
			// 	'extension'    => 'mcrypt',
			// 	'required'     => false,
			// 	'fallback_for' => 'libsodium',
			// ),
			// 'xmlreader' => array(
			// 	'extension'    => 'xmlreader',
			// 	'required'     => false,
			// 	'fallback_for' => 'xml',
			// ),
			'zlib'      => array(
				'extension'    => 'zlib',
				'required'     => false,
				'fallback_for' => 'zip',
			),
		);

		$failures = array();

		foreach ( $modules as $library => $module ) {
			$extension = ( isset( $module['extension'] ) ? $module['extension'] : null );
			$function  = ( isset( $module['function'] ) ? $module['function'] : null );

			// If this module is a fallback for another function, check if that other function passed.
			if ( isset( $module['fallback_for'] ) ) {
				/*
				 * If that other function has a failure, mark this module as required for normal operations.
				 * If that other function hasn't failed, skip this test as it's only a fallback.
				 */
				if ( isset( $failures[ $module['fallback_for'] ] ) ) {
					$module['required'] = true;
				} else {
					continue;
				}
			}

			if ( ! $this->child_test_php_extension_availability( $extension, $function ) ) {
				$failures[ $library ] = sprintf(
					'<span class="%s"></span> %s',
					( $module['required'] ? 'error' : 'warning' ),
					sprintf(
						// translators: %1$2: If a module is required or recommended. %2$s: The module name.
						__( 'The %1$s module %2$s is not installed, or has been disabled.', 'tainacan' ),
						( $module['required'] ? __( 'required', 'health-check' ) : __( 'optional', 'health-check' ) ),
						$library
					)
				);
				if ( isset($module['message']) ) {
					$failures[ $library ] .= ' ' . $module['message'];
				}
			}
		}

		if ( ! empty( $failures ) ) {
			echo '<ul>';

			foreach ( $failures as $failure ) {
				printf(
					'<li>%s</li>',
					$failure
				);
			}

			echo '</ul>';
		} else {
			printf(
				'<span class="good"></span> %s',
				__( 'All required and recommended modules are installed.', 'health-check' )
			);
		}
	}

	public function test_sql_server() {
		$status = 'good';
		$notice = array();

		$db_dropin = file_exists( WP_CONTENT_DIR . '/db.php' );

		if ( ! $this->mysql_rec_version_check ) {
			$status   = 'warning';
			$notice[] = sprintf(
				// translators: %1$s: The database engine in use (MySQL or MariaDB). %2$s: Database server recommended version number.
				esc_html__( 'We strongly recommend running %1$s version %2$s or higher. Future features may depend on this versions.', 'tainacan' ),
				( $this->mariadb ? 'MariaDB' : 'MySQL' ),
				$this->health_check_mysql_rec_version
			);
		}

		if ( ! $this->mysql_min_version_check ) {
			$status   = 'error';
			$notice[] = sprintf(
				// translators: %1$s: The database engine in use (MySQL or MariaDB). %2$s: Database server minimum version number.
				esc_html__( 'Tainacan requires %1$s version %2$s or higher.', 'tainacan' ),
				( $this->mariadb ? 'MariaDB' : 'MySQL' ),
				$this->health_check_mysql_min_version
			);
		}

		if ( $db_dropin ) {
			// translators: %s: The database engine in use (MySQL or MariaDB).
			$notice[] = wp_kses(
				sprintf(
					// translators: %s: The name of the database engine being used.
					__( 'You are using a <code>wp-content/db.php</code> drop-in which might mean that a %s database is not being used.', 'tainacan' ),
					( $this->mariadb ? 'MariaDB' : 'MySQL' )
				),
				array(
					'code' => true,
				)
			);
		}

		printf(
			'<span class="%s"></span> %s',
			esc_attr( $status ),
			sprintf(
				'%s%s',
				esc_html( $this->mysql_server_version ),
				( ! empty( $notice ) ? '<br> - ' . implode( '<br>', $notice ) : '' )
			)
		);
	}

	public function test_utf8mb4_support() {
		global $wpdb;

		if ( ! $this->mariadb ) {
			if ( version_compare( $this->mysql_server_version, '5.5.3', '<' ) ) {
				printf(
					'<span class="warning"></span> %s',
					sprintf(
						/* translators: %s: Number of version. */
						esc_html__( 'WordPress\' utf8mb4 support requires MySQL version %s or greater', 'health-check' ),
						'5.5.3'
					)
				);
			} else {
				printf(
					'<span class="good"></span> %s',
					esc_html__( 'Your MySQL version supports utf8mb4', 'health-check' )
				);
			}
		} else { // MariaDB introduced utf8mb4 support in 5.5.0
			if ( version_compare( $this->mysql_server_version, '5.5.0', '<' ) ) {
				printf(
					'<span class="warning"></span> %s',
					sprintf(
						/* translators: %s: Number of version. */
						esc_html__( 'WordPress\' utf8mb4 support requires MariaDB version %s or greater', 'health-check' ),
						'5.5.0'
					)
				);
			} else {
				printf(
					'<span class="good"></span> %s',
					esc_html__( 'Your MariaDB version supports utf8mb4', 'health-check' )
				);
			}
		}

		if ( $wpdb->use_mysqli ) {
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_client_info
			$mysql_client_version = mysqli_get_client_info();
		} else {
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysql_get_client_info
			$mysql_client_version = mysql_get_client_info();
		}

		/*
		 * libmysql has supported utf8mb4 since 5.5.3, same as the MySQL server.
		 * mysqlnd has supported utf8mb4 since 5.0.9.
		 */
		if ( false !== strpos( $mysql_client_version, 'mysqlnd' ) ) {
			$mysql_client_version = preg_replace( '/^\D+([\d.]+).*/', '$1', $mysql_client_version );
			if ( version_compare( $mysql_client_version, '5.0.9', '<' ) ) {
				printf(
					'<br><span class="warning"></span> %s',
					sprintf(
						/* translators: %1$s: Name of the library, %2$s: Number of version. */
						__( 'WordPress\' utf8mb4 support requires MySQL client library (%1$s) version %2$s or newer.', 'health-check' ),
						'mysqlnd',
						'5.0.9'
					)
				);
			}
		} else {
			if ( version_compare( $mysql_client_version, '5.5.3', '<' ) ) {
				printf(
					'<br><span class="warning"></span> %s',
					sprintf(
						/* translators: %1$s: Name of the library, %2$s: Number of version. */
						__( 'WordPress\' utf8mb4 support requires MySQL client library (%1$s) version %2$s or newer.', 'health-check' ),
						'libmysql',
						'5.5.3'
					)
				);
			}
		}
	}

}



?>
