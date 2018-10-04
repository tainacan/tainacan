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
class BulkEdit extends TAINACAN_UnitApiTestCase {

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

		$multiple_meta = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'multimetadado',
			    'status' => 'publish',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple' => 'yes',
				'required' => 'no'
		    ),
		    true
		);
		
		$this->multiple_meta = $multiple_meta;

		$taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes'
	        ),
	        true
		);
		
		$this->taxonomy = $taxonomy;

		$category = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'category',
			    'status' => 'publish',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => true,
					'taxonomy_id' => $taxonomy->get_id()
				],
				'multiple' => 'yes'
		    ),
		    true
	    );
		
		$this->category = $category;

		for ($i = 1; $i<=40; $i++) {
			
			$title = 'testeItem ' . str_pad($i, 2, "0", STR_PAD_LEFT); 
			
			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => $title,
					'collection' => $collection,
					'status' => 'publish'
				),
				true
			);
			
			$this->items_ids[] = $item->get_id();
			
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum, $i % 2 == 0 ? 'even' : 'odd');
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $category, ['good', 'bad']);
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $collection->get_core_title_metadatum(), $title);
		
		}

		$this->api_baseroute = $this->namespace . '/collection/' . $collection->get_id() . '/bulk-edit';
		
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

	function test_add() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

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

		$bulk->add_value($this->category, 'test');

		$items = $Tainacan_Items->fetch([
			
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'odd'
				]
			],
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts);

		$bulk->add_value($this->multiple_meta, 'super');

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				],
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'super'
				]
			],
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'odd'
				],
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'super'
				]
			],

			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts);

	}

	function test_remove_value_from_taxonomy_metadatum() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

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

		
		
		$bulk->remove_value($this->category, 'good');



		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'good'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'bad'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(40, $items->found_posts);


	}

	function test_remove_value_from_regular_metadatum() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);

		
		$bulk->add_value($this->multiple_meta, 'test'); // for everyone


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


		$bulk->remove_value($this->multiple_meta, 'test');


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);


	}

	function test_replace_value_in_tax_metadata() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

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


		$bulk->replace_value($this->category, 'awesome', 'good');

		
		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'good'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'awesome'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'bad'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(40, $items->found_posts);

	}

	function test_replace_regular_metadata() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$query = [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => 5
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);


		$bulk->replace_value($this->metadatum, 'super', 'even');


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(5, $items->found_posts);


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(15, $items->found_posts);

	}
	
	/**
	 * @group replace
	 */
	function test_replace_only_when_search_is_present_tax() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
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
		
		// add test to 20 items
		$bulk->add_value($this->category, 'test');
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);
	
		$bulk->replace_value($this->category, 'super', 'test');

		// should add super only to the 20 items that had test
		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);


		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts);
		
	}
	
	/**
	 * @group replace
	 */
	function test_replace_only_when_search_is_present() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		// all items
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);
	
		$bulk->replace_value($this->metadatum, 'super', 'even');

		// should add super only to the 20 items that had even
		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts);

	}
	
	/**
	 * @group replace
	 */
	function test_replace_core_metadatum() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		// all items
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);
		
		$core_title = $this->collection->get_core_title_metadatum();
		
		// all items selected, search and replace the value of one
		$bulk->replace_value($core_title, 'super_test', 'testeItem 22');
		
		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $core_title->get_id(),
					'value' => 'super_test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(1, $items->found_posts);
		
		$items = $Tainacan_Items->fetch(['title' => 'super_test']);
		
		$this->assertEquals(1, $items->found_posts);
		
	}

	function test_set_tax_meta() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$query = [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => 5
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);




		$bulk->set_value($this->category, 'super');




		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'bad'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(35, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'good'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(35, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(5, $items->found_posts);



	}

	function test_set_regular_meta() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$query = [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => 5
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);


		$bulk->set_value($this->metadatum, 'super');


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(5, $items->found_posts);


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(15, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'odd'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);
	}
	
	function test_set_status() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$ids = array_slice($this->items_ids, 4, 11);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);
		
		$id = $bulk->get_id();

		$bulk->set_status('draft');

		$items = $Tainacan_Items->fetch([
			'status' => 'draft',
			'posts_per_page' => -1
		]);

		$this->assertEquals(11, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'publish' => 'draft',
			'posts_per_page' => -1
		]);

		$this->assertEquals(29, $items->found_posts);

	}

	function test_set_regular_multi_meta() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);

		
		$bulk->add_value($this->multiple_meta, 'test'); // for everyone
		$bulk->add_value($this->multiple_meta, 'super'); // for everyone

		$ids = array_slice($this->items_ids, 2, 7);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);

		
		
		$bulk->set_value($this->multiple_meta, 'ultra');



		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(33, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(33, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'ultra'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(7, $items->found_posts);

	}

	function test_set_core_metadata() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$core_title = $this->collection->get_core_title_metadatum();
		$core_description = $this->collection->get_core_description_metadatum();

		$ids = array_slice($this->items_ids, 2, 7);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);


		$bulk->set_value($core_title, 'test_title');
		$bulk->set_value($core_description, 'test_description');


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $core_title->get_id(),
					'value' => 'test_title'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(7, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'title' => 'test_title',
			'posts_per_page' => -1
		]);

		$this->assertEquals(7, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $core_description->get_id(),
					'value' => 'test_description'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(7, $items->found_posts);

		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_content = 'test_description'" );

		$this->assertEquals(7, $count);


	}

	/**
	 * @group api
	 */
	function test_api_create_by_items_ids() {

		$ids = array_slice($this->items_ids, 2, 17);

		$request = new \WP_REST_Request(
			'POST', $this->api_baseroute
		);

		$request->set_body( json_encode(['items_ids' => $ids]) );

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertTrue(is_string($data['id']));

		$this->assertEquals(17, $data['items_count']);


	}

	/**
	 * @group api
	 */
	function test_api_create_by_query() {

		$query = [

			'metaquery' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'odd'
				]
			],
			'taxquery' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'good'
				]
			],
			'perpage' => 4,
			'paged' => 2

		];

		

		$request = new \WP_REST_Request(
			'POST', $this->api_baseroute
		);

		//$request->set_query_params($query);

		$request->set_body( json_encode(['use_query' => $query]) );

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertTrue(is_string($data['id']));

		$this->assertEquals(20, $data['items_count']);


	}

	/**
	 * @group api
	 */
	public function test_api_add_action() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$ids = array_slice($this->items_ids, 2, 14);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);

		$body = json_encode([
			'metadatum_id' => $this->multiple_meta->get_id(),
			'value' => 'superduper'
		]);

		
		$request = new \WP_REST_Request(
			'POST', $this->api_baseroute . '/' . $bulk->get_id() . '/add'
		);

		$request->set_body( $body );

		$response = $this->server->dispatch($request);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'superduper'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(14, $items->found_posts);


	}
	
	/**
	 * @group api
	 */
	public function test_api_set_status() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$ids = array_slice($this->items_ids, 2, 14);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);

		$body = json_encode([
			'value' => 'private'
		]);

		
		$request = new \WP_REST_Request(
			'POST', $this->api_baseroute . '/' . $bulk->get_id() . '/set_status'
		);

		$request->set_body( $body );

		$response = $this->server->dispatch($request);

		$items = $Tainacan_Items->fetch([
			'status' => 'private',
			'posts_per_page' => -1
		]);

		$this->assertEquals(14, $items->found_posts);


	}

	/**
	 * @group trash
	 */
	function test_trash() {

		$ids = array_slice($this->items_ids, 2, 17);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);

		
		$this->assertEquals( 17, $bulk->trash_items() );

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$trashed = $Tainacan_Items->fetch_ids(['post_status' => 'trash', 'posts_per_page' => -1]);
		$rest = $Tainacan_Items->fetch_ids(['posts_per_page' => -1]);

		
		$this->assertEquals(17, sizeof($trashed));
		$this->assertEquals(40 - 17, sizeof($rest));
		

	}

	/**
	 * @group trash
	 */
	function test_untrash() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$items = $Tainacan_Items->fetch(['posts_per_page' => -1], [], 'OBJECT');

		// Lets set 17 as private
		$i = 1;
		foreach ($items as $item) {
			
			If ($i > 17) break;
			
			$item->set_status('private');
			$item->validate();
			$Tainacan_Items->update($item);
			
			$i++;
		}

		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);
		
		$this->assertEquals( 40, $bulk->trash_items() ); // trash all items

		$trashed = $Tainacan_Items->fetch_ids(['post_status' => 'trash', 'posts_per_page' => -1]);
		$rest = $Tainacan_Items->fetch_ids(['posts_per_page' => -1]);

		$this->assertEquals(40, sizeof($trashed));
		$this->assertEquals(0, sizeof($rest));

		$this->assertEquals( 40, $bulk->untrash_items() ); // untrash all items
		
		$trashed = $Tainacan_Items->fetch_ids(['post_status' => 'trash', 'posts_per_page' => -1]);
		$private = $Tainacan_Items->fetch_ids(['post_status' => 'private', 'posts_per_page' => -1]);
		$public = $Tainacan_Items->fetch_ids(['post_status' => 'publish', 'posts_per_page' => -1]);

		$this->assertEquals(0, sizeof($trashed));
		$this->assertEquals(17, sizeof($private));
		$this->assertEquals(40 - 17, sizeof($public));
		

	}

	/**
	 * @group trash
	 */
	function test_delete_items() {

		$ids = array_slice($this->items_ids, 2, 17);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);

		$this->assertEquals( 0, $bulk->delete_items(), 'Items must be on trash to be deleted' );

		$bulk->trash_items();

		$this->assertEquals( 17, $bulk->delete_items() );

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$trashed = $Tainacan_Items->fetch_ids(['post_status' => 'trash', 'posts_per_page' => -1]);
		$rest = $Tainacan_Items->fetch_ids(['posts_per_page' => -1]);

		
		$this->assertEquals(0, sizeof($trashed));
		$this->assertEquals(40 - 17, sizeof($rest));
		

	}

	function test_repeated_terms() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

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

		$bulk->add_value($this->category, 'test');

		$items = $Tainacan_Items->fetch([
			
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(20, $items->found_posts);

		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);

		$bulk->add_value($this->category, 'test');

		$items = $Tainacan_Items->fetch([
			
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(40, $items->found_posts);

	}

	function test_allow_new_terms() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		$taxonomy2 = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'tax2',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes'
	        ),
	        true
		);

		$category2 = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'category_2',
			    'status' => 'publish',
			    'collection' => $this->collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => false,
					'taxonomy_id' => $taxonomy2->get_id()
				],
				'multiple' => 'yes'
		    ),
		    true
		);

		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $this->items_ids,
		]);

		$bulk->add_value($category2, 'test_new_value');


		$items = $Tainacan_Items->fetch([
			
			'tax_query' => [
				[
					'taxonomy' => $taxonomy2->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test_new_value'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts);



		$bulk->set_value($category2, 'test_new_value');

		$items = $Tainacan_Items->fetch([
			
			'tax_query' => [
				[
					'taxonomy' => $taxonomy2->get_db_identifier(),
					'field' => 'name',
					'terms' => 'test_new_value'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts);



	}

	function test_create_delete_group() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$ids = array_slice($this->items_ids, 2, 7);
		
		$bulk = new \Tainacan\Bulk_Edit([
			'items_ids' => $ids,
		]);

		$bulk->trash_items();

		$query = [
			'status' => 'trash',
			'posts_per_page' => -1
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);

		$this->assertEquals(7, $bulk->count_posts());


	}
	
	/**
	* @group sequence
	*/
	function test_get_item_in_sequence() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		$query = [
			'posts_per_page' => 22,
			'orderby' => 'title'
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);
		
		$item_id = $bulk->get_item_id_by_index(7);
		
		$item = $Tainacan_Items->fetch($item_id);
		
		$this->assertEquals('testeItem 34', $item->get_title());
		
		$item_id = $bulk->get_item_id_by_index(15);
		
		$item = $Tainacan_Items->fetch($item_id);
		
		$this->assertEquals('testeItem 26', $item->get_title());
		
		$query = [
			'posts_per_page' => 22,
			'orderby' => 'title',
			'order' => 'ASC'
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);
		
		$item_id = $bulk->get_item_id_by_index(19);
		
		$item = $Tainacan_Items->fetch($item_id);
		
		$this->assertEquals('testeItem 19', $item->get_title());
		
		$item_id = $bulk->get_item_id_by_index(30);
		
		$this->assertFalse($item_id);
		
		
	}
	
	/**
	* @group sequence
	*/
	function test_api_get_item_in_sequence() {
		
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		$query = [
			'posts_per_page' => 22,
			'orderby' => 'title',
			'order' => 'ASC'
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);
		
		
		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $bulk->get_id() . '/sequence/7'
		);

		$response = $this->server->dispatch($request);
		
		$id = $response->get_data();
		
		$item = $Tainacan_Items->fetch($id);

		$this->assertEquals('testeItem 07', $item->get_title());
		
		
	}

	function test_api_get_group() {
		
		$query = [
			'posts_per_page' => 22,
			'orderby' => 'title',
			'order' => 'ASC'
		];
		
		$bulk = new \Tainacan\Bulk_Edit([
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		]);
		
		
		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $bulk->get_id()
		);

		$response = $this->server->dispatch($request);
		
		$data = $response->get_data();
		
		$this->assertEquals($bulk->get_id(), $data['id']);
		$this->assertEquals($bulk->get_options()['order'], $data['options']['order']);
		$this->assertEquals($bulk->get_options()['orderby'], $data['options']['orderby']);
		$this->assertEquals($bulk->count_posts(), $data['items_count']);
		
		
		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/fefefe23232'
		);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(404, $response->get_status());
		
	}
	
	


}