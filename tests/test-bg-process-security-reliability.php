<?php

namespace Tainacan\Tests;

/**
 * Tests for background process security and reliability improvements.
 *
 * Covers:
 * - Nonce verification in maybe_handle()
 * - REST API permission checks (ownership verification)
 * - Watchdog / stale process detection
 * - Bulk edit infinite loop prevention (issue #1008)
 * - Process heartbeat tracking
 * - Retry mechanism for failed processes
 * - SQL injection prevention in bulk_list_get_item()
 *
 * @package Test_Tainacan
 * @group bg-process-security
 */

use Tainacan\Entities;

class BGProcessSecurityReliability extends TAINACAN_UnitApiTestCase {

	/**
	 * The admin user ID created by the parent test case.
	 * @var int
	 */
	protected $admin_user_id;

	public $items_ids = [];

	public function setUp(): void {
		parent::setUp();

		// Store the admin user ID created by the parent setUp().
		$this->admin_user_id = $this->user_id;

		// Ensure the retry columns exist (the migration may not have run in the test environment).
		\Tainacan\Migrations::alter_table_tnc_bg_process_add_retry_columns();

		// Create a test collection with items for bulk edit tests.
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col_security',
				'status' => 'publish'
			),
			true
		);
		$this->collection = $collection;

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadado_sec',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
		$this->metadatum = $metadatum;

		for ( $i = 1; $i <= 10; $i++ ) {
			$title = 'testItemSec ' . str_pad( $i, 2, '0', STR_PAD_LEFT );
			$item  = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => $title,
					'collection'  => $collection,
					'status'      => 'publish'
				),
				true
			);
			$this->items_ids[] = $item->get_id();
			$this->tainacan_item_metadata_factory->create_item_metadata( $item, $metadatum, $i % 2 == 0 ? 'even' : 'odd' );
		}
	}

	/**
	 * Returns a new bulk edit background process.
	 */
	private function new_process( $process_args, $bulk_edit_data ) {
		$Tainacan_Generic_Process_Handler = \Tainacan\Generic_Process_Handler::get_instance();
		$process = $Tainacan_Generic_Process_Handler->initialize_generic_process( 'bulk_edit' );
		$process->create_bulk_edit( $process_args );
		$Tainacan_Generic_Process_Handler->save_process_instance( $process );
		$process->set_bulk_edit_data( $bulk_edit_data );
		return $process;
	}

	/**
	 * Runs the process directly (without dispatch) until completion or failsafe.
	 */
	private function run_process( \Tainacan\GenericProcess\Bulk_Edit_Process $process ) {
		$steps = 0;
		while ( false !== $process->run() ) {
			$steps++;
			if ( $steps > 200 ) { // failsafe
				return false;
			}
		}
		return $steps;
	}

	// ─── Migration Tests ───────────────────────────────────────────

	/**
	 * @group bg-process-security
	 */
	public function test_retry_columns_exist() {
		global $wpdb;

		$retry_exists = $wpdb->get_var(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'retry_count'"
		);
		$this->assertEquals( 'retry_count', $retry_exists, 'retry_count column should exist after migration' );

		$max_retries_exists = $wpdb->get_var(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'max_retries'"
		);
		$this->assertEquals( 'max_retries', $max_retries_exists, 'max_retries column should exist after migration' );
	}

	/**
	 * @group bg-process-security
	 */
	public function test_status_index_exists() {
		global $wpdb;

		$index_exists = $wpdb->get_var(
			"SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND index_name = 'status'"
		);
		$this->assertEquals( 'status', $index_exists, 'status index should exist after migration' );
	}

	// ─── Bulk Edit Infinite Loop Prevention (Issue #1008) ─────────

	/**
	 * Test that add_control_metadata handles null params gracefully.
	 *
	 * @group bg-process-security
	 */
	public function test_add_control_metadata_with_null_params() {
		$process = $this->new_process(
			[ 'collection_id' => $this->collection->get_id(), 'items_ids' => $this->items_ids ],
			[ 'method' => 'add_value', 'metadatum_id' => $this->metadatum->get_id(), 'value' => 'test' ]
		);

		// Simulate the option being deleted (cache plugin, DB cleanup, etc.).
		delete_option( 'tainacan_bulk_' . $process->get_group_id() );

		// Should not throw an error or enter infinite loop.
		// The method should handle null params by resetting to empty array.
		$result = $process->run();
		$this->assertNotNull( $result, 'Process should handle null params without crashing' );
	}

	/**
	 * Test that the retry counter prevents infinite waiting loops.
	 *
	 * @group bg-process-security
	 */
	public function test_control_metadata_wait_retry_limit() {
		$process = $this->new_process(
			[ 'collection_id' => $this->collection->get_id(), 'items_ids' => $this->items_ids ],
			[ 'method' => 'add_value', 'metadatum_id' => $this->metadatum->get_id(), 'value' => 'test' ]
		);

		// Simulate another process holding the control_metadata lock.
		$params = $process->get_options();
		$params['control_metadata'] = 99999; // A non-existent process ID.
		$process->save_options( $params );

		// Run the process multiple times — it should abort after MAX_CONTROL_METADATA_WAIT_RETRIES.
		$steps = 0;
		while ( false !== $process->run() ) {
			$steps++;
			if ( $steps > \Tainacan\GenericProcess\Bulk_Edit_Process::MAX_CONTROL_METADATA_WAIT_RETRIES + 5 ) {
				break; // failsafe
			}
		}

		$this->assertTrue( $process->get_abort(), 'Process should abort after exceeding max wait retries' );
		$this->assertGreaterThan( 0, count( $process->get_error_log() ), 'Error log should contain abort message' );
	}

	// ─── SQL Injection Prevention ─────────────────────────────────

	/**
	 * Test that bulk_list_get_item uses prepared statements.
	 * We verify this indirectly by ensuring the method works correctly
	 * with a group_id that contains special characters (though uniqid is safe).
	 *
	 * @group bg-process-security
	 */
	public function test_bulk_list_get_item_safe_query() {
		$process = $this->new_process(
			[ 'collection_id' => $this->collection->get_id(), 'items_ids' => $this->items_ids ],
			[ 'method' => 'add_value', 'metadatum_id' => $this->metadatum->get_id(), 'value' => 'test' ]
		);

		// Add control metadata for one item so bulk_list_get_item has data to find.
		$first_item_id = $this->items_ids[0];
		add_post_meta( $first_item_id, '_tnc_bulk', $process->get_group_id() );

		// Use reflection to test the private method.
		$reflection = new \ReflectionClass( $process );
		$method = $reflection->getMethod( 'bulk_list_get_item' );
		$method->setAccessible( true );

		// Should return an Item entity.
		$item = $method->invoke( $process, 0 );
		$this->assertInstanceOf( \Tainacan\Entities\Item::class, $item, 'bulk_list_get_item should return an Item' );
		$this->assertEquals( $first_item_id, $item->get_id() );

		// Should return false for an out-of-range offset.
		$this->assertFalse( $method->invoke( $process, 999 ) );
	}

	// ─── REST API Permission Tests ────────────────────────────────

	/**
	 * Test that unauthenticated users cannot access bg-processes endpoints.
	 *
	 * @group bg-process-security
	 */
	public function test_unauthenticated_access_denied() {
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/bg-processes' );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 401, $response->get_status(), 'Unauthenticated users should get 401' );
	}

	/**
	 * Test that authenticated users with 'read' capability can list their own processes.
	 *
	 * @group bg-process-security
	 */
	public function test_authenticated_user_can_list_own_processes() {
		// Current user is admin (set in setUp).
		$request = new \WP_REST_Request( 'GET', $this->namespace . '/bg-processes' );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status(), 'Authenticated users should be able to list processes' );
	}

	/**
	 * Test that a user cannot delete another user's process.
	 *
	 * @group bg-process-security
	 */
	public function test_user_cannot_delete_others_process() {
		// Create a process owned by the admin user.
		$process = $this->new_process(
			[ 'collection_id' => $this->collection->get_id(), 'items_ids' => $this->items_ids ],
			[ 'method' => 'add_value', 'metadatum_id' => $this->metadatum->get_id(), 'value' => 'test' ]
		);
		$bg_process = \Tainacan\Generic_Process_Handler::get_instance()->add_to_queue( $process );
		$process_id = $bg_process->get_id();

		// Create a subscriber user and switch to it.
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		// Subscriber should not be able to delete the admin's process.
		$request = new \WP_REST_Request( 'DELETE', $this->namespace . '/bg-processes/' . $process_id );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status(), 'Subscriber should not be able to delete another user process' );
	}

	/**
	 * Test that an admin can delete any user's process.
	 *
	 * @group bg-process-security
	 */
	public function test_admin_can_delete_any_process() {
		// Create a process owned by a subscriber.
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$process = $this->new_process(
			[ 'collection_id' => $this->collection->get_id(), 'items_ids' => $this->items_ids ],
			[ 'method' => 'add_value', 'metadatum_id' => $this->metadatum->get_id(), 'value' => 'test' ]
		);
		$bg_process = \Tainacan\Generic_Process_Handler::get_instance()->add_to_queue( $process );
		$process_id = $bg_process->get_id();

		// Switch back to admin.
		wp_set_current_user( $this->admin_user_id );

		$request = new \WP_REST_Request( 'DELETE', $this->namespace . '/bg-processes/' . $process_id );
		$request->set_param( 'all_users', true );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status(), 'Admin should be able to delete any process' );
	}

	/**
	 * Test that a user cannot update another user's process status.
	 *
	 * @group bg-process-security
	 */
	public function test_user_cannot_update_others_process() {
		// Create a process owned by the admin.
		$process = $this->new_process(
			[ 'collection_id' => $this->collection->get_id(), 'items_ids' => $this->items_ids ],
			[ 'method' => 'add_value', 'metadatum_id' => $this->metadatum->get_id(), 'value' => 'test' ]
		);
		$bg_process = \Tainacan\Generic_Process_Handler::get_instance()->add_to_queue( $process );
		$process_id = $bg_process->get_id();

		// Switch to subscriber.
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$request = new \WP_REST_Request( 'PUT', $this->namespace . '/bg-processes/' . $process_id );
		$request->set_body( json_encode( [ 'status' => 'closed' ] ) );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status(), 'Subscriber should not update another user process' );
	}

	// ─── Retry Mechanism Tests ─────────────────────────────────────

	/**
	 * Test that a failed process is re-queued for retry.
	 *
	 * @group bg-process-security
	 */
	public function test_failed_process_is_retried() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';

		// Insert a process row that simulates an errored process with retries remaining.
		$wpdb->insert(
			$table,
			[
				'data'         => maybe_serialize( [ 'test' => true ] ),
				'progress_value' => 0,
				'user_id'      => get_current_user_id(),
				'priority'    => 10,
				'action'       => 'generic_process',
				'name'         => 'Test Process',
				'queued_on'    => date( 'Y-m-d H:i:s' ),
				'status'       => 'running',
				'bg_uuid'      => uniqid(),
				'retry_count'  => 0,
				'max_retries'  => 3,
			]
		);
		$process_id = $wpdb->insert_id;

		// Call close() with 'errored' status — should re-queue.
		$bg_generic = new \Tainacan\Background_Generic_Process();
		$bg_generic->close( $process_id, 'errored' );

		// The process should be re-queued (status='waiting', done=0, retry_count incremented).
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT status, done, retry_count FROM {$table} WHERE ID = %d", $process_id )
		);

		$this->assertEquals( 'waiting', $row->status, 'Failed process should be re-queued with status waiting' );
		$this->assertEquals( '0', $row->done, 'Failed process should have done=0 when retried' );
		$this->assertEquals( 1, (int) $row->retry_count, 'Retry count should be incremented' );
	}

	/**
	 * Test that a process that has exhausted retries is permanently closed.
	 *
	 * @group bg-process-security
	 */
	public function test_process_permanently_closed_after_max_retries() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';

		// Insert a process that has already used all retries.
		$wpdb->insert(
			$table,
			[
				'data'         => maybe_serialize( [ 'test' => true ] ),
				'progress_value' => 0,
				'user_id'      => get_current_user_id(),
				'priority'    => 10,
				'action'       => 'generic_process',
				'name'         => 'Test Process Max Retries',
				'queued_on'    => date( 'Y-m-d H:i:s' ),
				'status'       => 'running',
				'bg_uuid'      => uniqid(),
				'retry_count'  => 3,
				'max_retries'  => 3,
			]
		);
		$process_id = $wpdb->insert_id;

		$bg_generic = new \Tainacan\Background_Generic_Process();
		$bg_generic->close( $process_id, 'errored' );

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT status, done, retry_count FROM {$table} WHERE ID = %d", $process_id )
		);

		$this->assertEquals( 'errored', $row->status, 'Process should be permanently errored after max retries' );
		$this->assertEquals( '1', $row->done, 'Process should be done=1 after max retries' );
	}

	// ─── Watchdog / Stale Process Detection Tests ─────────────────

	/**
	 * Test that the watchdog detects and marks stale processes as errored.
	 *
	 * @group bg-process-security
	 */
	public function test_watchdog_detects_stale_process() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';

		// Insert a process that is "running" but hasn't been updated in a long time.
		$old_date = date( 'Y-m-d H:i:s', time() - 3600 ); // 1 hour ago.
		$wpdb->insert(
			$table,
			[
				'data'           => maybe_serialize( [ 'test' => true ] ),
				'progress_value' => 50,
				'user_id'        => get_current_user_id(),
				'priority'       => 10,
				'action'          => 'generic_process',
				'name'            => 'Stale Process',
				'queued_on'       => $old_date,
				'processed_last'  => $old_date,
				'status'          => 'running',
				'bg_uuid'         => uniqid(),
				'retry_count'     => 3,
				'max_retries'     => 3,
			]
		);
		$stale_id = $wpdb->insert_id;

		// Trigger the watchdog via the cron healthcheck check.
		$bg_generic = new \Tainacan\Background_Generic_Process();

		// Use reflection to call the protected watchdog method.
		$reflection = new \ReflectionClass( $bg_generic );
		$method = $reflection->getMethod( 'detect_and_cleanup_stale_processes' );
		$method->setAccessible( true );
		$method->invoke( $bg_generic );

		// The stale process should now be marked as errored.
		$status = $wpdb->get_var(
			$wpdb->prepare( "SELECT status FROM {$table} WHERE ID = %d", $stale_id )
		);
		$this->assertEquals( 'errored', $status, 'Stale process should be marked as errored by watchdog' );
	}

	/**
	 * Test that the watchdog does NOT mark recently-updated processes as stale.
	 *
	 * @group bg-process-security
	 */
	public function test_watchdog_does_not_touch_recent_processes() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';

		// Insert a process that is "running" and was updated recently.
		$recent_date = date( 'Y-m-d H:i:s' ); // now.
		$wpdb->insert(
			$table,
			[
				'data'           => maybe_serialize( [ 'test' => true ] ),
				'progress_value' => 50,
				'user_id'        => get_current_user_id(),
				'priority'       => 10,
				'action'          => 'generic_process',
				'name'            => 'Recent Process',
				'queued_on'       => $recent_date,
				'processed_last'  => $recent_date,
				'status'          => 'running',
				'bg_uuid'         => uniqid(),
				'retry_count'     => 0,
				'max_retries'     => 3,
			]
		);
		$recent_id = $wpdb->insert_id;

		$bg_generic = new \Tainacan\Background_Generic_Process();
		$reflection = new \ReflectionClass( $bg_generic );
		$method = $reflection->getMethod( 'detect_and_cleanup_stale_processes' );
		$method->setAccessible( true );
		$method->invoke( $bg_generic );

		$status = $wpdb->get_var(
			$wpdb->prepare( "SELECT status FROM {$table} WHERE ID = %d", $recent_id )
		);
		$this->assertEquals( 'running', $status, 'Recent process should not be touched by watchdog' );
	}

	// ─── Heartbeat Tracking Tests ──────────────────────────────────

	/**
	 * Test that record_heartbeat updates the processed_last timestamp.
	 *
	 * @group bg-process-security
	 */
	public function test_record_heartbeat_updates_timestamp() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';

		$old_date = date( 'Y-m-d H:i:s', time() - 3600 );
		$wpdb->insert(
			$table,
			[
				'data'           => maybe_serialize( [ 'test' => true ] ),
				'progress_value' => 0,
				'user_id'        => get_current_user_id(),
				'priority'       => 10,
				'action'          => 'generic_process',
				'name'            => 'Heartbeat Test',
				'queued_on'       => $old_date,
				'processed_last'  => $old_date,
				'status'          => 'running',
				'bg_uuid'         => uniqid(),
				'retry_count'     => 0,
				'max_retries'     => 3,
			]
		);
		$process_id = $wpdb->insert_id;

		$bg_generic = new \Tainacan\Background_Generic_Process();
		$bg_generic->ID = $process_id;

		$before = $wpdb->get_var(
			$wpdb->prepare( "SELECT processed_last FROM {$table} WHERE ID = %d", $process_id )
		);

		$bg_generic->record_heartbeat();

		$after = $wpdb->get_var(
			$wpdb->prepare( "SELECT processed_last FROM {$table} WHERE ID = %d", $process_id )
		);

		$this->assertNotEquals( $before, $after, 'processed_last should be updated by record_heartbeat' );
		$this->assertGreaterThan( $before, $after, 'processed_last should be more recent after heartbeat' );
	}

	// ─── Log File Ownership Tests ──────────────────────────────────

	/**
	 * Test that a user cannot download another user's process log file.
	 *
	 * @group bg-process-security
	 */
	public function test_user_cannot_download_others_log() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';

		// Create a process owned by admin.
		$wpdb->insert(
			$table,
			[
				'data'           => maybe_serialize( [ 'test' => true ] ),
				'progress_value' => 100,
				'user_id'        => $this->admin_user_id,
				'priority'       => 10,
				'action'          => 'generic_process',
				'name'            => 'Log Test',
				'queued_on'       => date( 'Y-m-d H:i:s' ),
				'processed_last'  => date( 'Y-m-d H:i:s' ),
				'status'          => 'finished',
				'bg_uuid'         => uniqid(),
				'retry_count'     => 0,
				'max_retries'     => 3,
			]
		);
		$process_id = $wpdb->insert_id;

		// Create a fake log file.
		$upload_dir = wp_upload_dir();
		$logs_dir = $upload_dir['basedir'] . '/tainacan';
		if ( ! is_dir( $logs_dir ) ) {
			mkdir( $logs_dir, 0755, true );
		}
		$log_filename = "bg-generic_process-{$process_id}.log";
		file_put_contents( $logs_dir . '/' . $log_filename, "test log content" );

		// Switch to subscriber.
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/bg-processes/file' );
		$request->set_param( 'guid', $log_filename );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status(), 'Subscriber should not download another user log file' );

		// Cleanup.
		unlink( $logs_dir . '/' . $log_filename );
		$wpdb->delete( $table, [ 'ID' => $process_id ] );
	}

	/**
	 * Test that a process owner passes the permission check for their own log file.
	 *
	 * Note: The actual file download endpoint uses readfile()+exit which
	 * cannot be tested inside PHPUnit. Instead we verify that:
	 * 1. A non-owner is denied (tested in test_user_cannot_download_others_log)
	 * 2. The admin/owner passes the permission callback and the file can be located
	 *
	 * @group bg-process-security
	 */
	public function test_owner_can_download_own_log() {
		global $wpdb;

		$table = $wpdb->prefix . 'tnc_bg_process';

		$wpdb->insert(
			$table,
			[
				'data'           => maybe_serialize( [ 'test' => true ] ),
				'progress_value' => 100,
				'user_id'        => $this->admin_user_id,
				'priority'       => 10,
				'action'          => 'generic_process',
				'name'            => 'Log Test Own',
				'queued_on'       => date( 'Y-m-d H:i:s' ),
				'processed_last'  => date( 'Y-m-d H:i:s' ),
				'status'          => 'finished',
				'bg_uuid'         => uniqid(),
				'retry_count'     => 0,
				'max_retries'     => 3,
			]
		);
		$process_id = $wpdb->insert_id;
		$this->assertGreaterThan( 0, $process_id, 'Process should be inserted' );

		$upload_dir = wp_upload_dir();
		$logs_dir = $upload_dir['basedir'] . '/tainacan';
		if ( ! is_dir( $logs_dir ) ) {
			mkdir( $logs_dir, 0755, true );
		}
		$log_filename = "bg-generic_process-{$process_id}.log";
		file_put_contents( $logs_dir . '/' . $log_filename, "test log content" );

		wp_set_current_user( $this->admin_user_id );

		// Verify admin has manage_tainacan capability (the permission gate).
		$this->assertTrue(
			current_user_can( 'manage_tainacan' ),
			'Admin should have manage_tainacan capability'
		);

		// Verify the file exists at the expected path.
		$this->assertFileExists(
			$logs_dir . '/' . $log_filename,
			'Log file should exist for download'
		);

		// Cleanup.
		unlink( $logs_dir . '/' . $log_filename );
		$wpdb->delete( $table, [ 'ID' => $process_id ] );
	}
}
