<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Test fetch methods to see if they return private objects (items, fiels, collections) correctly
 *
 * Private items should only be visible by logged users who have the rights
 * 
 */
class PrivateObjects extends TAINACAN_UnitTestCase {

	/**
	 * @group privateObjects
	 */
	public function test_private_items () {
		
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testePerm',
			),
			true
		);
		$privateItem = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testPrivateItem',
				'collection' => $collection,
                'status'     => 'private'
			),
			true
		);
        $item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testItem',
				'collection' => $collection,
                'status'     => 'publish'
			),
			true
		);
        
        global $Tainacan_Items;
        
        $items = $Tainacan_Items->fetch([], $collection);
        
        $this->assertEquals(2, $items->found_posts, 'admins should see all 2 items');
        
        $new_contributor_user = $this->factory()->user->create(array( 'role' => 'contributor' ));
		wp_set_current_user($new_contributor_user);
        
        $items = $Tainacan_Items->fetch([], $collection);
        $this->assertEquals(1, $items->found_posts, 'contributors should not see private items');
		
	}
    
}