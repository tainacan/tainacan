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
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeCaps',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);
		$user_id = get_current_user_id();
		$this->assertEquals($new_user, $user_id);
		
		//global $Tainacan_Collections;
		//$this->assertTrue($Tainacan_Collections->can_read($x));
		$this->assertFalse(current_user_can('edit_collection'));
		
		$autor1 = $this->factory()->user->create(array( 'role' => 'author' ));
		wp_set_current_user($autor1);
		$autor1_id = get_current_user_id();
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeCapsOwner',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);
		$this->assertEquals($autor1_id, $x->WP_Post->post_author);
		$autor2 = $this->factory()->user->create(array( 'role' => 'author' ));
		wp_set_current_user($autor2);
		$x2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeCapsOwner2',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);
		$current_user_id = get_current_user_id();
		$this->assertEquals($autor2, $current_user_id);
		$this->assertFalse(current_user_can($x->cap->edit_post, $x->WP_Post->ID));
		$this->assertTrue(current_user_can($x2->cap->edit_post, $x2->WP_Post->ID));
		$this->assertFalse(user_can($autor2, $x->cap->edit_post, $x->WP_Post->ID));
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
