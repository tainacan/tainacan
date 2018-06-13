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
class CompoundMetadatumTypes extends TAINACAN_UnitTestCase {

	
    function test_compound_metadata_types() {

        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
		
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
	        ),
	        true
        );
        
        $metadatum_child1 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta2',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
	        ),
	        true
        );
		
		$metadatum_child2 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta3',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
	        ),
	        true
        );
        
        
        $i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'         => 'item test',
				'description'   => 'adasdasdsa',
				'collection'    => $collection,
				'status'		   => 'publish',
			),
			true
		);
	   
		$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_child1);
		$item_metadata1->set_value('Red');
		
		$item_metadata1->validate();

		$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);
		
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_child2, null, $item_metadata1->get_parent_meta_id());
		$item_metadata->set_value('Blue');
		
		$item_metadata->validate();

		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
		
		$compoundItem = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
		
		global $wpdb;
		
		$compoundValue = $compoundItem->get_value();
		
		$this->assertTrue( is_array($compoundValue), 'value of a compound should return array' );
		$this->assertEquals( 2, sizeof($compoundValue), 'value should have 2 item metadata' );
		
		$this->assertTrue( isset($compoundValue[$metadatum_child1->get_id()]), 'First element of value must be set' );
		$this->assertTrue( isset($compoundValue[$metadatum_child2->get_id()]), 'Second element of value must be set' );
		
		$this->assertTrue( $compoundValue[$metadatum_child1->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'First element of value should be an item metadata entity' );
		$this->assertTrue( $compoundValue[$metadatum_child2->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'Second element of value should be an item metadata entity' );
		
		
		$this->assertEquals( 'Red', $compoundValue[$metadatum_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Blue', $compoundValue[$metadatum_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );
		
    }
	
	function test_multiple_compound_metadata_types() {

        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
		
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'yes'
	        ),
	        true
        );
        
        $metadatum_child1 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta2',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
	        ),
	        true
        );
		
		$metadatum_child2 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta3',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
	        ),
	        true
        );
        
        
        $i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'         => 'item test',
				'description'   => 'adasdasdsa',
				'collection'    => $collection,
				'status'		   => 'publish',
			),
			true
		);
	    
		global $wpdb;
		
		
		
		
		// First Instance
		$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_child1);
		$item_metadata1->set_value('Red');
		
		$item_metadata1->validate();

		$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);
		
		
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_child2, null, $item_metadata1->get_parent_meta_id());
		$item_metadata->set_value('Blue');
		
		$item_metadata->validate();

		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
		
		
		// Second Instance
		$item_metadata3 = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_child1);
		$item_metadata3->set_value('Green');
		
		$item_metadata3->validate();

		$item_metadata3 = $Tainacan_Item_Metadata->insert($item_metadata3);
		
		
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_child2, null, $item_metadata3->get_parent_meta_id());
		$item_metadata->set_value('Yellow');
		
		$item_metadata->validate();

		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
		
		
		
		$compoundItem = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
		
		//var_dump($wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = {$i->get_id()}"));
		//var_dump($wpdb->get_results("SELECT * FROM $wpdb->posts WHERE parent = {$metadatum->get_id()}"));
		
		$compoundValue = $compoundItem->get_value();
		
		$this->assertTrue( is_array($compoundValue), 'value of a compound should return array' );
		$this->assertEquals( 2, sizeof($compoundValue), 'value should have 2 values' );
		
		$this->assertTrue( is_array($compoundValue[0]), 'value of a compound should return array' );
		$this->assertTrue( is_array($compoundValue[1]), 'value of a compound should return array' );
		
		$this->assertTrue( isset($compoundValue[0][$metadatum_child1->get_id()]), 'First element of value must be set' );
		$this->assertTrue( isset($compoundValue[1][$metadatum_child2->get_id()]), 'Second element of value must be set' );
		
		$this->assertTrue( $compoundValue[0][$metadatum_child1->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'First element of value should be an item metadata entity' );
		$this->assertTrue( $compoundValue[1][$metadatum_child2->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'Second element of value should be an item metadata entity' );
		
		
		$this->assertEquals( 'Red', $compoundValue[0][$metadatum_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Blue', $compoundValue[0][$metadatum_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );
		
		$this->assertEquals( 'Green', $compoundValue[1][$metadatum_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Yellow', $compoundValue[1][$metadatum_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );
		
    }
	
	function test_validations_taxonomy_in_multiple() {
		
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
		
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'yes'
	        ),
	        true
        );
		
		$newMetadatum = new \Tainacan\Entities\Metadatum();
		$newMetadatum->set_name('test_multiple');
		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Taxonomy');
		$newMetadatum->set_parent($metadatum->get_id());
		
		$this->assertFalse($newMetadatum->validate(), 'You cant add a taxonomy metadatum inside a multiple compound metadatum');
		
		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Text');
		$this->assertTrue($newMetadatum->validate());
		
		
	}
	
	function test_validations_taxonomy_in_multiple_2() {
		
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
		
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'no'
	        ),
	        true
        );
		
		$newMetadatum = new \Tainacan\Entities\Metadatum();
		$newMetadatum->set_name('test_multiple');
		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Taxonomy');
		$newMetadatum->set_parent($metadatum->get_id());
		
		$this->assertTrue($newMetadatum->validate(), 'You can add a taxonomy metadatum inside a not multiple compound metadatum');
		$newMetadatum = $Tainacan_Metadata->insert($newMetadatum);
		
		$metadatum->set_multiple('yes');
		
		$this->assertFalse($metadatum->validate(), 'You cant turn a compound metadatum into multiple when there is a taxonomy metadatum inside it');
		
		
	}
	
	function test_validations_multiple_metadata() {
		
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
		
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'no'
	        ),
	        true
        );
		
		$newMetadatum = new \Tainacan\Entities\Metadatum();
		$newMetadatum->set_name('test_multiple');
		$newMetadatum->set_multiple('yes');
		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Text');
		$newMetadatum->set_parent($metadatum->get_id());
		
		$this->assertFalse($newMetadatum->validate(), 'You cant add a multiple metadatum inside a compound metadatum');
		
		$newMetadatum->set_multiple('no');
		
		$this->assertTrue($newMetadatum->validate());
		
		
	}
	
}