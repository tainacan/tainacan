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
class Item_Metadata extends \WP_UnitTestCase {

    /**
     * Teste da insercao de um metadado simples sem o tipo
     */
    function test_add() {
        
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        $collection->set_name('teste');
        $collection->validate();
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );
        $metadata->set_field_type_object( $type );

        //inserindo o metadado
        $metadata->validate();
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());
        
        $i = new \Tainacan\Entities\Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $i->validate();
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->fetch($item->get_id());

        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadata);
        
        $item_metadata->set_value('teste_value');
        
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        
        $this->assertEquals('teste_value', $item_metadata->get_value());
        
        

    }

    /**
     * Teste da insercao de um metadado simples com o tipo
     */
    function teste_required(){
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        $collection->set_name('teste');
        $collection->validate();
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );
        $metadata->set_required( 'yes' );
        $metadata->set_field_type_object( $type );

        //inserindo o metadado
        $metadata->validate();
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());
        
        $i = new \Tainacan\Entities\Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $i->validate();
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->fetch($item->get_id());

        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadata);
        
        // false because its required
        $this->assertFalse($item_metadata->validate());
        
        $item_metadata->set_value('teste_value');
        
        $this->assertTrue($item_metadata->validate());
        
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        
        $this->assertEquals('teste_value', $item_metadata->get_value());
    }
    
    function teste_collection_key(){
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        $collection->set_name('teste');
        $collection->validate();
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );
        $metadata->set_collection_key( 'yes' );
        $metadata->set_field_type_object( $type );

        //inserindo o metadado
        $metadata->validate();
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());
        
        $i = new \Tainacan\Entities\Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $i->validate();
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->fetch($item->get_id());

        
        
        $value = 'teste_val';
        
        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadata);
        $item_metadata->set_value($value);
        $this->assertTrue($item_metadata->validate());
        $item_metadata->validate();
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

        $n_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadata);
        $n_item_metadata->set_value($value);
        $this->assertFalse($n_item_metadata->validate());
    }
    
    function teste_fetch(){
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        $collection->set_name('teste');
        $collection->validate();
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );
        $metadata->set_status('publish');
        $metadata->set_field_type_object( $type );

        //inserindo o metadado
        $metadata->validate();
        $metadata = $Tainacan_Metadatas->insert($metadata);

        //$test = $Tainacan_Metadatas->fetch($metadata->get_id());
        
        $i = new \Tainacan\Entities\Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $i->validate();
        $item = $Tainacan_Items->insert($i);
        

        $ItemMetadatas = $Tainacan_Item_Metadata->fetch($item, 'OBJECT');
        
        $this->assertTrue(is_array($ItemMetadatas));
        $this->assertEquals(1, sizeof($ItemMetadatas));
        $this->assertEquals('metadado', $ItemMetadatas[0]->get_metadata()->get_name());
        
    }
}