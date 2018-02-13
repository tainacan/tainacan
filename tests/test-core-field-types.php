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
class CoreFieldTypes extends TAINACAN_UnitTestCase {

	
    function test_core_field_types() {
        
        global $Tainacan_Item_Metadata, $Tainacan_Items;
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
        
        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'metadado',
		        'description' => 'title',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Core_Title'
	        ),
	        true
        );
        
        $fieldDescription = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'metadado_desc',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Core_Description'
	        ),
	        true
        );
        
        
        $i = $this->tainacan_entity_factory->create_entity(
           'item',
           array(
               'title'         => 'item test',
               'description'   => 'adasdasdsa',
               'collection'    => $collection
           ),
           true
       );
       
       
       $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $field);
       $item_metadata->set_value('changed title');
       $item_metadata->validate();
       
       $Tainacan_Item_Metadata->insert($item_metadata);
       
       $checkItem = $Tainacan_Items->fetch($i->get_id());
       
       $this->assertEquals('changed title', $checkItem->get_title());
       
       $check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($checkItem, $field);
       $this->assertEquals('changed title', $check_item_metadata->get_value());
       
       
       // description
       $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $fieldDescription);
       $item_metadata->set_value('changed description');
       $item_metadata->validate();
       
       $Tainacan_Item_Metadata->insert($item_metadata);
       
       $checkItem = $Tainacan_Items->fetch($i->get_id());
       
       $this->assertEquals('changed description', $checkItem->get_description());
       
       $check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($checkItem, $fieldDescription);
       $this->assertEquals('changed description', $check_item_metadata->get_value());
       
    }
    
}