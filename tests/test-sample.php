<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Test_Collections extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_add() {
		
        $x = new Tainacan_Collection();
        
        $x->set_name('teste');
        $x->set_description('adasdasdsa');
        $x->set_itens_per_page(23);
        
        global $Tainacan_Collections;
        $col = $Tainacan_Collections->insert($x);
        
        //
        
        $test = $Tainacan_Collections->get_collection_by_id($col->get_id());
        
        
        $this->assertEquals($test->get_name(), 'teste');
        $this->assertEquals($test->get_description(), 'adasdasdsa');
        $this->assertEquals($test->get_itens_per_page(), 23);
        
        
    }
    
    function test_item() {
        
        
        $x = new Tainacan_Collection();
        
        $x->set_name('teste');
        $x->set_description('adasdasdsa');
        $x->set_itens_per_page(23);
        
        global $Tainacan_Collections;
        $col = $Tainacan_Collections->insert($x);
        
        $collection = $Tainacan_Collections->get_collection_by_id($col->get_id());
        
        
        
        $i = new Tainacan_Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->get_item_by_id($item->get_id());
        
        $this->assertEquals($item->get_title(), 'item teste');
        $this->assertEquals($item->get_description(), 'adasdasdsa');
        $this->assertEquals($item->get_collection_id(), $collection->get_id());
        
    }
}
