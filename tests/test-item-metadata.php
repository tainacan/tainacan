<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Test_Item_Metadata extends WP_UnitTestCase {

    /**
     * Teste da insercao de um metadado simples sem o tipo
     */
    function test_add() {
        
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new Tainacan_Collection();
        $metadata = new Tainacan_Metadata();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );

        //inserindo o metadado
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->get_metadata_by_id($metadata->get_id());
        
        $i = new Tainacan_Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->get_item_by_id($item->get_id());

        $item_metadata = new Tainacan_Item_Metadata_Entity($item, $metadata);
        
        $item_metadata->set_value('teste_value');
        
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        
        $this->assertEquals('teste_value', $item_metadata->get_value());
        
        

    }

    /**
     * Teste da insercao de um metadado simples com o tipo
     */
    function teste_required(){
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new Tainacan_Collection();
        $metadata = new Tainacan_Metadata();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );
        $metadata->set_required( 'yes' );

        //inserindo o metadado
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->get_metadata_by_id($metadata->get_id());
        
        $i = new Tainacan_Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->get_item_by_id($item->get_id());

        $item_metadata = new Tainacan_Item_Metadata_Entity($item, $metadata);
        
        // false because its required
        $this->assertFalse($item_metadata->validate());
        
        $item_metadata->set_value('teste_value');
        
        $this->assertTrue($item_metadata->validate());
        
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
        
        $this->assertEquals('teste_value', $item_metadata->get_value());
    }
    
    function teste_collection_key(){
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new Tainacan_Collection();
        $metadata = new Tainacan_Metadata();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );
        $metadata->set_collection_key( 'yes' );

        //inserindo o metadado
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->get_metadata_by_id($metadata->get_id());
        
        $i = new Tainacan_Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->get_item_by_id($item->get_id());

        
        
        $value = 'teste_val';
        
        $item_metadata = new Tainacan_Item_Metadata_Entity($item, $metadata);
        $item_metadata->set_value($value);
        $this->assertTrue($item_metadata->validate());
        $item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

        $n_item_metadata = new Tainacan_Item_Metadata_Entity($item, $metadata);
        $n_item_metadata->set_value($value);
        $this->assertFalse($n_item_metadata->validate());
    }
}