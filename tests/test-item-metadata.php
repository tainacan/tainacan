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

        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name'              => 'metadado',
		        'description'       => 'descricao',
		        'collection'        => $collection,
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
	        ),
	        true
        );

        $test = $Tainacan_Metadata->fetch($metadatum->get_id());
        
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
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $metadatum = $this->tainacan_entity_factory->create_entity(
	    	'metadatum',
		    array(
		    	'name'              => 'metadado',
			    'description'       => 'descricao',
			    'collection'        => $collection,
			    'required'          => 'yes',
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
	    );

        $test = $Tainacan_Metadata->fetch($metadatum->get_id());
        
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
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

	    $metadatum = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'              => 'metadado',
			    'description'       => 'descricao',
			    'collection'        => $collection,
			    'collection_key'    => 'yes',
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
	    );

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
		
		$i2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'other item',
				'description' => 'adasdasdsa',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);
        
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $value = 'teste_val';
        
        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
        $item_metadata->set_value($value);

        $this->assertTrue($item_metadata->validate());

        $item_metadata->validate();
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

        $n_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
        $n_item_metadata->set_value($value);
		$this->assertTrue($n_item_metadata->validate(), 'trying to validate the same item with same value should be ok');

		$n_item_metadata2 = new \Tainacan\Entities\Item_Metadata_Entity($i2, $metadatum);
		$n_item_metadata2->set_value($value);
		$this->assertFalse($n_item_metadata2->validate(), 'Collection key should not validate another item metadatada with the same value');
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
        	'metadatum',
            array(
            	'name'              => 'metadado',
	            'description'       => 'descricao',
	            'collection'        => $collection,
	            'status'            => 'publish',
	            'metadata_type'  => 'Tainacan\Metadata_Types\Text',
            ),
	        true
        );

        //$test = $Tainacan_Metadata->fetch($metadatum->get_id());
        
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
            $names[] = $item_metadata->get_metadatum()->get_name();
        }
        
        $this->assertTrue(is_array($item_metadatas));

        // notice for repository metadata
        $this->assertEquals(3, sizeof($item_metadatas));
        //first 2 metadata are repository metadata
        $this->assertTrue( in_array('metadado', $names) );
        
	}
	
	function test_metadata_text_textarea() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'teste'
	        ),
	        true
		);
		
		$i = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'       => 'item teste',
			    'description' => 'description',
			    'collection'  => $collection,
			    'status'      => 'publish'
		    ),
		    true
	    );

	    $metadatum_text = $this->tainacan_entity_factory->create_entity(
	        'metadatum',
	        array(
		        'name'              => 'metadadoText',
		        'description'       => 'descricao',
		        'collection_id'     => $collection->get_id(),
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
	        ),
	        true
		);
		
		$metadatum_textarea = $this->tainacan_entity_factory->create_entity(
	        'metadatum',
	        array(
		        'name'              => 'metadadoTextarea',
		        'description'       => 'descricao',
		        'collection_id'     => $collection->get_id(),
		        'metadata_type'  => 'Tainacan\Metadata_Types\Textarea',
	        ),
	        true
		);
		
		$value_text = 'GOOGLE: www.google.com';
		$item_metadata_text = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_text);
		$item_metadata_text->set_value($value_text);
		
		$value_textarea = 'GOOGLE: www.google.com \n GOOGLE: https://www.google.com';
		$item_metadata_textarea = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum_textarea);
		$item_metadata_textarea->set_value($value_textarea);

		$response_text = 'GOOGLE: <a href="www.google.com" target="_blank" title="www.google.com">www.google.com</a>';
		$response_textarea = 'GOOGLE: <a href="www.google.com" target="_blank" title="www.google.com">www.google.com</a> \n GOOGLE: <a href="https://www.google.com" target="_blank" title="https://www.google.com">https://www.google.com</a>';

		$this->assertEquals($item_metadata_text->get_value_as_html(), $response_text);
		$this->assertEquals($item_metadata_textarea->get_value_as_html(), $response_textarea);
	}
}