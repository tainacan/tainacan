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

        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        
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

    function test_validate_required_title() {

        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'   => 'test',
            ),
            true
        );

        $i = $this->tainacan_entity_factory->create_entity(
            'item',
            array(
                'description'   => 'adasdasdsa',
                'collection'    => $collection,
                'status'        => 'draft'
            ),
            true
        );

        $fields = $Tainacan_Fields->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        foreach ( $fields as $index => $field ){
            if ( $field->get_field_type_object()->get_core() && $field->get_field_type_object()->get_related_mapped_prop() == 'title') {
                $core_title = $field;
            }
        }



        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $core_title);
        $item_metadata->set_value('title');
        $item_metadata->validate();
        $Tainacan_Item_Metadata->insert($item_metadata);

        $i->set_status('publish' );

        $this->assertTrue($i->validate(), 'Item with empy title should validate because core title field has value');

    }

    function test_dont_allow_multiple() {

        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'   => 'test',
            ),
            true
        );

        $fields = $Tainacan_Fields->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        foreach ( $fields as $index => $field ){
            if ( $field->get_field_type_object()->get_core() && $field->get_field_type_object()->get_related_mapped_prop() == 'title') {
                $core_title = $field;
            }
            if ( $field->get_field_type_object()->get_core() && $field->get_field_type_object()->get_related_mapped_prop() == 'description') {
                $core_description = $field;
            }
        }

        $core_title->set_multiple('yes');
        $core_description->set_multiple('yes');

        $this->assertFalse($core_title->validate(), 'Core metadata should not validate because it can not allow it to have multiple');
        $this->assertFalse($core_description->validate(), 'Core metadata should not validate because it can not allow it to have multiple');

    }
    
}