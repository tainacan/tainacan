<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Sample test case.
 */
class BulkEdit extends TAINACAN_UnitTestCase {

	public $items_ids = [];

	function setUp() {
		parent::setUp();
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);
		$this->collection = $collection;
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'metadado',
			    'status' => 'publish',
			    'collection' => $collection,
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
	    );
		
		$this->metadatum = $metadatum;
		
		for ($i = 1; $i<=40; $i++) {
			
			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'testeItem ' . $i,
					'collection' => $collection,
					'status' => 'publish'
				),
				true
			);
			
			$this->items_ids[] = $item->get_id();
			
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum, $i % 2 == 0 ? 'even' : 'odd');
			
		}
		
	}
	
	function test_setup() {
		$this->assertEquals(40, sizeof($this->items_ids));
	}
	
	function test_init_by_query() {
		
		
		$query = [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => -1
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);
		
		$this->assertEquals(20, $bulk->count_posts());
		
		
	}
	
	function test_init_by_ids() {
		
		$ids = array_slice($this->items_ids, 2, 7);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);
		
		$this->assertEquals(7, $bulk->count_posts());
		
	}
	
	function test_init_by_bulk_id() {
		
		$ids = array_slice($this->items_ids, 4, 11);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);
		
		$id = $bulk->get_id();
		
		$newBulk = new \Tainacan\Bulk_Edit([
			'id' => $id,
		]);
		
		$this->assertEquals(11, $newBulk->count_posts());
		
	}
}