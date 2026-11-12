<?php

namespace Tainacan\Tests;

/**
 * Exporter used to test controlled abortion.
 */
class Aborting_Exporter_Test_Double extends \Tainacan\Exporter\Exporter {

	public function process_item( $item, $metadata ) {
		return false;
	}

	public function __construct( $attributes = array() ) {
		parent::__construct( $attributes );

		$this->set_steps(
			array(
				array(
					'name'           => 'Abort exporter',
					'progress_label' => 'Aborting exporter',
					'callback'       => 'abort_export',
					'total'          => 1,
				),
			)
		);
	}

	public function abort_export() {
		$this->abort();

		return false;
	}

}

/**
 * Exporter used to test an exception during execution.
 */
class Throwing_Exporter_Test_Double extends \Tainacan\Exporter\Exporter {

	public function process_item( $item, $metadata ) {
		return false;
	}

	public function __construct( $attributes = array() ) {
		parent::__construct( $attributes );

		$this->set_steps(
			array(
				array(
					'name'           => 'Throw exporter exception',
					'progress_label' => 'Throwing exporter exception',
					'callback'       => 'throw_exporter_exception',
					'total'          => 1,
				),
			)
		);
	}

	public function throw_exporter_exception() {
		throw new \Error( 'Test exporter error.' );
	}

}

/**
 * Tests the exporter files lifecycle service.
 *
 * @package Test_Tainacan
 */
class Exporter_Files_Test extends TAINACAN_UnitApiTestCase {

	/**
	 * Exporter files service.
	 *
	 * @var \Tainacan\Exporter_Files
	 */
	private $exporter_files;

	/**
	 * Files created during a test.
	 *
	 * @var array
	 */
	private $created_files = array();

	/**
	 * Background processes created during a test.
	 *
	 * @var array
	 */
	private $created_processes = array();

	/**
	 * Prepares each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->created_files     = array();
		$this->created_processes = array();

		delete_option( 'tainacan_option_exporter_files_expiration_days' );
		remove_all_filters( 'tainacan-exporter-files-expiration-days' );

		$this->exporter_files = \Tainacan\Exporter_Files::get_instance();
	}

	/**
	 * Restores the expiration configuration.
	 */
	public function tearDown(): void {
		global $wpdb;

		delete_option( 'tainacan_option_exporter_files_expiration_days' );
		remove_all_filters( 'tainacan-exporter-files-expiration-days' );

		foreach ( $this->created_files as $file_path ) {
			if ( file_exists( $file_path ) ) {
				wp_delete_file( $file_path );
			}
		}

		foreach ( $this->created_processes as $process_id ) {
			$wpdb->delete(
				$wpdb->prefix . 'tnc_bg_process',
				array( 'ID' => $process_id ),
				array( '%d' )
			);
		}

		\Tainacan\Exporter_Files::deactivate();
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * Tests the default availability period.
	 */
	public function test_default_expiration_period() {
		$this->assertSame(
			7,
			$this->exporter_files->get_expiration_days()
		);

		$this->assertSame(
			7 * DAY_IN_SECONDS,
			$this->exporter_files->get_expiration_period()
		);
	}

	/**
	 * Provides valid expiration periods.
	 *
	 * @return array
	 */
	public function expiration_days_provider() {
		return array(
			'minimum' => array( 1 ),
			'default' => array( 7 ),
			'maximum' => array( 30 ),
		);
	}

	/**
	 * Tests valid configured expiration periods.
	 *
	 * @dataProvider expiration_days_provider
	 *
	 * @param int $expiration_days Expiration period in days.
	 */
	public function test_configured_expiration_days( $expiration_days ) {
		update_option(
			'tainacan_option_exporter_files_expiration_days',
			$expiration_days
		);

		$this->assertSame(
			$expiration_days,
			$this->exporter_files->get_expiration_days()
		);

		$this->assertSame(
			$expiration_days * DAY_IN_SECONDS,
			$this->exporter_files->get_expiration_period()
		);
	}

	/**
	 * Tests that configured values below the minimum are limited.
	 */
	public function test_configured_value_below_minimum() {
		update_option(
			'tainacan_option_exporter_files_expiration_days',
			0
		);

		$this->assertSame(
			1,
			$this->exporter_files->get_expiration_days()
		);
	}

	/**
	 * Tests that configured values above the maximum are limited.
	 */
	public function test_configured_value_above_maximum() {
		update_option(
			'tainacan_option_exporter_files_expiration_days',
			31
		);

		$this->assertSame(
			30,
			$this->exporter_files->get_expiration_days()
		);
	}

	/**
	 * Tests that the expiration filter cannot reduce the period below one day.
	 */
	public function test_filter_value_below_minimum() {
		$filter = function () {
			return 0;
		};

		add_filter(
			'tainacan-exporter-files-expiration-days',
			$filter
		);

		$this->assertSame(
			1,
			$this->exporter_files->get_expiration_days()
		);

		remove_filter(
			'tainacan-exporter-files-expiration-days',
			$filter
		);
	}

	/**
	 * Tests that the expiration filter cannot exceed thirty days.
	 */
	public function test_filter_value_above_maximum() {
		$filter = function () {
			return 999;
		};

		add_filter(
			'tainacan-exporter-files-expiration-days',
			$filter
		);

		$this->assertSame(
			30,
			$this->exporter_files->get_expiration_days()
		);

		remove_filter(
			'tainacan-exporter-files-expiration-days',
			$filter
		);
	}

	/**
	 * Tests preparing an exporter file for download.
	 */
	public function test_prepare_output_files() {
		$filename = trailingslashit( wp_upload_dir()['basedir'] )
			. 'tainacan/exporter/exporter-files-test.csv';

		$output_files = array(
			array(
				'filename' => $filename,
			),
		);

		$expected_minimum_expiration = time()
			+ $this->exporter_files->get_expiration_period();

		$prepared_files = $this->exporter_files->prepare_output_files(
			$output_files,
			123
		);

		$expected_maximum_expiration = time()
			+ $this->exporter_files->get_expiration_period();

		$this->assertCount( 1, $prepared_files );
		$this->assertSame(
			'exporter/exporter-files-test.csv',
			$prepared_files[0]['guid']
		);
		$this->assertGreaterThanOrEqual(
			$expected_minimum_expiration,
			$prepared_files[0]['expires_at']
		);
		$this->assertLessThanOrEqual(
			$expected_maximum_expiration,
			$prepared_files[0]['expires_at']
		);
		$this->assertStringContainsString(
			'guid=exporter%2Fexporter-files-test.csv',
			$prepared_files[0]['url']
		);
		$this->assertStringContainsString(
			'process_id=123',
			$prepared_files[0]['url']
		);
		$this->assertStringContainsString(
			'_wpnonce=[nonce]',
			$prepared_files[0]['url']
		);
	}

	/**
	 * Tests that preparing a file twice does not extend its expiration.
	 */
	public function test_prepare_output_files_preserves_existing_expiration() {
		$expires_at = time() + DAY_IN_SECONDS;
		$filename   = trailingslashit( wp_upload_dir()['basedir'] )
			. 'tainacan/exporter/exporter-files-existing-expiration.csv';

		$output_files = array(
			array(
				'filename'   => $filename,
				'expires_at' => $expires_at,
			),
		);

		$prepared_files = $this->exporter_files->prepare_output_files(
			$output_files,
			123
		);

		$this->assertSame(
			$expires_at,
			$prepared_files[0]['expires_at']
		);
	}

	/**
	 * Tests preparing an invalid output files value.
	 */
	public function test_prepare_output_files_rejects_non_array_value() {
		$this->assertSame(
			array(),
			$this->exporter_files->prepare_output_files( 'invalid', 123 )
		);
	}

	/**
	 * Tests that an invalid process identifier does not modify the files.
	 */
	public function test_prepare_output_files_rejects_invalid_process_id() {
		$output_files = array(
			array(
				'filename' => '/tmp/exporter-files-test.csv',
			),
		);

		$this->assertSame(
			$output_files,
			$this->exporter_files->prepare_output_files( $output_files, 0 )
		);
	}

	/**
	 * Tests that files outside the Tainacan exporter directory are ignored.
	 */
	public function test_prepare_output_files_ignores_unrelated_paths() {
		$output_files = array(
			array(
				'filename' => '/tmp/exporter-files-test.csv',
			),
		);

		$this->assertSame(
			$output_files,
			$this->exporter_files->prepare_output_files(
				$output_files,
				123
			)
		);
	}

	/**
	 * Provides exporter failure scenarios.
	 *
	 * @return array
	 */
	public function exporter_failure_provider() {
		return array(
			'controlled abort' => array(
				Aborting_Exporter_Test_Double::class,
				'Process aborted by Exporter',
			),
			'exception during execution' => array(
				Throwing_Exporter_Test_Double::class,
				'Test exporter error.',
			),
		);
	}

/**
 * Tests that files from failed exporters receive expiration metadata.
 *
 * @dataProvider exporter_failure_provider
 *
 * @param string $exporter_class   Exporter class.
 * @param string $expected_message Expected exception message.
 */
	public function test_failed_exporter_files_receive_expiration(
		$exporter_class,
		$expected_message
	) {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$file = $this->create_exporter_file(
			'failed-exporter-test.csv'
		);

		$process_id = $this->create_exporter_process(
			array( $file ),
			$user_id
		);

		$batch = (object) array(
			'key'            => $process_id,
			'data'           => array(
				'class_name'   => $exporter_class,
				'output_files' => array( $file ),
				'send_email'   => 1,
			),
			'progress_label' => '',
			'progress_value' => 0,
			'output'         => '',
		);

		$sent_emails = 0;
		$mail_filter = function ( $return ) use ( &$sent_emails ) {
			$sent_emails++;

			return true;
		};

		add_filter( 'pre_wp_mail', $mail_filter );

		$caught_throwable = null;

		try {
			$background_exporter = new \Tainacan\Background_Exporter();
			$background_exporter->task( $batch );
		} catch ( \Throwable $throwable ) {
			$caught_throwable = $throwable;
		}

		remove_filter( 'pre_wp_mail', $mail_filter );

		$this->assertInstanceOf( \Throwable::class, $caught_throwable );
		$this->assertSame(
			$expected_message,
			$caught_throwable->getMessage()
		);
		$this->assertSame( 0, $sent_emails );

		$process_data = $this->get_process_data( $process_id );
		$prepared_file = $process_data['output_files'][0];

		$this->assertNotEmpty( $prepared_file['expires_at'] );
		$this->assertGreaterThan( time(), $prepared_file['expires_at'] );
		$this->assertStringContainsString(
			'process_id=' . $process_id,
			$prepared_file['url']
		);
	}

	/**
	 * Creates a temporary exporter file.
	 *
	 * @param string $file_name File name.
	 *
	 * @return array
	 */
	private function create_exporter_file( $file_name ) {
		$upload_dir   = wp_upload_dir();
		$exporter_dir = trailingslashit( $upload_dir['basedir'] )
			. 'tainacan/exporter';

		wp_mkdir_p( $exporter_dir );

		$file_name = wp_unique_filename( $exporter_dir, $file_name );
		$file_path = trailingslashit( $exporter_dir ) . $file_name;

		$result = file_put_contents(
			$file_path,
			'Exporter files test content.'
		);

		if ( false === $result ) {
			throw new \RuntimeException(
				'Could not create the temporary exporter file.'
			);
		}

		$this->created_files[] = $file_path;

		return array(
			'filename' => $file_path,
			'guid'     => 'exporter/' . $file_name,
		);
	}

	/**
	 * Creates an exporter background process.
	 *
	 * @param array  $output_files Exporter output files.
	 * @param int    $user_id      Process owner.
	 * @param string $output       Process output.
	 *
	 * @return int
	 */
	private function create_exporter_process(
		$output_files,
		$user_id = 0,
		$output = ''
	) {
		global $wpdb;

		$inserted = $wpdb->insert(
			$wpdb->prefix . 'tnc_bg_process',
			array(
				'user_id'        => $user_id,
				'queued_on'      => gmdate( 'Y-m-d H:i:s' ),
				'processed_last' => gmdate( 'Y-m-d H:i:s' ),
				'data'           => maybe_serialize(
					array(
						'output_files' => $output_files,
					)
				),
				'action'         => 'exporter',
				'name'           => 'Exporter files test process',
				'done'           => 1,
				'status'         => 'finished',
				'output'         => $output,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);

		if ( false === $inserted ) {
			throw new \RuntimeException(
				'Could not create the exporter process: '
				. $wpdb->last_error
			);
		}

		$process_id = (int) $wpdb->insert_id;

		$this->created_processes[] = $process_id;

		return $process_id;
	}

	/**
	 * Gets a background process from the database.
	 *
	 * @param int $process_id Process ID.
	 *
	 * @return object|null
	 */
	private function get_process( $process_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
				FROM {$wpdb->prefix}tnc_bg_process
				WHERE ID = %d",
				$process_id
			)
		);
	}

	/**
	 * Gets a background process ID from the database.
	 *
	 * @param int $process_id Process ID.
	 *
	 * @return string|null
	 */
	private function get_process_id( $process_id ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->prefix}tnc_bg_process
				WHERE ID = %d",
				$process_id
			)
		);
	}

	/**
	 * Gets the unserialized data of a background process.
	 *
	 * @param int $process_id Process ID.
	 *
	 * @return array|null
	 */
	private function get_process_data( $process_id ) {
		$process = $this->get_process( $process_id );

		if ( ! is_object( $process ) ) {
			return null;
		}

		$data = maybe_unserialize( $process->data );

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Requests the deletion of a background process.
	 *
	 * @param int $process_id Process ID.
	 *
	 * @return \WP_REST_Response
	 */
	private function request_process_deletion( $process_id ) {
		$request = new \WP_REST_Request(
			'DELETE',
			'/tainacan/v2/bg-processes/' . $process_id
		);
		$request->set_param( 'id', $process_id );

		$controller = new \Tainacan\API\EndPoints\REST_Background_Processes_Controller();

		return $controller->delete_item( $request );
	}

	/**
	 * Tests access to an available exporter file.
	 */
	public function test_get_process_file_allows_authorized_user() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$file               = $this->create_exporter_file(
			'exporter-files-available.csv'
		);
		$file['expires_at'] = time() + DAY_IN_SECONDS;

		$process_id = $this->create_exporter_process(
			array( $file ),
			$user_id
		);

		$result = $this->exporter_files->get_process_file(
			$process_id,
			$file['guid']
		);

		$this->assertIsArray( $result );
		$this->assertSame(
			wp_normalize_path( $file['filename'] ),
			$result['path']
		);
	}

	/**
	 * Tests access denial after an exporter file expires.
	 */
	public function test_get_process_file_rejects_expired_file() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$file               = $this->create_exporter_file(
			'exporter-files-expired.csv'
		);
		$file['expires_at'] = time() - 1;

		$process_id = $this->create_exporter_process(
			array( $file ),
			$user_id
		);

		$result = $this->exporter_files->get_process_file(
			$process_id,
			$file['guid']
		);

		$this->assertWPError( $result );
		$this->assertSame(
			'exporter_file_expired',
			$result->get_error_code()
		);
		$this->assertSame(
			410,
			$result->get_error_data()['status']
		);
	}

	/**
	 * Tests access denial for a user without Tainacan management permission.
	 */
	public function test_get_process_file_rejects_unauthorized_user() {
		$owner_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		$user_id  = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);

		$file               = $this->create_exporter_file(
			'exporter-files-forbidden.csv'
		);
		$file['expires_at'] = time() + DAY_IN_SECONDS;

		$process_id = $this->create_exporter_process(
			array( $file ),
			$owner_id
		);

		wp_set_current_user( $user_id );

		$result = $this->exporter_files->get_process_file(
			$process_id,
			$file['guid']
		);

		$this->assertWPError( $result );
		$this->assertSame(
			'exporter_file_forbidden',
			$result->get_error_code()
		);
		$this->assertSame(
			403,
			$result->get_error_data()['status']
		);

		$nonexistent_result = $this->exporter_files->get_process_file(
			999999999,
			'exporter/nonexistent.csv'
		);

		$this->assertWPError( $nonexistent_result );
		$this->assertSame(
			'exporter_file_forbidden',
			$nonexistent_result->get_error_code()
		);
		$this->assertSame(
			403,
			$nonexistent_result->get_error_data()['status']
		);
	}

	/**
	 * Tests an invalid exporter file request.
	 */
	public function test_get_process_file_rejects_invalid_request() {
		$result = $this->exporter_files->get_process_file(
			0,
			''
		);

		$this->assertWPError( $result );
		$this->assertSame(
			'invalid_exporter_file_request',
			$result->get_error_code()
		);
		$this->assertSame(
			400,
			$result->get_error_data()['status']
		);
	}

	/**
	 * Tests a request for a nonexistent exporter process.
	 */
	public function test_get_process_file_rejects_nonexistent_process() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$result = $this->exporter_files->get_process_file(
			999999999,
			'exporter/nonexistent.csv'
		);

		$this->assertWPError( $result );
		$this->assertSame(
			'exporter_process_not_found',
			$result->get_error_code()
		);
		$this->assertSame(
			404,
			$result->get_error_data()['status']
		);
	}

	/**
	 * Tests that a file cannot be downloaded through another process.
	 */
	public function test_get_process_file_rejects_file_from_another_process() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$associated_file               = $this->create_exporter_file(
			'exporter-files-associated.csv'
		);
		$associated_file['expires_at'] = time() + DAY_IN_SECONDS;

		$other_file               = $this->create_exporter_file(
			'exporter-files-other-process.csv'
		);
		$other_file['expires_at'] = time() + DAY_IN_SECONDS;

		$process_id = $this->create_exporter_process(
			array( $associated_file ),
			$user_id
		);

		$result = $this->exporter_files->get_process_file(
			$process_id,
			$other_file['guid']
		);

		$this->assertWPError( $result );
		$this->assertSame(
			'exporter_file_not_associated',
			$result->get_error_code()
		);
		$this->assertSame(
			404,
			$result->get_error_data()['status']
		);
	}

	/**
	 * Tests an associated exporter file that is missing from disk.
	 */
	public function test_get_process_file_rejects_missing_file() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$file               = $this->create_exporter_file(
			'exporter-files-missing.csv'
		);
		$file['expires_at'] = time() + DAY_IN_SECONDS;

		$process_id = $this->create_exporter_process(
			array( $file ),
			$user_id
		);

		wp_delete_file( $file['filename'] );

		$result = $this->exporter_files->get_process_file(
			$process_id,
			$file['guid']
		);

		$this->assertWPError( $result );
		$this->assertSame(
			'exporter_file_not_found',
			$result->get_error_code()
		);
		$this->assertSame(
			404,
			$result->get_error_data()['status']
		);
	}

	/**
	 * Tests that a legacy file without expiration remains available.
	 */
	public function test_get_process_file_allows_legacy_file_without_expiration() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$file = $this->create_exporter_file(
			'exporter-files-legacy.csv'
		);

		$process_id = $this->create_exporter_process(
			array( $file ),
			$user_id
		);

		$result = $this->exporter_files->get_process_file(
			$process_id,
			$file['guid']
		);

		$this->assertIsArray( $result );
		$this->assertSame(
			wp_normalize_path( $file['filename'] ),
			$result['path']
		);
	}

	/**
	 * Tests that cleanup removes only expired exporter files.
	 */
	public function test_delete_expired_files_removes_only_expired_files() {
		$expired_file               = $this->create_exporter_file(
			'exporter-files-cleanup-expired.csv'
		);
		$expired_file['expires_at'] = time() - 1;

		$available_file               = $this->create_exporter_file(
			'exporter-files-cleanup-available.csv'
		);
		$available_file['expires_at'] = time() + DAY_IN_SECONDS;

		$process_id = $this->create_exporter_process(
			array(
				$expired_file,
				$available_file,
			)
		);

		$deleted_files = $this->exporter_files->delete_expired_files();

		$this->assertSame( 1, $deleted_files );
		$this->assertFileDoesNotExist( $expired_file['filename'] );
		$this->assertFileExists( $available_file['filename'] );

		$process_data = $this->get_process_data( $process_id );

		$this->assertNotEmpty(
			$process_data['output_files'][0]['deleted_at']
		);
		$this->assertArrayNotHasKey(
			'deleted_at',
			$process_data['output_files'][1]
		);
	}

	/**
	 * Tests that cleanup preserves legacy files without expiration.
	 */
	public function test_delete_expired_files_preserves_legacy_files() {
		$legacy_file = $this->create_exporter_file(
			'exporter-files-cleanup-legacy.csv'
		);

		$process_id = $this->create_exporter_process(
			array( $legacy_file )
		);

		$deleted_files = $this->exporter_files->delete_expired_files();

		$this->assertSame( 0, $deleted_files );
		$this->assertFileExists( $legacy_file['filename'] );

		$process_data = $this->get_process_data( $process_id );

		$this->assertArrayNotHasKey(
			'deleted_at',
			$process_data['output_files'][0]
		);
	}

	/**
	 * Tests that deleting a process removes all its exporter files.
	 */
	public function test_delete_process_removes_associated_exporter_files() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);
		wp_set_current_user( $user_id );

		$first_file = $this->create_exporter_file(
			'exporter-files-delete-process-first.csv'
		);
		$second_file = $this->create_exporter_file(
			'exporter-files-delete-process-second.csv'
		);
		$other_file = $this->create_exporter_file(
			'exporter-files-delete-other-process.csv'
		);

		$process_id = $this->create_exporter_process(
			array(
				$first_file,
				$second_file,
			),
			$user_id
		);

		$other_process_id = $this->create_exporter_process(
			array( $other_file ),
			$user_id
		);

		$response = $this->request_process_deletion( $process_id );

		$this->assertInstanceOf(
			\WP_REST_Response::class,
			$response
		);
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 1, $response->get_data() );

		$this->assertFileDoesNotExist( $first_file['filename'] );
		$this->assertFileDoesNotExist( $second_file['filename'] );
		$this->assertFileExists( $other_file['filename'] );

		$this->assertNull(
			$this->get_process_id( $process_id )
		);

		$this->assertSame(
			(string) $other_process_id,
			$this->get_process_id( $other_process_id )
		);
	}

	/**
	 * Tests that a user cannot delete another user's process.
	 */
	public function test_user_cannot_delete_another_users_process() {
		$owner_id = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);
		$other_user_id = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);

		$file = $this->create_exporter_file(
			'exporter-files-delete-forbidden.csv'
		);

		$process_id = $this->create_exporter_process(
			array( $file ),
			$owner_id
		);

		wp_set_current_user( $other_user_id );

		$response = $this->request_process_deletion( $process_id );

		$this->assertInstanceOf(
			\WP_REST_Response::class,
			$response
		);
		$this->assertSame( 403, $response->get_status() );
		$this->assertSame(
			'background_process_forbidden',
			$response->get_data()['code']
		);
		$this->assertFileExists( $file['filename'] );

		$this->assertSame(
			(string) $process_id,
			$this->get_process_id( $process_id )
		);
	}

	/**
	 * Tests that an administrator can delete another user's process.
	 */
	public function test_administrator_can_delete_another_users_process() {
		$owner_id = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);
		$administrator_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);

		$file = $this->create_exporter_file(
			'exporter-files-delete-by-administrator.csv'
		);

		$process_id = $this->create_exporter_process(
			array( $file ),
			$owner_id
		);

		wp_set_current_user( $administrator_id );

		$response = $this->request_process_deletion( $process_id );

		$this->assertInstanceOf(
			\WP_REST_Response::class,
			$response
		);
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 1, $response->get_data() );
		$this->assertFileDoesNotExist( $file['filename'] );

		$this->assertNull(
			$this->get_process_id( $process_id )
		);
	}

	/**
	 * Provides values for the administrative expiration sanitizer.
	 *
	 * @return array
	 */
	public function expiration_sanitizer_provider() {
		return array(
			'zero returns default'       => array( 0, 7 ),
			'minimum remains minimum'    => array( 1, 1 ),
			'default remains default'    => array( 7, 7 ),
			'maximum remains maximum'    => array( 30, 30 ),
			'above maximum is limited'   => array( 31, 30 ),
			'large value is limited'     => array( 999, 30 ),
		);
	}

	/**
	 * Tests the administrative expiration sanitizer.
	 *
	 * @dataProvider expiration_sanitizer_provider
	 *
	 * @param int $input    Input value.
	 * @param int $expected Expected sanitized value.
	 */
	public function test_expiration_days_sanitizer( $input, $expected ) {
		$reflection = new \ReflectionClass( '\Tainacan\Settings' );
		$settings   = $reflection->newInstanceWithoutConstructor();

		$this->assertSame(
			$expected,
			$settings->sanitize_exporter_files_expiration_days( $input )
		);
	}

	/**
	 * Tests cleanup bookkeeping when an expired file is already missing.
	 */
	public function test_delete_expired_files_marks_missing_file_as_deleted() {
		$file               = $this->create_exporter_file(
			'exporter-files-cleanup-missing.csv'
		);
		$file['expires_at'] = time() - 1;

		$process_id = $this->create_exporter_process(
			array( $file )
		);

		wp_delete_file( $file['filename'] );

		$deleted_files = $this->exporter_files->delete_expired_files();

		$this->assertSame( 0, $deleted_files );
		$this->assertFileDoesNotExist( $file['filename'] );

		$process_data = $this->get_process_data( $process_id );

		$this->assertNotEmpty(
			$process_data['output_files'][0]['deleted_at']
		);
	}

	/**
	 * Tests scheduling and removal of the exporter cleanup event.
	 */
	public function test_cleanup_schedule_lifecycle() {
		\Tainacan\Exporter_Files::deactivate();

		$this->assertFalse(
			wp_next_scheduled(
				\Tainacan\Exporter_Files::CRON_HOOK
			)
		);

		$this->exporter_files->schedule_cleanup();

		$this->assertNotFalse(
			wp_next_scheduled(
				\Tainacan\Exporter_Files::CRON_HOOK
			)
		);

		\Tainacan\Exporter_Files::deactivate();

		$this->assertFalse(
			wp_next_scheduled(
				\Tainacan\Exporter_Files::CRON_HOOK
			)
		);
	}

	/**
	 * Tests the structured process response for an available file.
	 */
	public function test_prepare_process_response_for_available_file() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$file               = $this->create_exporter_file(
			'exporter-files-response-available.csv'
		);
		$file['expires_at'] = time() + DAY_IN_SECONDS;
		$file['url']        = 'https://example.org/old-export-url';

		$process_id = $this->create_exporter_process(
			array( $file ),
			$user_id,
			'<a href="' . $file['url'] . '">Download</a>'
		);

		$process = $this->get_process( $process_id );

		$response = $this->exporter_files->prepare_process_for_response(
			$process
		);

		$this->assertTrue( $response->output_files_download_allowed );
		$this->assertFalse( $response->output_files_expired );
		$this->assertCount( 1, $response->exporter_files );
		$this->assertFalse( $response->exporter_files[0]['expired'] );
		$this->assertNotEmpty( $response->exporter_files[0]['url'] );
		$this->assertStringContainsString(
			'process_id=' . $process_id,
			$response->exporter_files[0]['url']
		);
		$this->assertSame(
			wp_date(
				'Y-m-d H:i:s',
				$file['expires_at'],
				wp_timezone()
			),
			$response->output_files_expires_at
		);
		$this->assertStringNotContainsString(
			$file['url'],
			$response->output
		);
	}

	/**
	 * Tests the structured process response for an expired file.
	 */
	public function test_prepare_process_response_for_expired_file() {
		$user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		wp_set_current_user( $user_id );

		$file               = $this->create_exporter_file(
			'exporter-files-response-expired.csv'
		);
		$file['expires_at'] = time() - 1;
		$file['url']        = 'https://example.org/expired-export-url';

		$process_id = $this->create_exporter_process(
			array( $file ),
			$user_id,
			'<a href="' . $file['url'] . '">Download</a>'
		);

		$process = $this->get_process( $process_id );

		$response = $this->exporter_files->prepare_process_for_response(
			$process
		);

		$this->assertTrue( $response->output_files_download_allowed );
		$this->assertTrue( $response->output_files_expired );
		$this->assertTrue( $response->exporter_files[0]['expired'] );
		$this->assertNull( $response->exporter_files[0]['url'] );
		$this->assertStringContainsString(
			'href="#"',
			$response->output
		);
	}

	/**
	 * Tests the structured response for a user without download permission.
	 */
	public function test_prepare_process_response_for_unauthorized_user() {
		$owner_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);
		$user_id  = self::factory()->user->create(
			array( 'role' => 'subscriber' )
		);

		$file               = $this->create_exporter_file(
			'exporter-files-response-forbidden.csv'
		);
		$file['expires_at'] = time() + DAY_IN_SECONDS;
		$file['url']        = 'https://example.org/forbidden-export-url';

		$process_id = $this->create_exporter_process(
			array( $file ),
			$owner_id,
			'<a href="' . $file['url'] . '">Download</a>'
		);

		wp_set_current_user( $user_id );

		$process = $this->get_process( $process_id );

		$response = $this->exporter_files->prepare_process_for_response(
			$process
		);

		$this->assertFalse( $response->output_files_download_allowed );
		$this->assertFalse( $response->output_files_expired );
		$this->assertFalse( $response->exporter_files[0]['expired'] );
		$this->assertNull( $response->exporter_files[0]['url'] );
		$this->assertStringContainsString(
			'href="#"',
			$response->output
		);
	}
}
