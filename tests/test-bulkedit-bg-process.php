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
class BulkEditBgProcess extends TAINACAN_UnitApiTestCase {

	public $items_ids = [];

	function setUp(): void {
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
				'allow_insert' => 'yes',
				'status' => 'publish'
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
					'allow_new_terms' => 'yes',
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

	/**
	 * Returns a new bulk edit background process
	 *
	 * @param array $process_args Arguments to initialize the process. As expected by Tainacan\GenericProcess\Bulk_Edit_Process::create_bulk_edit()
	 * @param array $bulk_edit_data Arguments with the bulk edit data, as expected by Tainacan\GenericProcess\Bulk_Edit_Process::set_bulk_edit_data()
	 * @return \Tainacan\GenericProcess\Bulk_Edit_Process $process
	 */
	private function new_process($process_args, $bulk_edit_data) {
		$Tainacan_Generic_Process_Handler = \Tainacan\Generic_Process_Handler::get_instance();
		$process = $Tainacan_Generic_Process_Handler->initialize_generic_process('bulk_edit');
		$process->create_bulk_edit($process_args);
		$Tainacan_Generic_Process_Handler->save_process_instance($process);

		$process->set_bulk_edit_data($bulk_edit_data);

		return $process;
	}

	/**
	 * Runs the process
	 *
	 * @param \Tainacan\GenericProcess\Bulk_Edit_Process $process
	 * @return int|bool Returns the number of iterations the process took to run if it succeeded. Return false in case of error.
	 */
	private function run_process( \Tainacan\GenericProcess\Bulk_Edit_Process $process ) {

		$steps = 0;

		while (false !== $process->run()) {

			$steps++;

			// failsafe
			if ( $steps > 100 ) { // Its lost...
				return false;
			}

			continue;

		}
		return $steps;
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

		//$this->assertEquals(17, $data['items_count']);


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
		$this->assertEquals($this->collection->get_id(), $data['options']['collection_id']);

		//$this->assertEquals(20, $data['items_count']);


	}

	/**
	 * @group api
	 */
	function test_api_create_by_query_stores_full_set() {

		$query = [
			'metaquery' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'odd'
				]
			],
			'perpage' => 4,
			'paged' => 2,
			'offset' => 3,
			'nopaging' => 1
		];

		$request = new \WP_REST_Request(
			'POST', $this->api_baseroute
		);

		$request->set_body( json_encode(['use_query' => $query]) );

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();
		$stored_query = $data['options']['query'];

		$this->assertSame(-1, $stored_query['posts_per_page']);
		$this->assertArrayNotHasKey('paged', $stored_query);
		$this->assertArrayNotHasKey('offset', $stored_query);
		$this->assertArrayNotHasKey('nopaging', $stored_query);
		$this->assertEquals('odd', $stored_query['meta_query'][0]['value']);

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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);

		$this->assertIsInt($this->run_process($process));

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

		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id()
			],
			[
				"value" 				=> 'super',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->multiple_meta->get_id(),
			]
		);

		$this->assertIsInt($this->run_process($process));

		//$bulk->add_value($this->multiple_meta, 'super');

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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'good',
				"method" 				=> 'remove_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);

		$this->assertIsInt($this->run_process($process));

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

		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->multiple_meta->get_id(),
			]
		);

		$this->assertIsInt($this->run_process($process));

		$query = [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => -1
		];

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test',
				"method" 				=> 'remove_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->multiple_meta->get_id(),
			]
		);

		$this->assertIsInt($this->run_process($process));


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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'awesome',
				"method" 				=> 'replace_value',
				"old_value"			=> 'good',
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);

		$this->assertIsInt($this->run_process($process));


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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'super',
				"method" 				=> 'replace_value',
				"old_value"			=> 'even',
				"metadatum_id" 	=> $this->metadatum->get_id(),
			]
		);

		$this->assertIsInt($this->run_process($process));


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
	 * @group clear
	 */
	function test_clear() {

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$ids = array_slice($this->items_ids, 2, 17);

		$process = $this->new_process(
			[
				'items_ids' => $ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> null,
				"method" 				=> 'clear_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->metadatum->get_id(),
			]
		);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'compare' => 'NOT EXISTS'
				]
			],
			'posts_per_page' => -1
		]);
		$this->assertEquals(0, $items->found_posts);

		$this->assertIsInt($this->run_process($process));

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'compare' => 'NOT EXISTS'
				]
			],
			'posts_per_page' => -1
		]);
		$this->assertEquals(count($ids), $items->found_posts);

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

		// add test to 20 items
		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'super',
				"method" 				=> 'replace_value',
				"old_value"			=> 'test',
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

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
		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'super',
				"method" 				=> 'replace_value',
				"old_value"			=> 'even',
				"metadatum_id" 	=> $this->metadatum->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));


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

		$core_title = $this->collection->get_core_title_metadatum();

		// all items
		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'super_test',
				"method" 				=> 'replace_value',
				"old_value"			=> 'testeItem 22',
				"metadatum_id" 	=> $core_title->get_id(),
			]
		);
		// all items selected, search and replace the value of one
		$this->assertIsInt($this->run_process($process));

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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'super',
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

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


		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'super',
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->metadatum->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

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

		$process = $this->new_process(
			[
				'items_ids' => $ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'draft',
				"method" 				=> 'set_status',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));


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

		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids, // all
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->multiple_meta->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id(), // to the same bulk group
			],
			[
				"value" 				=> 'super',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->multiple_meta->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));


		$ids = array_slice($this->items_ids, 2, 7);

		$process = $this->new_process(
			[
				'items_ids' => $ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'ultra',
				"method" 				=> 'set_value', // SET Value! replacing operations above
				"old_value"			=> null,
				"metadatum_id" 	=> $this->multiple_meta->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));



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

		$process = $this->new_process(
			[
				'items_ids' => $ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test_title',
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $core_title->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id(), // to the same bulk group
			],
			[
				"value" 				=> 'test_description',
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $core_description->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

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

		$post_type = $this->collection->get_db_identifier();
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_content = 'test_description' and post_type = '$post_type'" );

		$this->assertEquals(7, $count);


	}


	/**
	 * @group trash
	 */
	function test_trash() {

		$ids = array_slice($this->items_ids, 2, 17);

		$process = $this->new_process(
			[
				'items_ids' => $ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> null,
				"method" 				=> 'trash_items',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));

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

		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> null,
				"method" 				=> 'trash_items',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));

		$trashed = $Tainacan_Items->fetch_ids(['post_status' => 'trash', 'posts_per_page' => -1]);
		$rest = $Tainacan_Items->fetch_ids(['posts_per_page' => -1]);

		$this->assertEquals(40, sizeof($trashed));
		$this->assertEquals(0, sizeof($rest));

		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id(),
			],
			[
				"value" 				=> null,
				"method" 				=> 'untrash_items',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));

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

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$ids = array_slice($this->items_ids, 2, 17);

		$process = $this->new_process(
			[
				'items_ids' => $ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> null,
				"method" 				=> 'delete_items',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));

		$items = $Tainacan_Items->fetch_ids(['posts_per_page' => -1]);
		$this->assertEquals(40, sizeof($items), 'Items must be on trash to be deleted');

		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id(),
			],
			[
				"value" 				=> null,
				"method" 				=> 'trash_items',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));


		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id(),
			],
			[
				"value" 				=> null,
				"method" 				=> 'delete_items',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));


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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

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

		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

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
		        'allow_insert' => 'yes',
				'status' => 'publish'
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
					'allow_new_terms' => 'no',
					'taxonomy_id' => $taxonomy2->get_id()
				],
				'multiple' => 'yes'
		    ),
		    true
		);

		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'test_new_value',
				"method" 				=> 'add_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $category2->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));


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

		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id(),
			],
			[
				"value" 				=> 'test_new_value',
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $category2->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

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

	function test_api_get_group() {

		$query = [
			'posts_per_page' => 22,
			'orderby' => 'title',
			'order' => 'ASC'
		];

		$args = [
			'query' => $query,
			'collection_id' => $this->collection->get_id()
		];

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[

			]
		);

		$bulk_id = $process->get_id();

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $bulk_id
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($process->get_id(), $data['id']);
		$this->assertEquals($process->get_options()['order'], $data['options']['order']);
		$this->assertEquals($process->get_options()['orderby'], $data['options']['orderby']);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/fefefe23232'
		);

		$response = $this->server->dispatch($request);

		$this->assertEquals(404, $response->get_status());

	}

	function test_set_multiple_tax_meta() {
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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> ['super', 'dooper'],
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->category->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));


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

		$items = $Tainacan_Items->fetch([
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'field' => 'name',
					'terms' => 'dooper'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(5, $items->found_posts);



	}

	function test_set_regular_multiple_meta() {
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

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> ['super', 'dooper'],
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->metadatum->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

		// single valued metadatum dont accept array
		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts, 'single valued metadatum dont accept array');

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'super dooper'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(0, $items->found_posts, 'single valued metadatum dont accept array');

		$process = $this->new_process(
			[
				'group_id' => $process->get_group_id()
			],
			[
				"value" 				=> ['super', 'dooper'],
				"method" 				=> 'set_value',
				"old_value"			=> null,
				"metadatum_id" 	=> $this->multiple_meta->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));


		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'super'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(5, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $this->multiple_meta->get_id(),
					'value' => 'dooper'
				]
			],
			'posts_per_page' => -1
		]);

		$this->assertEquals(5, $items->found_posts);

	}

	/**
	 * @group varied
	 */
	function test_set_coments() {
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

		// add test to 20 items
		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'open', //closed
				"method" 				=> 'set_comment_status',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));


		$query = [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'odd'
				]
			],
			'posts_per_page' => -1
		];

		// add test to 20 items
		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"value" 				=> 'closed',
				"method" 				=> 'set_comment_status',
				"old_value"			=> null,
				"metadatum_id" 	=> null,
			]
		);
		$this->assertIsInt($this->run_process($process));

		$items = $Tainacan_Items->fetch([
			'comment_status' => 'closed',
			'posts_per_page' => -1
		]);
		$this->assertEquals(20, $items->found_posts);

		$items = $Tainacan_Items->fetch([
			'comment_status' => 'open',
			'posts_per_page' => -1
		]);
		$this->assertEquals(20, $items->found_posts);
	}

	/**
	 * @group bulkedit-copy
	 */
	function test_copy_meta() {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$metadatum_copy = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadado_copy',
				'status' => 'publish',
				'collection' => $this->collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_owner = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadado_owner',
				'status' => 'publish',
				'collection' => $this->collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\User',
				'metadata_type_options' => [
					'default_author' => 'no'
				]
			),
			true
		);

		$query = [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => -1
		];

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"method"                  => 'copy_value',
				"metadatum_id_to"         => $metadatum_copy->get_id(),
				"metadatum_id_from"       => $this->metadatum->get_id(),
			]
		);
		$this->assertIsInt($this->run_process($process));

		$query = [
			'meta_query' => [
				[
					'key' => $metadatum_copy->get_id(),
					'value' => 'even'
				]
			],
			'posts_per_page' => -1
		];
		$items = $Tainacan_Items->fetch($query);
		$this->assertEquals(20, $items->found_posts);

		$process = $this->new_process(
			[
				'query' => $query,
				'collection_id' => $this->collection->get_id()
			],
			[
				"method"                  => 'copy_value',
				"metadatum_id_to"         => $metadatum_owner->get_id(),
				"metadatum_id_from"       => 'created_by',
			]
		);
		$this->assertIsInt($this->run_process($process));
		$query = [
			'meta_query' => [
				[
					'key' => $metadatum_owner->get_id(),
					'value' => get_current_user_id()
				]
			],
			'posts_per_page' => -1
		];
		$items = $Tainacan_Items->fetch($query);
		$this->assertEquals(20, $items->found_posts);


		$taxonomy_copy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'genero_copy',
				'description'  => 'tipos de musica',
				'allow_insert' => 'yes',
				'status' => 'publish'
			),
			true
		);
		$multiple_meta_copy = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'multiple_meta_copy',
				'status' => 'publish',
				'collection' => $this->collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy_copy->get_id()
				],
				'multiple' => 'yes'
			),
			true
		);
		$process = $this->new_process(
			[
				'items_ids' => $this->items_ids, // all
				'collection_id' => $this->collection->get_id()
			],
			[
				"method"                  => 'copy_value',
				"metadatum_id_to"         => $multiple_meta_copy->get_id(),
				"metadatum_id_from"       => $this->category->get_id()
			]
		);
		$this->assertIsInt($this->run_process($process));


		$query = [
			'taxquery' => [
				[
					'taxonomy' => $this->taxonomy->get_db_identifier(),
					'terms'    => 'good'
				]
				],
			'posts_per_page' => -1
		];
		$items = $Tainacan_Items->fetch($query);

		$query = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomy_copy->get_db_identifier(),
					'terms'    => 'good'
				]
				],
			'posts_per_page' => -1
		];
		$items_copy = $Tainacan_Items->fetch($query);
		$this->assertEquals($items_copy->found_posts, $items->found_posts);
	}

	private function create_copy_metadatum($type, $multiple = false, $attributes = []) {
		$options = array_merge([
			'name' => 'Copy ' . $type,
			'status' => 'publish',
			'collection' => $this->collection,
			'metadata_type' => 'Tainacan\\Metadata_Types\\' . $type,
			'multiple' => $multiple ? 'yes' : 'no',
			'required' => 'no',
		], $attributes);
		if (isset($attributes['collection_id'])) {
			unset($options['collection']);
		}
		return $this->tainacan_entity_factory->create_entity('metadatum', $options, true);
	}

	private function store_copy_value($item, $metadatum, $value) {
		$entry = new Entities\Item_Metadata_Entity($item, $metadatum);
		$entry->set_value($value);
		$this->assertTrue($entry->validate(), wp_json_encode($entry->get_errors()));
		return \Tainacan\Repositories\Item_Metadata::get_instance()->insert($entry);
	}

	private function copy_metadata_values($source, $destination, $item_ids = null) {
		$process = $this->new_process([
			'items_ids' => $item_ids ?? [$this->items_ids[0]],
			'collection_id' => $this->collection->get_id(),
		], [
			'method' => 'copy_value',
			'metadatum_id_from' => $source instanceof Entities\Metadatum ? $source->get_id() : $source,
			'metadatum_id_to' => $destination instanceof Entities\Metadatum ? $destination->get_id() : $destination,
		]);
		$this->assertIsInt($this->run_process($process));
		return $process;
	}

	private function read_copy_value($item_id, $metadatum) {
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($item_id);
		return (new Entities\Item_Metadata_Entity($item, $metadatum))->get_value();
	}

	/** @group bulkedit-copy */
	function test_copy_textarea_to_rich_text_formats_each_selected_item() {
		$source = $this->create_copy_metadatum('Textarea');
		$destination = $this->create_copy_metadatum('Rich_Text');
		$values = ["First paragraph\n\nhttps://example.org", "Second item\nAnother line", 'Outside group'];
		foreach ($values as $index => $value) {
			$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[$index]);
			$this->store_copy_value($item, $source, $value);
			$this->store_copy_value($item, $destination, '<p>Previous destination</p>');
		}
		$this->copy_metadata_values($source, $destination, array_slice($this->items_ids, 0, 2));
		foreach (array_slice($values, 0, 2) as $index => $value) {
			$this->assertSame(trim(wpautop(make_clickable($value))), $this->read_copy_value($this->items_ids[$index], $destination));
			$this->assertSame($value, $this->read_copy_value($this->items_ids[$index], $source));
		}
		$this->assertStringContainsString('<p>First paragraph</p>', $this->read_copy_value($this->items_ids[0], $destination));
		$this->assertStringContainsString('<a href="https://example.org"', $this->read_copy_value($this->items_ids[0], $destination));
		$this->assertSame('<p>Previous destination</p>', $this->read_copy_value($this->items_ids[2], $destination));
		$this->copy_metadata_values($source, $destination, array_slice($this->items_ids, 0, 2));
		$this->assertSame(trim(wpautop(make_clickable($values[0]))), $this->read_copy_value($this->items_ids[0], $destination));
	}
	/** @group bulkedit-copy */
	function test_copy_rich_text_to_textarea_preserves_safe_html_and_queries() {
		$source = $this->create_copy_metadatum('Rich_Text');
		$destination = $this->create_copy_metadatum('Textarea');
		$html = '<p><strong>First</strong> <a href="https://example.org">link</a></p><ul><li><em>Second</em></li></ul>';
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		$this->store_copy_value($item, $source, $html);
		$this->store_copy_value($item, $destination, 'Previous');
		foreach ([false, true] as $enabled) {
			update_option('tainacan_option_allow_rich_text_editor', $enabled);
			$this->copy_metadata_values($source, $destination);
			$this->assertSame($html, $this->read_copy_value($item->get_id(), $destination));
			$this->assertSame($html, $this->read_copy_value($item->get_id(), $source));
		}
		$request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $destination->get_id());
		$response = $this->server->dispatch($request);
		$this->assertSame(200, $response->get_status());
		$this->assertSame($html, $response->get_data()['value']);
		$request = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $this->collection->get_id() . '/items');
		$request->set_param('metaquery', [['key' => $destination->get_id(), 'value' => $html]]);
		$response = $this->server->dispatch($request);
		$this->assertSame(200, $response->get_status());
		$this->assertSame([$item->get_id()], array_column($response->get_data()['items'], 'id'));
	}

	/** @group bulkedit-copy */
	function test_copy_textarea_rich_text_adapts_single_and_multiple_values() {
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		foreach ([['Textarea', 'Rich_Text'], ['Rich_Text', 'Textarea']] as [$from, $to]) {
			foreach ([false, true] as $multiple) {
				$source = $this->create_copy_metadatum($from, $multiple);
				$destination = $this->create_copy_metadatum($to, true);
				$values = $from === 'Textarea' ? ['0', "Another\n\nhttps://example.org"] : ['<p>0</p>', '<p><a href="https://example.org">Second</a></p>'];
				$this->store_copy_value($item, $source, $multiple ? $values : $values[0]);
				$this->store_copy_value($item, $destination, ['Previous', 'Replaced']);
				$this->copy_metadata_values($source, $destination);
				$expected = array_slice($values, 0, $multiple ? 2 : 1);
				if ($from === 'Textarea') {
					$expected = array_map(static function($value) { return trim(wpautop(make_clickable($value))); }, $expected);
				}
				$this->assertSame($expected, $this->read_copy_value($item->get_id(), $destination));
				$this->assertSame($expected, get_post_meta($item->get_id(), $destination->get_id(), false));
				$this->assertSame($multiple ? $values : $values[0], $this->read_copy_value($item->get_id(), $source));
			}
		}
	}

	/** @group bulkedit-copy */
	function test_copy_rejects_multiple_to_single_and_other_types_without_writes() {
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		foreach ([['Textarea', 'Rich_Text', true], ['Rich_Text', 'Textarea', true], ['Text', 'Rich_Text', false], ['Core_Description', 'Rich_Text', false]] as [$from, $to, $multiple]) {
			$source = $from === 'Core_Description' ? $this->collection->get_core_description_metadatum() : $this->create_copy_metadatum($from, $multiple);
			$destination = $this->create_copy_metadatum($to);
			$this->store_copy_value($item, $source, $multiple ? ['One', 'Two'] : 'Source');
			$this->store_copy_value($item, $destination, 'Previous');
			$before = $this->read_copy_value($item->get_id(), $source);
			$process = $this->copy_metadata_values($source, $destination);
			$this->assertNotEmpty($process->get_error_log());
			$this->assertSame('Previous', $this->read_copy_value($item->get_id(), $destination));
			$this->assertSame($before, $this->read_copy_value($item->get_id(), $source));
		}
	}

	/** @group bulkedit-copy */
	function test_copy_rejects_missing_ids_and_self_copy_without_fatal_errors() {
		$source = $this->create_copy_metadatum('Textarea');
		$destination = $this->create_copy_metadatum('Rich_Text');
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		$this->store_copy_value($item, $source, 'Source');
		$this->store_copy_value($item, $destination, 'Previous');
		foreach ([[999999, $destination], [$source, 999999], [$source, $source]] as [$from, $to]) {
			$process = $this->copy_metadata_values($from, $to);
			$this->assertNotEmpty($process->get_error_log());
			$this->assertSame('Previous', $this->read_copy_value($item->get_id(), $destination));
			$this->assertSame('Source', $this->read_copy_value($item->get_id(), $source));
		}
	}

	/** @group bulkedit-copy */
	function test_copy_rejects_foreign_collection_and_compound_children() {
		$foreign = $this->tainacan_entity_factory->create_entity('collection', ['name' => 'Foreign', 'status' => 'publish'], true);
		$source = $this->create_copy_metadatum('Textarea');
		$destination = $this->create_copy_metadatum('Rich_Text');
		$foreign_source = $this->create_copy_metadatum('Textarea', false, ['collection' => $foreign]);
		$foreign_destination = $this->create_copy_metadatum('Rich_Text', false, ['collection' => $foreign]);
		$compound = $this->create_copy_metadatum('Compound', true);
		$child_source = $this->create_copy_metadatum('Textarea', false, ['parent' => $compound->get_id()]);
		$child_destination = $this->create_copy_metadatum('Rich_Text', false, ['parent' => $compound->get_id()]);
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		$this->store_copy_value($item, $source, 'Source');
		$this->store_copy_value($item, $destination, 'Previous');
		foreach ([[$foreign_source, $destination], [$source, $foreign_destination], [$child_source, $destination], [$source, $child_destination]] as [$from, $to]) {
			$before = get_post_meta($item->get_id(), $to->get_id(), false);
			$process = $this->copy_metadata_values($from, $to);
			$this->assertNotEmpty($process->get_error_log());
			$this->assertSame($before, get_post_meta($item->get_id(), $to->get_id(), false));
			$this->assertSame('Previous', $this->read_copy_value($item->get_id(), $destination));
		}
	}

	/** @group bulkedit-copy */
	function test_copy_accepts_repository_and_parent_collection_metadata() {
		$parent = $this->tainacan_entity_factory->create_entity('collection', ['name' => 'Parent', 'status' => 'publish'], true);
		$this->collection->set_parent($parent->get_id());
		\Tainacan\Repositories\Collections::get_instance()->update($this->collection);
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		foreach ([$parent, null] as $collection) {
			$source = $this->create_copy_metadatum('Textarea', false, $collection ? ['collection' => $collection] : ['collection_id' => 'default']);
			$destination = $this->create_copy_metadatum('Rich_Text');
			$this->store_copy_value($item, $source, 'Inherited');
			$this->copy_metadata_values($source, $destination);
			$this->assertSame('<p>Inherited</p>', $this->read_copy_value($item->get_id(), $destination));
		}
	}

	/** @group bulkedit-copy */
	function test_copy_empty_values_validates_destination_and_preserves_zero() {
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		foreach ([['Textarea', 'Rich_Text'], ['Rich_Text', 'Textarea']] as [$from, $to]) {
			foreach ([false, true] as $multiple) {
				$source = $this->create_copy_metadatum($from, $multiple);
				$optional = $this->create_copy_metadatum($to, $multiple);
				$required = $this->create_copy_metadatum($to, $multiple, ['required' => 'yes']);
				foreach ([$optional, $required] as $destination) {
					$this->store_copy_value($item, $destination, $multiple ? ['Previous'] : 'Previous');
					$process = $this->copy_metadata_values($source, $destination);
					if ($destination->is_required()) {
						$this->assertNotEmpty($process->get_error_log());
						$this->assertSame($multiple ? ['Previous'] : 'Previous', $this->read_copy_value($item->get_id(), $destination));
					} else {
						$this->assertFalse(metadata_exists('post', $item->get_id(), $destination->get_id()));
					}
				}
			}
		}
		$source = $this->create_copy_metadatum('Textarea');
		$destination = $this->create_copy_metadatum('Rich_Text');
		foreach (['0' => '<p>0</p>', '   ' => '', '<p><br></p>' => ''] as $input => $expected) {
			$this->store_copy_value($item, $source, (string) $input);
			$this->copy_metadata_values($source, $destination);
			$this->assertSame($expected, $this->read_copy_value($item->get_id(), $destination));
		}
	}

	/** @group bulkedit-copy */
	function test_copy_safe_links_and_unsafe_historical_values_use_destination_sanitization() {
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		foreach ([['Textarea', 'Rich_Text'], ['Rich_Text', 'Textarea']] as [$from, $to]) {
			$source = $this->create_copy_metadatum($from);
			$destination = $this->create_copy_metadatum($to);
			// Explicit historical fixture bypasses write sanitization to exercise destination protection.
			$value = '<p onclick="alert(1)">Safe <a href="https://example.org">existing</a></p><script>alert(1)</script><a href="javascript:alert(1)">Bad</a>' . "\n\nhttps://example.net contact@example.org";
			update_post_meta($item->get_id(), $source->get_id(), $value);
			$this->copy_metadata_values($source, $destination);
			$saved = $this->read_copy_value($item->get_id(), $destination);
			$this->assertStringNotContainsString('onclick', $saved);
			$this->assertStringNotContainsString('<script', $saved);
			$this->assertStringNotContainsString('javascript:', $saved);
			$this->assertStringContainsString('<a href="https://example.org">existing</a>', $saved);
			$this->assertSame($value, $this->read_copy_value($item->get_id(), $source));
			if ($from === 'Textarea') {
				$this->assertSame(1, substr_count($saved, 'href="https://example.org"'));
				$this->assertStringContainsString('href="https://example.net"', $saved);
				$this->assertStringContainsString('href="mailto:contact@example.org"', $saved);
			} else {
				$this->assertStringNotContainsString('href="https://example.net"', $saved);
			}
		}
	}

	/** @group bulkedit-copy */
	function test_copy_same_textarea_and_rich_text_types_does_not_format() {
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		foreach (['Textarea', 'Rich_Text'] as $type) {
			$source = $this->create_copy_metadatum($type);
			$destination = $this->create_copy_metadatum($type);
			$value = "<p>Existing</p>\nhttps://example.org";
			$this->store_copy_value($item, $source, $value);
			$this->copy_metadata_values($source, $destination);
			$this->assertSame($value, $this->read_copy_value($item->get_id(), $destination));
		}
	}

	/**
	 * @group bulkedit-copy
	 * @group api
	 */
	function test_rest_copy_dispatch_executes_the_queued_worker_payload() {
		global $wpdb;
		$source = $this->create_copy_metadatum('Textarea');
		$destination = $this->create_copy_metadatum('Rich_Text');
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		$this->store_copy_value($item, $source, "REST\n\nhttps://example.org");
		$request = new \WP_REST_Request('POST', $this->api_baseroute);
		$request->set_body(wp_json_encode(['items_ids' => [$item->get_id()]]));
		$response = $this->server->dispatch($request);
		$this->assertSame(200, $response->get_status());
		$group_id = $response->get_data()['id'];
		$request = new \WP_REST_Request('POST', $this->api_baseroute . '/' . $group_id . '/copy_value');
		$request->set_body(wp_json_encode(['metadatum_id_from' => $source->get_id(), 'metadatum_id_to' => $destination->get_id()]));
		// Only intercept the asynchronous transport; REST dispatch and queue persistence are real.
		$transport = static function() { return ['headers' => [], 'body' => '', 'response' => ['code' => 200, 'message' => 'OK']]; };
		add_filter('pre_http_request', $transport);
		try {
			$response = $this->server->dispatch($request);
		} finally {
			remove_filter('pre_http_request', $transport);
		}
		$this->assertSame(200, $response->get_status());
		$this->assertSame('', $this->read_copy_value($item->get_id(), $destination), 'Enqueueing is not completion');
		$table = $wpdb->prefix . 'tnc_bg_process';
		$row = $wpdb->get_row("SELECT * FROM $table WHERE action = 'generic_process' ORDER BY ID DESC LIMIT 1");
		$this->assertNotNull($row);
		$batch = (object) ['key' => $row->ID, 'data' => maybe_unserialize($row->data)];
		$this->assertSame($source->get_id(), $batch->data['bulk_edit_data']['metadatum_id_from']);
		$this->assertSame($destination->get_id(), $batch->data['bulk_edit_data']['metadatum_id_to']);
		$worker = new \Tainacan\Background_Generic_Process();
		try {
			for ($steps = 0; $steps < 100; $steps++) {
				$result = $worker->task($batch);
				if ($result === false) break;
				$batch = $result;
			}
			$this->assertLessThan(100, $steps);
			$this->assertSame(trim(wpautop(make_clickable("REST\n\nhttps://example.org"))), $this->read_copy_value($item->get_id(), $destination));
		} finally {
			$worker->delete($row->ID);
		}
	}

	/**
	 * @group bulkedit-copy
	 * @group api
	 */
	function test_copy_does_not_edit_items_without_permission() {
		$source = $this->create_copy_metadatum('Textarea');
		$destination = $this->create_copy_metadatum('Rich_Text');
		$item = \Tainacan\Repositories\Items::get_instance()->fetch($this->items_ids[0]);
		$this->store_copy_value($item, $source, 'Source');
		$this->store_copy_value($item, $destination, 'Previous');
		$user_id = $this->factory()->user->create(['role' => 'subscriber']);
		wp_set_current_user($user_id);
		$request = new \WP_REST_Request('POST', $this->api_baseroute);
		$request->set_body(wp_json_encode(['items_ids' => [$item->get_id()]]));
		$response = $this->server->dispatch($request);
		$this->assertSame(403, $response->get_status());
		$process = $this->copy_metadata_values($source, $destination);
		$this->assertNotEmpty($process->get_error_log());
		$this->assertSame('Previous', $this->read_copy_value($item->get_id(), $destination));
		$this->assertSame('Source', $this->read_copy_value($item->get_id(), $source));
	}

}
