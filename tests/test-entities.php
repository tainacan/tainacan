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
class TestEntities extends TAINACAN_UnitTestCase {
	
	
	function setUp() {
		parent::setUp();
		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste',
				'description' => 'Filter teste colletion'
			),
			true
		);
		
		$this->collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste2',
				'description' => 'Filter teste colletion'
			),
			true
		);
		
		$this->filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'       => 'filtro',
				'collection' => $this->collection,
				'description' => 'Teste Filtro'
			),
			true
		);
		
		$this->item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $this->collection,
			),
			true
		);
		
		$this->id_filter = $this->filter->get_id();
		$this->id_collection = $this->collection->get_id();
		
		$this->post_filter = get_post($this->filter->get_id());
		$this->post_collection = get_post($this->collection->get_id());
		
	}
	
	
	public function test_construct_with_id() {
		
		
		$test_filter = new \Tainacan\Entities\Filter( $this->id_filter );
		
		$this->assertTrue( $test_filter instanceof \Tainacan\Entities\Filter );
		
	}
	
	public function test_contruct_with_post() {
	
		$new_test_filter = new \Tainacan\Entities\Filter( $this->post_filter );
		
		$this->assertTrue( $new_test_filter instanceof \Tainacan\Entities\Filter );
		
	}
	
	public function test_construct_with_wrong_id() {	
		
		$this->expectException(\Exception::class);
		
		$test_wrong = new \Tainacan\Entities\Filter( $this->id_collection );
	
	}
	
	public function test_construct_with_wrong_post() {	
		
		$this->expectException(\Exception::class);
		
		$test_wrong = new \Tainacan\Entities\Filter( $this->post_collection );
		
		
	}
	
	public function test_contruct_item() {
		
		$item = new  \Tainacan\Entities\Item( $this->item->get_id() );
		
		$this->assertTrue( $item instanceof \Tainacan\Entities\Item );
		
		$this->assertEquals( $item->get_collection_id(), $this->id_collection );
		
	}
	
	public function test_contruct_wrong_item() {
		
		$this->expectException(\Exception::class);
		
		$item = new  \Tainacan\Entities\Item( $this->collection2->get_id() );
		
		
	}
	
	
}