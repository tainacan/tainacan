<?php

namespace Tainacan\Tests;

/**
 * @group api
 *
 */
class TAINACAN_REST_Roles_Controller extends TAINACAN_UnitApiTestCase {
	public function setUp() {
		parent::setUp();
		// reset WP_Roles object. Possible bug was cleaning database between tests, but not the object
		global $wpdb;
		wp_roles()->roles = get_option($wpdb->prefix . 'user_roles');
		wp_roles()->init_roles();
	}

	public function test_create_get_roles() {

		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');

		$request->set_query_params(['name' => 'New role']);

		$create = $this->server->dispatch($request);

		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('GET', $this->namespace . '/roles');

		//$request->set_query_params($name_query);

		$name_response = $this->server->dispatch($request);
		$data = $name_response->get_data();
		$this->assertArrayHasKey('tainacan-new-role', $data);
		$this->assertEquals('New role', $data['tainacan-new-role']['name']);
	}

	public function test_create_remove_roles() {
		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');

		$request->set_query_params(['name' => 'Super role']);

		$create = $this->server->dispatch($request);

		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('DELETE', $this->namespace . '/roles/tainacan-super-role');

		$delete_response = $this->server->dispatch($request);

		$this->assertEquals( 200, $delete_response->get_status() );

		$request = new \WP_REST_Request('GET', $this->namespace . '/roles');

		$name_response = $this->server->dispatch($request);
		$data = $name_response->get_data();
		$this->assertArrayNotHasKey('tainacan-super-role', $data);
	}

	public function test_edit_role() {
		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');

		$request->set_query_params(['name' => 'New role']);

		$create = $this->server->dispatch($request);
		//var_dump($create);
		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'add_cap' => 'tnc_rep_edit_collections'
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$role = \wp_roles()->roles['tainacan-new-role'];
		$this->assertArrayHasKey('tnc_rep_edit_collections', $role['capabilities']);
		$this->assertTrue($role['capabilities']['tnc_rep_edit_collections']);
		$this->assertEquals('Changed name', $role['name']);

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');
		$request->set_query_params(
			[
				'add_cap' => 'manage_options'
			]
		);
		$response = $this->server->dispatch($request);

		$this->assertEquals( 400, $response->get_status() );

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');
		$request->set_query_params(
			[
				'add_cap' => 'manage_tainacan_collection_234'
			]
		);
		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_edit_role_validation() {

		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');

		$request->set_query_params(['name' => 'New role']);

		$create = $this->server->dispatch($request);
		//var_dump($create);
		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'capabilities' => [
					'manage_options' => true
				]
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 400, $response->get_status() );


		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'add_cap' => 'manage_options'
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 400, $response->get_status() );


		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'capabilities' => [
					'tnc_col_23_edit_items' => true
				]
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_get_role() {
		$request = new \WP_REST_Request('GET', $this->namespace . '/roles/administrator');
		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( translate_user_role('Administrator'), $data['name'] );
		$this->assertArrayHasKey('manage_tainacan', $data['capabilities']);
		$this->assertTrue($data['capabilities']['manage_tainacan']);
	}

	public function test_get_roles() {
		$request = new \WP_REST_Request('GET', $this->namespace . '/roles');
		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		foreach (\tainacan_roles()->get_tainacan_roles() as $role => $r) {
			$this->assertArrayHasKey($role, $data);
		}

	}

	public function test_add_dependencies() {
		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');

		$request->set_query_params(['name' => 'New role']);

		$create = $this->server->dispatch($request);
		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'add_cap' => 'tnc_col_12_edit_items'
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$role = \wp_roles()->roles['tainacan-new-role'];

		$this->assertArrayHasKey('tnc_col_12_edit_items', $role['capabilities']);
		$this->assertTrue($role['capabilities']['tnc_col_12_edit_items']);
		$this->assertArrayHasKey('upload_files', $role['capabilities']);
		$this->assertTrue($role['capabilities']['upload_files']);
	}

	public function test_add_dependencies_capabilities() {
		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');

		$request->set_query_params(['name' => 'New role']);

		$create = $this->server->dispatch($request);
		//var_dump($create);
		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'capabilities' => [
					'tnc_col_12_edit_items' => true
				]
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$role = \wp_roles()->roles['tainacan-new-role'];

		$this->assertArrayHasKey('tnc_col_12_edit_items', $role['capabilities']);
		$this->assertTrue($role['capabilities']['tnc_col_12_edit_items']);
		$this->assertArrayHasKey('upload_files', $role['capabilities']);
		$this->assertTrue($role['capabilities']['upload_files']);
	}

	public function test_get_collection_caps() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste',
				'status' => 'publish'
			),
			true
		);

		$editor = get_role('editor');
		$contributor = get_role('contributor');
		$author = get_role('author');

		$editor->add_cap('manage_tainacan_collection_' . $collection->get_id());

		$author->add_cap( 'tnc_col_' . $collection->get_id() . '_edit_items' );
		$author->add_cap( 'tnc_col_' . $collection->get_id() . '_edit_metadata' );
		$author->add_cap( 'tnc_col_' . $collection->get_id() . '_edit_filters' );

		$contributor->add_cap( 'tnc_col_all_edit_items' );
		$contributor->add_cap( 'tnc_col_all_edit_published_items' );

		$request = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/capabilities');
		$response = $this->server->dispatch($request);
		$this->assertEquals( 200, $response->get_status() );

		$caps = $response->get_data()['capabilities'];

		$this->assertArrayHasKey('editor', $caps['manage_tainacan_collection_' . $collection->get_id()]['roles']);

		$this->assertArrayHasKey('author', $caps['tnc_col_' . $collection->get_id() . '_edit_items']['roles']);
		$this->assertArrayHasKey('author', $caps['tnc_col_' . $collection->get_id() . '_edit_metadata']['roles']);
		$this->assertArrayHasKey('author', $caps['tnc_col_' . $collection->get_id() . '_edit_filters']['roles']);

		$this->assertArrayHasKey('contributor', $caps['tnc_col_' . $collection->get_id() . '_edit_items']['roles_inherited']);
		$this->assertArrayHasKey('contributor', $caps['tnc_col_' . $collection->get_id() . '_edit_published_items']['roles_inherited']);

		$this->assertArrayHasKey('administrator', $caps['tnc_col_' . $collection->get_id() . '_delete_published_items']['roles_inherited']);
	}

	function test_get_repo_capabilities() {
		$role = add_role('test', 'test', ['tnc_rep_edit_metadata'=>true]);

		$request = new \WP_REST_Request('GET', $this->namespace . '/capabilities');

		$response = $this->server->dispatch($request);
		$this->assertEquals( 200, $response->get_status() );

		$caps = $response->get_data()['capabilities'];

		$this->assertArrayHasKey('editor', $caps['manage_tainacan']['roles']);
		$this->assertArrayHasKey('administrator', $caps['manage_tainacan']['roles']);
		$this->assertArrayHasKey('test', $caps['tnc_rep_edit_metadata']['roles']);
		$this->assertArrayHasKey('editor', $caps['tnc_rep_edit_metadata']['roles_inherited']);

		$this->assertArrayNotHasKey('editor', $caps['manage_tainacan']['roles_inherited']);
		$this->assertArrayNotHasKey('administrator', $caps['manage_tainacan']['roles_inherited']);
	}

	function test_edit_collection_users_permission() {
		global $current_user;
		$subscriber = $this->factory()->user->create(array( 'role' => 'subscriber' ));

		wp_set_current_user($subscriber);
		$sub_user = get_userdata( $subscriber );

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/contributor');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'add_cap' => 'tnc_col_12_edit_items'
			]
		);
		$response = $this->server->dispatch($request);

		$this->assertEquals( 403, $response->get_status(), 'should not be permitted');

		$sub_user->add_cap('tnc_col_12_edit_users');
		$current_user = $sub_user;

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/contributor');
		$request->set_query_params(
			[
				'name' => 'Changed name',
				'add_cap' => 'tnc_col_12_edit_items'
			]
		);
		$response = $this->server->dispatch($request);

		$this->assertEquals( 403, $response->get_status(), 'should still not be permitted because edits name');

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/contributor');
		$request->set_query_params(
			[
				'add_cap' => 'tnc_rep_edit_metadata'
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 403, $response->get_status(), 'should not be permitted');

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/contributor');
		$request->set_query_params(
			[
				'add_cap' => 'tnc_col_12_edit_items'
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status(), 'should be permitted');
	}

	public function test_create_get_roles_with_caps() {

		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');

		$request->set_query_params([
			'name' => 'New role',
			'capabilities' => [
				'tnc_rep_edit_collections' => true
			]
		]);

		$create = $this->server->dispatch($request);

		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('GET', $this->namespace . '/roles');
		//$request->set_query_params($name_query);

		$name_response = $this->server->dispatch($request);
		$data = $name_response->get_data();
		$this->assertArrayHasKey('tainacan-new-role', $data);
		$this->assertEquals('New role', $data['tainacan-new-role']['name']);

		$role = $data['tainacan-new-role'];

		$this->assertArrayHasKey('tnc_rep_edit_collections', $role['capabilities']);
		$this->assertTrue($role['capabilities']['tnc_rep_edit_collections']);
	}

	public function test_edit_role_with_caps() {
		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');
		$request->set_query_params([
			'name' => 'New role',
			'capabilities' => [
				'tnc_rep_edit_collections' => true,
				'tnc_rep_delete_collections' => true,
				'tnc_rep_edit_taxonomies' => true,
				'manage_tainacan_collection_123' => true
			]
		]);

		$create = $this->server->dispatch($request);
		$this->assertEquals( 201, $create->get_status() );

		$request = new \WP_REST_Request('GET', $this->namespace . '/roles/tainacan-new-role');

		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();

		$this->assertEquals( translate_user_role('New role'), $data['name'] );
		$this->assertArrayHasKey('tnc_rep_edit_collections', $data['capabilities']);
		$this->assertTrue($data['capabilities']['tnc_rep_edit_collections']);
		$this->assertArrayHasKey('tnc_rep_delete_collections', $data['capabilities']);
		$this->assertTrue($data['capabilities']['tnc_rep_delete_collections']);
		$this->assertArrayHasKey('tnc_rep_edit_taxonomies', $data['capabilities']);
		$this->assertTrue($data['capabilities']['tnc_rep_edit_taxonomies']);
		$this->assertArrayHasKey('manage_tainacan_collection_123', $data['capabilities']);
		$this->assertTrue($data['capabilities']['manage_tainacan_collection_123']);

	 	// EDIT
		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/tainacan-new-role');

		$request->set_query_params(
			[
				'name' => 'Changed name',
				'capabilities' => [
					'tnc_rep_edit_collections' => true,
					'tnc_rep_delete_collections' => true,
					'tnc_col_12_edit_items' => true,
					'tnc_rep_edit_metadata' => true // replaced tnc_rep_edit_taxonomies by tnc_rep_edit_metadata
					// removed manage_tainacan_collection_123
				]
			]
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$request = new \WP_REST_Request('GET', $this->namespace . '/roles/tainacan-new-role');
		$response = $this->server->dispatch($request);

		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();

		$this->assertEquals( translate_user_role('Changed name'), $data['name'] );
		$this->assertArrayHasKey('tnc_rep_edit_collections', $data['capabilities']);
		$this->assertTrue($data['capabilities']['tnc_rep_edit_collections']);
		$this->assertArrayHasKey('tnc_rep_delete_collections', $data['capabilities']);
		$this->assertTrue($data['capabilities']['tnc_rep_delete_collections']);
		$this->assertArrayHasKey('tnc_rep_edit_metadata', $data['capabilities']);
		$this->assertTrue($data['capabilities']['tnc_rep_edit_metadata']);
		$this->assertArrayHasKey('tnc_col_12_edit_items', $data['capabilities']);
		$this->assertTrue($data['capabilities']['tnc_col_12_edit_items']);

		$this->assertArrayNotHasKey('tnc_rep_edit_taxonomies', $data['capabilities']);
		$this->assertArrayNotHasKey('manage_tainacan_collection_123', $data['capabilities']);
	}

	function test_new_role_can_read() {

		$request = new \WP_REST_Request('POST', $this->namespace . '/roles');
		$request->set_query_params(['name' => 'New role']);
		$create = $this->server->dispatch($request);

		$this->assertEquals( 201, $create->get_status() );
		$role = get_role('tainacan-new-role');

		$this->assertTrue( $role->has_cap( 'read' ) );
	}

}