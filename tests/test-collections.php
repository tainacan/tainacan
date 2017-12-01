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
class Collections extends \WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_add() {
		
		$x = new \Tainacan\Entities\Collection();
        
        $x->set_name('teste');
        $x->set_description('adasdasdsa');
        $x->set_default_order('DESC');
        
        global $Tainacan_Collections;
        $x->validate();
        $col = $Tainacan_Collections->insert($x);
        
        $this->assertEquals('Tainacan\Entities\Collection', get_class($col));
        
        $test = $Tainacan_Collections->fetch($col->get_id());

        $this->assertEquals($test->get_name(), 'teste');
        $this->assertEquals($test->get_description(), 'adasdasdsa');
        $this->assertEquals($test->get_default_order(), 'DESC');
        $this->assertEquals('draft', $test->get_status());
    }
    
    function test_item() {
        
        
    	$x = new \Tainacan\Entities\Collection();
        
        $x->set_name('teste');
        $x->set_description('adasdasdsa');
        $x->set_default_order('DESC');
        
        global $Tainacan_Collections;
        $x->validate();
        $col = $Tainacan_Collections->insert($x);
        
        $collection = $Tainacan_Collections->fetch($col->get_id());
        
        
        
        $i = new \Tainacan\Entities\Item();
        
        $i->set_title('item teste');
        $i->set_description('adasdasdsa');
        $i->set_collection($collection);
        
        global $Tainacan_Items;
        $i->validate();
        $item = $Tainacan_Items->insert( $i );
        
        $item = $Tainacan_Items->fetch( $item->get_id() );
        
        $this->assertEquals($item->get_title(), 'item teste');
        $this->assertEquals($item->get_description(), 'adasdasdsa');
        $this->assertEquals($item->get_collection_id(), $collection->get_id());
        
    }
    
    function test_validation() {
		
    	$x = new \Tainacan\Entities\Collection();
        
        $x->set_name('teste');
        $x->set_description('adasdasdsa');
        $x->set_default_order(13);
        
        $this->assertFalse($x->validate());
        $this->assertTrue(sizeof($x->get_errors()) > 0);
        
        $x->set_default_order('ASDASD');
        
        $this->assertFalse($x->validate());
        $this->assertTrue(sizeof($x->get_errors()) > 0);
        
        $x->set_default_order('DESC');
        $this->assertTrue($x->validate());
        $this->assertTrue(empty($x->get_errors()));
        
        global $Tainacan_Collections;
        
        $this->assertTrue(has_action('init', array($Tainacan_Collections, 'register_post_type')) !== false, 'Collections Init is not registred!');
        
    }
}
