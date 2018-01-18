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

	    $metadata = $this->tainacan_entity_factory->create_entity(
		    'metadata',
		    array(
			    'name'   => 'metadado',
			    'status' => 'publish',
			    'collection' => $collection,
			    'field_type' => $type
		    ),
		    true
	    );

	    $metadata2 = $this->tainacan_entity_factory->create_entity(
		    'metadata',
		    array(
			    'name'   => 'metadado2',
			    'status' => 'publish',
			    'collection' => $collection,
			    'field_type' => $type
		    ),
		    true
	    );

	    $metadata3 = $this->tainacan_entity_factory->create_entity(
		    'metadata',
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
		        	[$metadata, 'value_1']
		        ]
	        ),
	        true
        );

        $item = $Tainacan_Items->fetch($i->get_id());
        $meta_test = $item->get_metadata();

        $this->assertTrue( isset($meta_test[$metadata->get_id()]) );
        $this->assertTrue( $meta_test[$metadata->get_id()] instanceof Entities\Item_Metadata_Entity );
        $this->assertEquals( 'value_1', $meta_test[$metadata->get_id()]->get_value());

	    $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'apple',
			    'collection'   => $collection2,
			    'add_metadata' => [
			    	[$metadata2, 'value_2'],
				    [$metadata3, 'value_2']
			    ]
		    ),
		    true
	    );

	    $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'lemon',
			    'collection'   => $collection2,
			    'add_metadata' => [
			    	[$metadata2, 'value_2'],
				    [$metadata2, 'value_3'],
				    [$metadata3, 'value_3']
			    ]
		    ),
		    true
	    );

	    $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'        => 'pineapple',
			    'collection'   => $collection2,
			    'add_metadata' => [
			    	[$metadata2, 'value_3'],
				    [$metadata3, 'value_6']
			    ]
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
                    'key' => $metadata2->get_id(),
                    'value' => 'value_2'
                ]
            ]
        ], $collection2);
        $this->assertEquals(1, $test_query->post_count);
        
        // should return 2 items
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $metadata2->get_id(),
                    'value' => 'value_3'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);
        
        // should return 2 item
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $metadata3->get_id(),
                    'value' => 'value_2',
                    'compare' => '>'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);

    }
}