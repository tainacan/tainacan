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
class Collections extends TAINACAN_UnitTestCase {

	/**
	 * @group permissions
	 */
	function test_permissions () {
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);
		$user_id = get_current_user_id();
		$this->assertEquals($new_user, $user_id);
		//TODO test collection insert, update, save, fetch permissions
	}
	
	/**
	 * A single example test.
	 */
	function test_add() {
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);

        global $Tainacan_Collections;
        
        $this->assertEquals('Tainacan\Entities\Collection', get_class($x));
        
        $test = $Tainacan_Collections->fetch($x->get_id());

        $this->assertEquals('teste', $test->get_name());
        $this->assertEquals('adasdasdsa', $test->get_description());
        $this->assertEquals('DESC', $test->get_default_order());
        $this->assertEquals('draft', $test->get_status());
    }
    
    function test_item() {
	    $x = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'          => 'teste',
			    'description'   => 'adasdasdsa',
			    'default_order' => 'DESC'
		    ),
		    true
	    );

        global $Tainacan_Collections;
        $collection = $Tainacan_Collections->fetch($x->get_id());

	    $i = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
			    'title'         => 'item test',
			    'description'   => 'adasdasdsa',
			    'order'         => 'DESC',
			    'collection'    => $collection
		    ),
		    true
	    );

        global $Tainacan_Items;
        $item = $Tainacan_Items->fetch( $i->get_id() );
        
        $this->assertEquals($item->get_title(), 'item test');
        $this->assertEquals($item->get_description(), 'adasdasdsa');
        $this->assertEquals($item->get_collection_id(), $collection->get_id());
        
    }
    
    function test_validation() {
	    $x = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'          => 'teste',
			    'description'   => 'adasdasdsa',
			    'default_order' => 13
		    )
	    );

        $this->assertFalse($x->validate());
        $this->assertTrue(sizeof($x->get_errors()) > 0);
        
        $x->set_default_order('ASDASD');
        
        $this->assertFalse($x->validate());
        $this->assertTrue(sizeof($x->get_errors()) > 0);
        
        $x->set_default_order('DESC');
        $this->assertTrue($x->validate());
        $this->assertTrue(empty($x->get_errors()));
    }
    
    function test_hooks() {
    	global $Tainacan_Collections;
    	$this->assertTrue(has_action('init', array($Tainacan_Collections, 'register_post_type')) !== false, 'Collections Init is not registred!');
    }
}
