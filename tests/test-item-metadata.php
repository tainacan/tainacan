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
        
        global $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $type = $this->tainacan_field_factory->create_field('text');

	    $metadata = $this->tainacan_entity_factory->create_entity(
        	'metadata',
	        array(
	        	'name'              => 'metadado',
		        'description'       => 'descricao',
		        'collection'        => $collection,
		        'field_type' => $type
	        ),
	        true
        );

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());
        
        $i = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'       => 'item teste',
		        'description' => 'adasdasdsa',
		        'collection'  => $collection
	        ),
	        true
        );
        
        global $Tainacan_Items;
        
        $item = $Tainacan_Items->fetch($i->get_id());

        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $test);
        $item_metadata->set_value('teste_value');

        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        
        $this->assertEquals('teste_value', $item_metadata->get_value());
    }

    /**
     * Teste da insercao de um metadado simples com o tipo
     */
    function teste_required(){
        global $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $type = $this->tainacan_field_factory->create_field('text');

	    $metadata = $this->tainacan_entity_factory->create_entity(
	    	'metadata',
		    array(
		    	'name'              => 'metadado',
			    'description'       => 'descricao',
			    'collection'        => $collection,
			    'required'          => 'yes',
			    'field_type' => $type
		    ),
		    true
	    );

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());
        
        $i = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'       => 'item teste',
		        'description' => 'adasdasdsa',
		        'collection'  => $collection
	        ),
	        true
        );
        
        global $Tainacan_Items;
        
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
        global $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $type = $this->tainacan_field_factory->create_field('text');

	    $metadata = $this->tainacan_entity_factory->create_entity(
		    'metadata',
		    array(
			    'name'              => 'metadado',
			    'description'       => 'descricao',
			    'collection'        => $collection,
			    'collection_key'    => 'yes',
			    'field_type' => $type
		    ),
		    true
	    );

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());

	    $i = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'       => 'item teste',
			    'description' => 'adasdasdsa',
			    'collection'  => $collection
		    ),
		    true
	    );
        
        global $Tainacan_Items;

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
        global $Tainacan_Item_Metadata;

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );


	    $type = $this->tainacan_field_factory->create_field('text');

        $this->tainacan_entity_factory->create_entity(
        	'metadata',
            array(
            	'name'              => 'metadado',
	            'description'       => 'descricao',
	            'collection'        => $collection,
	            'status'            => 'publish',
	            'field_type' => $type
            ),
	        true
        );

        //$test = $Tainacan_Metadatas->fetch($metadata->get_id());
        
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
        
        $this->assertTrue(is_array($item_metadatas));
        $this->assertEquals(1, sizeof($item_metadatas));
        $this->assertEquals('metadado', $item_metadatas[0]->get_metadata()->get_name());
        
    }
}