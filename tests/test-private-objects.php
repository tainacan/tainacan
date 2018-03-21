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
 /**
  * @group privateObjects
  */
class PrivateObjects extends TAINACAN_UnitTestCase {

	// TODO Test the same things via API
    
	public function test_private_items () {
		
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testePerm',
                'status' => 'publish'
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
        
        $Tainacan_Items = \Tainacan\Repositories\Items::getInstance();
        
        $items = $Tainacan_Items->fetch([], $collection);
        
        $this->assertEquals(2, $items->found_posts, 'admins should see all 2 items');
        
        $items = $Tainacan_Items->fetch(['post_status' => 'private', 'perm' => 'readable'], $collection);
        $this->assertEquals(1, $items->found_posts, 'contributors should not see private items');
        
        $new_contributor_user = $this->factory()->user->create(array( 'role' => 'contributor' ));
		wp_set_current_user($new_contributor_user);
        
        $items = $Tainacan_Items->fetch([], $collection);
        $this->assertEquals(1, $items->found_posts, 'contributors should not see private items');
        
        $items = $Tainacan_Items->fetch(['post_status' => 'private', 'perm' => 'readable'], $collection);
        $this->assertEquals(0, $items->found_posts, 'contributors should not see private items');
        
        $items = $Tainacan_Items->fetch(['post_status' => 'private'], $collection);
        $this->assertEquals(1, $items->found_posts, 'contributors should not see private items');
		
	}
    
    public function test_items_in_private_collections () {
		
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testePerm',
                'status' => 'publish'
			),
			true
		);
        $privateCollection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testePerm',
                'status' => 'private'
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
        $item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testItem',
				'collection' => $collection,
                'status'     => 'publish'
			),
			true
		);
        
        $item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testItem',
				'collection' => $privateCollection,
                'status'     => 'publish'
			),
			true
		);
        $item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testItem',
				'collection' => $privateCollection,
                'status'     => 'publish'
			),
			true
		);
        
        $new_contributor_user = $this->factory()->user->create(array( 'role' => 'contributor' ));
		wp_set_current_user($new_contributor_user);

        $Tainacan_Items = \Tainacan\Repositories\Items::getInstance();
        $Tainacan_Collections = \Tainacan\Repositories\Collections::getInstance();
        
        $items = $Tainacan_Items->fetch([], $collection);
        $this->assertEquals(2, $items->found_posts, 'items of a public collections should be visible');
        
        $items = $Tainacan_Items->fetch([], $privateCollection);
        $this->assertEquals(0, $items->found_posts, 'items of a private collection should not be visible');
        
        $privateCollection->set_status('publish');
        $privateCollection->validate();
        $privateCollection = $Tainacan_Collections->insert($privateCollection);
        
        $items = $Tainacan_Items->fetch([], $privateCollection);
        $this->assertEquals(2, $items->found_posts, 'items should be visible after collections is made public');
        
        $privateCollection->set_status('private');
        $privateCollection->validate();
        $privateCollection = $Tainacan_Collections->insert($privateCollection);
        
        $items = $Tainacan_Items->fetch([], $privateCollection);
        $this->assertEquals(0, $items->found_posts, 'items should not be visible after collection is made private');
		
	}
    
}