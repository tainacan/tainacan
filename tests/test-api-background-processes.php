<?php

namespace Tainacan\Tests;

/**
 * Regression tests for the `bg-processes/file` endpoint.
 *
 * Covers the CWE-22 path traversal / arbitrary file read reported against
 * REST_Background_Processes_Controller::get_file(): a Subscriber+ user could
 * read arbitrary server files (e.g. wp-config.php) via the `guid` parameter
 * whenever wp-content/uploads/tainacan did not yet exist, because
 * realpath() on the missing base directory returned false, which PHP
 * coerced to '' on string concatenation.
 *
 * @group api
 */
class TAINACAN_REST_Background_Processes_File extends TAINACAN_UnitApiTestCase {

	private $tainacan_uploads_dir;

	public function setUp() : void {
		parent::setUp();

		$upload_dir = wp_upload_dir();
		$this->tainacan_uploads_dir = $upload_dir['basedir'] . '/tainacan';

		// The vulnerability only reproduces when this directory does not exist yet,
		// so make sure no previous test in the suite left it behind.
		$this->remove_tainacan_uploads_dir();
	}

	public function tearDown() : void {
		$this->remove_tainacan_uploads_dir();
		parent::tearDown();
	}

	private function remove_tainacan_uploads_dir() {
		$this->rmdir_recursive( $this->tainacan_uploads_dir );
	}

	/**
	 * Other test suites (exporters, importers, background processes) create
	 * wp-content/uploads/tainacan on demand and never clean it up, since it
	 * lives on the filesystem rather than in the DB transaction that WP's
	 * test case rolls back. When the full suite runs, that directory is
	 * almost always already populated (with subfolders) by the time this
	 * test class runs, so a plain glob()+rmdir() silently no-ops on it.
	 */
	private function rmdir_recursive( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		foreach ( scandir( $dir ) as $entry ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$path = $dir . '/' . $entry;

			if ( is_dir( $path ) && ! is_link( $path ) ) {
				$this->rmdir_recursive( $path );
			} else {
				@unlink( $path );
			}
		}

		@rmdir( $dir );
	}

	/**
	 * The /bg-processes/file route requires manage_tainacan, so these
	 * traversal regression tests need an authorized user to actually reach
	 * get_file()'s path-handling logic rather than being stopped earlier by
	 * the permission_callback.
	 */
	private function request_file_as_authorized_user( $guid ) {
		$admin = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/bg-processes/file' );
		$request->set_param( 'guid', $guid );

		return $this->server->dispatch( $request );
	}

	public function test_rejects_dot_dot_traversal() {
		$response = $this->request_file_as_authorized_user( '../../../../etc/passwd' );

		$this->assertEquals( 403, $response->get_status() );
		$this->assertEquals( 'unauthorized_file_path', $response->get_data()['code'] );
	}

	public function test_rejects_absolute_unix_path() {
		$response = $this->request_file_as_authorized_user( '/etc/passwd' );

		$this->assertEquals( 403, $response->get_status() );
		$this->assertEquals( 'unauthorized_file_path', $response->get_data()['code'] );
	}

	public function test_rejects_windows_style_absolute_path() {
		$response = $this->request_file_as_authorized_user( 'C:\\Windows\\win.ini' );

		$this->assertEquals( 403, $response->get_status() );
		$this->assertEquals( 'unauthorized_file_path', $response->get_data()['code'] );
	}

	/**
	 * Reproduces the reported PoC: with wp-content/uploads/tainacan missing,
	 * a guid like "etc/passwd" (no leading slash) used to be able to escape
	 * the intended base directory entirely once realpath() on it failed.
	 * The fix must short-circuit with a 404 instead of falling through to
	 * readfile().
	 */
	public function test_missing_base_directory_does_not_leak_arbitrary_files() {
		$this->assertDirectoryDoesNotExist( $this->tainacan_uploads_dir );

		$response = $this->request_file_as_authorized_user( 'etc/passwd' );

		$this->assertEquals( 404, $response->get_status() );
		$this->assertStringContainsString( 'Base directory not found', $response->get_data()['error_message'] );
	}

	public function test_missing_guid_returns_400() {
		$admin = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );

		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/bg-processes/file' );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_logged_out_user_is_unauthorized() {
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/bg-processes/file' );
		$request->set_param( 'guid', 'some-log.log' );

		$response = $this->server->dispatch( $request );

		// The route's permission_callback rejects anonymous requests before get_file() runs;
		// WP core's rest_authorization_required_code() maps that to 401, reserving 403 for
		// an authenticated user that lacks the capability.
		$this->assertEquals( 401, $response->get_status() );
	}

	public function test_user_without_manage_tainacan_is_forbidden() {
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/bg-processes/file' );
		$request->set_param( 'guid', 'some-log.log' );

		$response = $this->server->dispatch( $request );

		// A logged-in user without manage_tainacan is rejected by the
		// permission_callback before get_file() runs.
		$this->assertEquals( 403, $response->get_status() );
	}
}
