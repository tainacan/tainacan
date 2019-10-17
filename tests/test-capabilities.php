<?php

namespace Tainacan\Tests;

use Tainacan\Entities\Collection;

/**
 * Class Capabilities
 *
 * @package Test_Tainacan
 */

/**
 * @group permissions
 */
class Capabilities extends TAINACAN_UnitTestCase {
	
	function setUp() {
		parent::setUp();
		
		$subscriber = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		$this->subscriber = get_userdata( $subscriber );
		
	}
	
	/**
	 * 
	 */
	function test_super_manage_tainacan () {
		
		$this->assertFalse( user_can($this->subscriber, 'tnc_rep_manage_taxonomies') );
		
		$this->subscriber->add_cap('manage_tainacan');
		$this->subscriber->get_role_caps();
		
		$this->assertTrue( user_can($this->subscriber, 'tnc_rep_manage_taxonomies') );
		
	}
	
	function test_super_manage_tainacan_collection () {
		
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		
		$this->subscriber->add_cap('manage_tainacan_collection_25');
		$this->subscriber->get_role_caps();
		
		$this->assertTrue( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_36_read_private_filters') );
		
	}
	
	function test_items_capabilities() {
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Javascript Frameworks',
				'description' => 'The best framework to javascript',
				'status' => 'publish'
			),
			true
		);
		
		$caps = $collection->get_items_capabilities();
		
		
		
	}
	
}