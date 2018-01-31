<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Sample test case.
 */
class Items extends TAINACAN_UnitTestCase {

	/**
	 * @group permissions2
	 */
	public function test_permissions () {
		
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testePerm',
			),
			true
		);
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
			),
			true
		);
		$this->assertTrue($item->can_read(), 'Administrator cannot read the Item');
		$this->assertTrue($item->can_edit(), 'Administrator cannot edit the Item');
        
        // another administrator should be able to edit items
        $new_admin = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_admin);
        
        $this->assertTrue($item->can_read(), 'Administrator cannot read the Item');
		$this->assertTrue($item->can_edit(), 'Administrator cannot edit the Item');
		$this->assertTrue(current_user_can($collection->get_items_capabilities()->edit_post, $item->get_id()), 'Administrator cannot edit an item!');
        
		$sub = $this->factory()->user->create(array( 'role' => 'subscriber', 'display_name' => 'Sub' ));
		
		$collectionM = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testePermModerator',
				'moderators_ids'	=> [$sub]
			),
			true
		);

		$itemM = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItemModerator',
				'collection' => $collectionM,
			),
			true
		);
		$this->assertEquals([$sub], $collectionM->get_moderators_ids());
		
        wp_set_current_user($sub);
		$this->assertTrue(current_user_can($collectionM->get_items_capabilities()->edit_post, $itemM->get_id()), 'Moderators cannot edit an item!');
		$this->assertTrue($itemM->can_edit($sub), 'Moderators cannot edit an item!');
		
	}
    
    function teste_query(){
        $type = $this->tainacan_field_factory->create_field('text');

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
        	array(
        	    'name'   => 'teste',
	            'status' => 'publish'
	        ),
	        true
        );

	    $collection2 = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'   => 'teste2',
			    'status' => 'publish'
		    ),
		    true
	    );

	    $field = $this->tainacan_entity_factory->create_entity(
		    'field',
		    array(
			    'name'   => 'metadado',
			    'status' => 'publish',
			    'collection' => $collection,
			    'field_type' => $type
		    ),
		    true
	    );

	    $field2 = $this->tainacan_entity_factory->create_entity(
		    'field',
		    array(
			    'name'   => 'metadado2',
			    'status' => 'publish',
			    'collection' => $collection,
			    'field_type' => $type
		    ),
		    true
	    );

	    $field3 = $this->tainacan_entity_factory->create_entity(
		    'field',
		    array(
			    'name'              => 'metadado3',
			    'status'            => 'publish',
			    'collection'        => $collection,
			    'field_type' => $type
		    ),
		    true
	    );

        global $Tainacan_Items;

        $i = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'      => 'orange',
		        'collection' => $collection,
		        'add_metadata' => [
		        	[$field, 'value_1']
		        ],
		        'status'      => 'publish'
	        ),
	        true
        );

        $item = $Tainacan_Items->fetch($i->get_id());
        $meta_test = $item->get_field();

        $this->assertTrue( isset($meta_test[$field->get_id()]) );
        $this->assertTrue( $meta_test[$field->get_id()] instanceof Entities\Item_Metadata_Entity );
        $this->assertEquals( 'value_1', $meta_test[$field->get_id()]->get_value());

	    $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'apple',
			    'collection'   => $collection2,
			    'add_metadata' => [
			    	[$field2, 'value_2'],
				    [$field3, 'value_2']
			    ],
			    'status'      => 'publish'
		    ),
		    true
	    );

	    $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'lemon',
			    'collection'   => $collection2,
			    'add_metadata' => [
			    	[$field2, 'value_2'],
				    [$field2, 'value_3'],
				    [$field3, 'value_3']
			    ],
			    'status'      => 'publish'
		    ),
		    true
	    );

	    $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'pineapple',
			    'collection'   => $collection2,
			    'add_metadata' => [
			    	[$field2, 'value_3'],
				    [$field3, 'value_6']
			    ],
			    'status'      => 'publish'
		    ),
		    true
	    );

        // should return all 4 items
        $test_query = $Tainacan_Items->fetch([]);
        $this->assertEquals(4, $test_query->post_count );
        
        // should also return all 4 items
        $test_query = $Tainacan_Items->fetch([], [$collection, $collection2]);
        $this->assertEquals(4, $test_query->post_count);
        
        // should return 2 items
        $test_query = $Tainacan_Items->fetch(['posts_per_page' => 2], [$collection, $collection2]);
        $this->assertEquals(2, $test_query->post_count);
        
        // should return only the first item
        $test_query = $Tainacan_Items->fetch([], $collection);
        $this->assertEquals(1,$test_query->post_count);

        $test_query->the_post();
        $item1 = new Entities\Item( get_the_ID() );
        $this->assertEquals('orange', $item1->get_title() );

        $test_query = $Tainacan_Items->fetch(['title' => 'orange']);
        $test_query->the_post();
        $item2 = new Entities\Item( get_the_ID() );

        $this->assertEquals(1, $test_query->post_count);
        $this->assertEquals('orange', $item2->get_title());
        
        // should return the other 3 items
        $test_query = $Tainacan_Items->fetch([], $collection2);
        $this->assertEquals(3,$test_query->post_count);
        
        $test_query = $Tainacan_Items->fetch(['title' => 'apple']);
        $test_query->the_post();
        $item3 = new Entities\Item( get_the_ID() );

        $this->assertEquals(1, $test_query->post_count);
        $this->assertEquals('apple', $item3->get_title());
        
        // should return 1 item
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $field2->get_id(),
                    'value' => 'value_2'
                ]
            ]
        ], $collection2);
        $this->assertEquals(1, $test_query->post_count);
        
        // should return 2 items
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $field2->get_id(),
                    'value' => 'value_3'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);
        
        // should return 2 item
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $field3->get_id(),
                    'value' => 'value_2',
                    'compare' => '>'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);

    }
}