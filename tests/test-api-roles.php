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

}

?>
