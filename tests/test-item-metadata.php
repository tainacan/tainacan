<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Item_Metadata extends TAINACAN_UnitTestCase {

    /**
     * Teste da insercao de um metadado simples sem o tipo
     */
    function test_add() {

        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name'              => 'metadado',
		        'description'       => 'descricao',
		        'collection'        => $collection,
		        'field_type'  => 'Tainacan\Field_Types\Text',
	        ),
	        true
        );

        $test = $Tainacan_Fields->fetch($field->get_id());
        
        $i = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'       => 'item teste',
		        'description' => 'adasdasdsa',
		        'collection'  => $collection
	        ),
	        true
        );
        
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        
        $item = $Tainacan_Items->fetch($i->get_id());

        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $test);
        $item_metadata->set_value('teste_value');
		
		$item_metadata->validate();
		
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        
        $this->assertEquals('teste_value', $item_metadata->get_value());
    }

    /**
     * Teste da insercao de um metadado simples com o tipo
     */
    function teste_required(){
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $field = $this->tainacan_entity_factory->create_entity(
	    	'field',
		    array(
		    	'name'              => 'metadado',
			    'description'       => 'descricao',
			    'collection'        => $collection,
			    'required'          => 'yes',
			    'field_type'  => 'Tainacan\Field_Types\Text',
		    ),
		    true
	    );

        $test = $Tainacan_Fields->fetch($field->get_id());
        
        $i = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'       => 'item teste',
		        'description' => 'adasdasdsa',
		        'collection'  => $collection
	        ),
	        true
        );
        
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        
        $item = $Tainacan_Items->fetch($i->get_id());
        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $test);
        
        // false because its required
        $this->assertFalse($item_metadata->validate());
        
        $item_metadata->set_value('teste_value');
        
        $this->assertTrue($item_metadata->validate());
        
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        
        $this->assertEquals('teste_value', $item_metadata->get_value());
    }
    
    function teste_collection_key(){
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $field = $this->tainacan_entity_factory->create_entity(
		    'field',
		    array(
			    'name'              => 'metadado',
			    'description'       => 'descricao',
			    'collection'        => $collection,
			    'collection_key'    => 'yes',
			    'field_type'  => 'Tainacan\Field_Types\Text',
		    ),
		    true
	    );

        $test = $Tainacan_Fields->fetch($field->get_id());

	    $i = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'       => 'item teste',
			    'description' => 'adasdasdsa',
			    'collection'  => $collection,
			    'status'      => 'publish'
		    ),
		    true
	    );
        
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $item = $Tainacan_Items->fetch($i->get_id());

        $value = 'teste_val';
        
        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $test);
        $item_metadata->set_value($value);

        $this->assertTrue($item_metadata->validate());

        $item_metadata->validate();
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

        $n_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $test);
        $n_item_metadata->set_value($value);

        $this->assertFalse($n_item_metadata->validate());
    }
    
    function teste_fetch(){
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );


        $this->tainacan_entity_factory->create_entity(
        	'field',
            array(
            	'name'              => 'metadado',
	            'description'       => 'descricao',
	            'collection'        => $collection,
	            'status'            => 'publish',
	            'field_type'  => 'Tainacan\Field_Types\Text',
            ),
	        true
        );

        //$test = $Tainacan_Fields->fetch($field->get_id());
        
        $i = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title' => 'item teste',
		        'description' => 'adasdasdsa',
		        'collection' => $collection
	        ),
	        true
        );

        $item_metadatas = $Tainacan_Item_Metadata->fetch($i, 'OBJECT');

        $names = [];
        foreach ($item_metadatas as $item_metadata) {
            $names[] = $item_metadata->get_field()->get_name();
        }
        
        $this->assertTrue(is_array($item_metadatas));

        // notice for repository fields
        $this->assertEquals(3, sizeof($item_metadatas));
        //first 2 fields are repository fields
        $this->assertTrue( in_array('metadado', $names) );
        
    }
}