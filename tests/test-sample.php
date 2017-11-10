<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class TestCollections extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_add() {
		
        $x = new TainacanCollection();
        
        $x->set_name('teste');
        $x->set_description('adasdasdsa');
        $x->set_itens_per_page(23);
        
        global $TainacanCollections;
        $id = $TainacanCollections->insert($x);
        
        //
        
        $test = $TainacanCollections->get_collection_by_id($id);
        
        
        $this->assertEquals($test->get_name(), 'teste');
        $this->assertEquals($test->get_description(), 'adasdasdsa');
        $this->assertEquals($test->get_itens_per_page(), 23);
        
        
    }
    
    function test_item() {
        
        
        $x = new TainacanCollection();
        
        $x->set_name('teste');
        $x->set_description('adasdasdsa');
        $x->set_itens_per_page(23);
        
        global $TainacanCollections;
        $cid = $TainacanCollections->insert($x);
        
        $collection = $TainacanCollections->get_collection_by_id($cid);
        
        
        
        $i = new TainacanItem();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $TainacanItems;
        $id = $TainacanItems->insert($i);
        
        $item = $TainacanItems->get_item_by_id($id);
        
        $this->assertEquals($item->get_title(), 'item teste');
        $this->assertEquals($item->get_description(), 'adasdasdsa');
        $this->assertEquals($item->get_collection_id(), $cid);
        
    }
}
