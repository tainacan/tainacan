<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * @group permissions
 */
class Permissions extends TAINACAN_UnitTestCase {
	
	/**
	 * 
	 */
	function test_roles () {
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);
		$user_id = get_current_user_id();
		$this->assertEquals($new_user, $user_id);
		$this->assertTrue(user_can($user_id, 'read_'.\Tainacan\Entities\Collection::get_post_type()), 'User cannot read Collections');
		$this->assertTrue(user_can($user_id, 'subscriber'));
		$this->assertFalse(user_can($user_id, 'edit_'.\Tainacan\Entities\Collection::get_post_type()), 'A subscriber user can edit a Collections?');
		
		$new_admin_user = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_admin_user);
		$user_id = get_current_user_id();
		$this->assertTrue(user_can($user_id, 'administrator'));
		$this->assertTrue(user_can($user_id, 'edit_'.\Tainacan\Entities\Collection::get_post_type()), 'A administrator user cannot edit a Collections?');
		//TODO test all roles and check the capabilities
	}
	
}