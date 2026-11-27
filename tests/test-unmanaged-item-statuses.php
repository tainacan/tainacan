<?php

namespace Tainacan\Tests;

/**
 * Item totals must ignore statuses Tainacan does not list, and those items
 * can be moved back to draft. See https://github.com/tainacan/tainacan/issues/1394
 *
 * @group api
 */
class Test_Unmanaged_Item_Statuses extends TAINACAN_UnitApiTestCase {

	private $other_collection;
	private $publish_ids = array();
	private $empty_ids = array();
	private $future_id;
	private $trash_id;
	private $auto_draft_id;
	private $other_empty_id;

	public function setUp(): void {
		parent::setUp();

		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Unmanaged statuses',
				'status' => 'publish',
			),
			true
		);

		$this->other_collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Other collection',
				'status' => 'publish',
			),
			true
		);

		$this->publish_ids = array(
			$this->create_item( $this->collection, 'Published one' ),
			$this->create_item( $this->collection, 'Published two' ),
		);
		$this->empty_ids = array(
			$this->create_item( $this->collection, 'Empty one', 'empty' ),
			$this->create_item( $this->collection, 'Empty two', 'empty' ),
		);
		$this->future_id = $this->create_item( $this->collection, 'Scheduled', 'future' );
		$this->trash_id = $this->create_item( $this->collection, 'Trashed', 'trash' );
		$this->auto_draft_id = $this->create_item( $this->collection, 'Autosave', 'auto-draft' );
		$this->other_empty_id = $this->create_item( $this->other_collection, 'Other empty', 'empty' );

		wp_cache_flush();
	}

	public function test_managed_statuses_are_the_listed_ones() {
		$items = \Tainacan\Repositories\Items::get_instance();

		$this->assertSame(
			array( 'publish', 'private', 'pending', 'draft' ),
			$items->get_managed_item_statuses()
		);
	}

	public function test_collection_total_keeps_raw_counts_and_unmanaged_ids_skip_known_statuses() {
		$items = \Tainacan\Repositories\Items::get_instance();
		$ids = $items->fetch_unmanaged_status_item_ids( $this->collection );

		sort( $ids );
		$expected = array( $this->empty_ids[0], $this->empty_ids[1] );
		sort( $expected );
		$this->assertSame( $expected, $ids );
		$this->assertNotContains( $this->future_id, $ids );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/collections/' . $this->collection->get_id() );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 2, (int) $data['total_items']['publish'] );
		$this->assertEquals( 2, (int) $data['total_items']['empty'] );
		$this->assertEquals( 1, (int) $data['total_items']['future'] );
		$this->assertEquals( 1, (int) $data['total_items']['trash'] );

		$listed = 0;
		foreach ( $items->get_managed_item_statuses() as $status ) {
			$listed += isset( $data['total_items'][ $status ] ) ? (int) $data['total_items'][ $status ] : 0;
		}
		$this->assertEquals( 2, $listed );
	}

	public function test_recover_moves_unrecognized_items_to_draft() {
		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $this->collection->get_id() . '/bulk-edit/recover-unmanaged-status'
		);
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status(), wp_json_encode( $data ) );
		$this->assertEquals( 2, $data['items'] );
		$this->assertNotEmpty( $data['id'] );

		$handler = \Tainacan\Generic_Process_Handler::get_instance();
		$process = $handler->get_process_instance_by_session_id( $data['id'] );
		$this->assertInstanceOf( \Tainacan\GenericProcess\Bulk_Edit_Process::class, $process );

		$process->init_objects();
		$steps = $process->get_steps();
		$this->assertSame( 2, $steps[1]['total'] );
		$steps = 0;
		while ( false !== $process->run() ) {
			$steps++;
			if ( $steps > 30 ) {
				$this->fail( 'Bulk edit did not finish.' );
			}
		}

		foreach ( $this->empty_ids as $id ) {
			$this->assertSame( 'draft', get_post_status( $id ) );
		}
		$this->assertSame( 'future', get_post_status( $this->future_id ) );

		foreach ( $this->publish_ids as $id ) {
			$this->assertSame( 'publish', get_post_status( $id ) );
		}
		$this->assertSame( 'trash', get_post_status( $this->trash_id ) );
		$this->assertSame( 'auto-draft', get_post_status( $this->auto_draft_id ) );
		$this->assertSame( 'empty', get_post_status( $this->other_empty_id ) );
	}

	public function test_recover_without_unmanaged_items_does_not_queue_a_process() {
		$empty_collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'No stray items',
				'status' => 'publish',
			),
			true
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $empty_collection->get_id() . '/bulk-edit/recover-unmanaged-status'
		);
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status(), wp_json_encode( $data ) );
		$this->assertEquals( 0, $data['items'] );
		$this->assertNull( $data['id'] );
	}

	public function test_subscriber_cannot_recover_unmanaged_items() {
		$subscriber = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $this->collection->get_id() . '/bulk-edit/recover-unmanaged-status'
		);
		$response = $this->server->dispatch( $request );

		$this->assertContains( $response->get_status(), array( 401, 403 ) );
	}

	private function create_item( $collection, $title, $status = 'publish' ) {
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => $title,
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		if ( 'publish' !== $status ) {
			$this->force_item_status( $item->get_id(), $status );
		}

		return (int) $item->get_id();
	}

	private function force_item_status( $item_id, $status ) {
		global $wpdb;

		$wpdb->update(
			$wpdb->posts,
			array( 'post_status' => $status ),
			array( 'ID' => $item_id ),
			array( '%s' ),
			array( '%d' )
		);
		clean_post_cache( $item_id );
	}
}
