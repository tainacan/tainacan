<?php

namespace Tainacan\Tests;

/**
 * @group api 
 *
 */
class TAINACAN_REST_Roles_Controller extends TAINACAN_UnitApiTestCase {
	
	/**
	 * just while we dont refactor capabilities
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$role = get_role('administrator');
		$role->add_cap('edit_tainacan_users');
		global $current_user;
		$current_user->get_role_caps();
	}
	
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
		
		$request = new \WP_REST_Request('PATCH', $this->namespace . '/roles/new-role');
		
		$request->set_query_params(
			[
				'name' => 'Changed name',
				'add_cap' => 'fly'
			]
		);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals( 200, $response->get_status() );
		
		$role = \wp_roles()->roles['tainacan-new-role'];
		$this->assertContains('fly', $role['capabilities']);
		$this->assertEquals('Changed name', $role['name']);
		
	}
	
	public function test_get_role() {
		$request = new \WP_REST_Request('GET', $this->namespace . '/roles/administrator');
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals( 200, $response->get_status() );
		
		$data = $response->get_data();
		
		$this->assertEquals( translate_user_role('Administrator'), $data['name'] );
		$this->assertContains('manage_options', $data['capabilities']);
		
	}

}

?>
