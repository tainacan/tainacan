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
class CategoryFieldTypes extends TAINACAN_UnitTestCase {

	
    function test_category_field_types() {
        
        global $Tainacan_Item_Metadata, $Tainacan_Items, $Tainacan_Fields, $Tainacan_Terms;
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
		
		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test',
				'collections' => [$collection],
			),
			true
		);
        
        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Category',
				'status'	 => 'publish',
				'field_type_options' => [
					'taxonomy_id' => $tax->get_db_identifier(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
        
        $field2 = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta2',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Category',
				'status'	 => 'draft',
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
	   
		
		$term = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $tax->get_db_identifier(),
			    'name'     => 'Red',
		    ),
		    true
	    );

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $field);
		$item_metadata->set_value('Red');

		$this->assertTrue($item_metadata->validate(), 'item metadata should validate because it is an existing term');

		$Tainacan_Item_Metadata->insert($item_metadata);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $field);
		$item_metadata->set_value('love');

		$this->assertFalse($item_metadata->validate(), 'item metadata should not validate because it does not allow new terms');

		// Lets change it
		$options = $field->get_field_type_options();
		$options['allow_new_terms'] = true;
		$field->set_field_type_options($options);
		$field->validate();
		$field = $Tainacan_Fields->insert($field);

		$item_metadata->set_field($field);

		$this->assertTrue($item_metadata->validate(), 'item metada should validate because it now allows new terms');

		$Tainacan_Item_Metadata->insert($item_metadata);

		$checkItem = $Tainacan_Items->fetch($i->get_id());

		$check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($checkItem, $field);

		$this->assertEquals('WP_Term', get_class($check_item_metadata->get_value()));
		
		// test 2 fields with same category
		$field2->set_field_type_options([
			'taxonomy_id' => $tax->get_db_identifier(),
		]);
		$field2->set_status('publish');
		
		$this->assertFalse($field2->validate(), 'Category Field should not validate when using a category in use by another field in the same collection');
		$errors = $field2->get_errors();
		$this->assertInternalType('array', $errors);
		$this->assertArrayHasKey('taxonomy_id', $errors[0]);
    }
    
}