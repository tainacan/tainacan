<?php

namespace Tainacan\Tests;

use Tainacan\Entities\Collection;

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
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testePerms',
				'description'   => 'adasdasdsa',
			),
			true
		);
		
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);
		$user_id = get_current_user_id();
		$this->assertEquals($new_user, $user_id);
		//var_dump($collection->cap);
		$this->assertTrue(user_can($user_id, $collection->cap->read, $collection->get_id()), 'A subscriber user cannot read Collections');
		$this->assertTrue(user_can($user_id, 'subscriber'));
		$this->assertFalse(user_can($user_id, $collection->cap->edit_post, $collection->get_id()), 'A subscriber user can edit a Collections?');
		
		
		$new_admin_user = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_admin_user);
		$user_id = get_current_user_id();
		$this->assertTrue(user_can($user_id, 'administrator'));
		$this->assertTrue(user_can($user_id, $collection->cap->edit_post, $collection->get_id()), 'A administrator user cannot edit a Collections?');
		//TODO test all roles and check the capabilities
		
		$new_contributor_user = $this->factory()->user->create(array( 'role' => 'contributor' ));
		wp_set_current_user($new_contributor_user);
		$this->assertTrue($collection->can_read());
		$this->assertFalse($collection->can_publish());
        
        
		$this->assertTrue(user_can($new_admin_user, $collection->get_items_capabilities()->edit_posts, $collection->get_id()), 'admin should be able to edit items in the collection');
        
        $privateCollection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testePermsCC',
				'description'   => 'adasdasdsa',
                'status'        => 'private'
			),
			true
		);
        
		$this->assertTrue(user_can($new_admin_user, $collection->cap->read_post, $collection->get_id()), 'admin should be able read private collection');
        
        // subsciber should not be able to
        $this->assertFalse(user_can($new_user, $collection->cap->read_post, $collection->get_id()), 'subscriber should not be able read private collection');
	}
	
	/**
	 * @group serialize_permission
	 */
	function test_entity_serialization() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeSeria',
				'description'   => 'adasdasdsa',
			),
			true
		);
		
		$ser = base64_encode( maybe_serialize($collection));
		$u2 = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($u2);
		$collection_unser = maybe_unserialize( base64_decode($ser));
		$this->assertFalse(user_can($u2, $collection_unser->cap->edit_post, $collection_unser->get_id()));
	}
	
}