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
	 * @group permissions
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

	    $metadatum = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'metadado',
			    'status' => 'publish',
			    'collection' => $collection,
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
	    );

	    $metadatum2 = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'metadado2',
			    'status' => 'publish',
			    'collection' => $collection,
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
	    );

	    $metadatum3 = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'              => 'metadado3',
			    'status'            => 'publish',
			    'collection'        => $collection,
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
	    );

        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $i = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'      => 'orange',
		        'collection' => $collection,
		        'status'      => 'publish'
	        ),
	        true
        );
        
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum, 'value_1');
        
        $item = $Tainacan_Items->fetch($i->get_id());
        $meta_test = new Entities\Item_Metadata_Entity($item, $metadatum);
        $this->assertTrue( $meta_test instanceof Entities\Item_Metadata_Entity );
        $this->assertEquals( $metadatum->get_id(), $meta_test->get_metadatum()->get_id() );
        $this->assertEquals( 'value_1', $meta_test->get_value());

	    $i = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'apple',
			    'collection'   => $collection2,
			    'status'      => 'publish'
		    ),
		    true
	    );
        
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum3, 'value_2');
        
	    $i = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'lemon',
			    'collection'   => $collection2,
			    'status'      => 'publish'
		    ),
		    true
	    );
        
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum2, 'value_2');
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum2, 'value_3');
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum3, 'value_3');

	    $i = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'pineapple',
			    'collection'   => $collection2,
			    'status'      => 'publish'
		    ),
		    true
	    );
        
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum2, 'value_3');
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum3, 'value_6');

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
                    'key' => $metadatum2->get_id(),
                    'value' => 'value_3'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);
        
        // should return 2 items
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $metadatum2->get_id(),
                    'value' => 'value_3'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);
        
        // should return 2 items
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $metadatum3->get_id(),
                    'value' => 'value_2',
                    'compare' => '>'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);
		
		// test fetch ids
		$test_query = $Tainacan_Items->fetch_ids([]);
        $this->assertTrue( is_array($test_query) );
        $this->assertEquals(4, sizeof($test_query) );
		$this->assertTrue( is_int($test_query[0]) );
		$this->assertTrue( is_int($test_query[1]) );
		$this->assertTrue( is_int($test_query[2]) );
		$this->assertTrue( is_int($test_query[3]) );
		
		$test_query = $Tainacan_Items->fetch_ids(['title' => 'inexistent']);
		$this->assertTrue( is_array($test_query) );
		$this->assertEquals(0, sizeof($test_query) );
		
    }
    
    /**
     * @group comments
     */
    public function test_items_comment() {
        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'   => 'collectionComments',
                'comment_status' => 'closed'
            ),
            true,
            true
        );
        $item = $this->tainacan_entity_factory->create_entity(
            'item',
            array(
                'title'      => 'itemComments1',
                'collection' => $collection,
                'comment_status' => 'open'
            ),
            true,
            true
        );
        global $wp_query;
        
        $wp_query = new \WP_Query();
        
        $this->assertTrue(setup_postdata($item->WP_Post));
        
        $this->assertFalse(comments_open($item->get_id()));
        
        $collections = \Tainacan\Repositories\Collections::get_instance();
        $collection->set('comment_status', 'open');
        $collection->validate();
        $collections->update($collection);
        
        $this->assertTrue(comments_open($item->get_id()));
        
        $items = \Tainacan\Repositories\Items::get_instance();
        
        $item->set('comment_status', 'closed');
        $item->validate();
        $items->update($item);
        
        $this->assertFalse(comments_open($item->get_id()));
    }
}