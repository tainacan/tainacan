<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Test_Filters extends WP_UnitTestCase {


    function teste_add(){
        global $Tainacan_Collections, $Tainacan_Filters;

        $collection = new Tainacan_Collection();
        $filter = new Tainacan_Filter();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $filter->set_name('metadado');
        $filter->set_description('descricao');
        $filter->set_collection( $collection );

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