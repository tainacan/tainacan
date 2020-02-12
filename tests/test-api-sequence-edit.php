<?php

namespace Tainacan\Tests;

use Tainacan\Entities;

/**
 * Sample test case.
 */
class SequenceEdit extends TAINACAN_UnitApiTestCase {

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
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $collection->get_core_title_metadatum(), $title);

		}

		$this->api_baseroute = $this->namespace . '/collection/' . $collection->get_id() . '/sequence-edit';

	}

	function test_setup() {
		$this->assertEquals(40, sizeof($this->items_ids));
	}

	function test_init_by_query() {


		$query = [
			'metaquery' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'orderby' => 'title'
		];

		$request = new \WP_REST_Request(
			'POST', $this->api_baseroute
		);

		$request->set_body( json_encode(['use_query' => $query]) );

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertEquals(20, $data['items_count']);

		$group_id = $data['id'];

		$items = \Tainacan\Repositories\Items::get_instance()->fetch( [
			'meta_query' => [
				[
					'key' => $this->metadatum->get_id(),
					'value' => 'even'
				]
			],
			'nopaging' => 1,
			'orderby' => 'post_title'
		], $this->collection->get_id(), 'OBJECT');

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/1'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(200, $response->get_status());
		$this->assertEquals($items[0]->get_id(), $data);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/7'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(200, $response->get_status());
		$this->assertEquals($items[6]->get_id(), $data);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/20'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(200, $response->get_status());
		$this->assertEquals($items[19]->get_id(), $data);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/30'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(404, $response->get_status());


	}

	function test_init_by_ids() {

		$ids = array_slice($this->items_ids, 2, 7);

		$request = new \WP_REST_Request(
			'POST', $this->api_baseroute
		);

		$request->set_body( json_encode(['items_ids' => $ids]) );

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertEquals(7, $data['items_count']);
		$group_id = $data['id'];

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/1'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(200, $response->get_status());
		$this->assertEquals($ids[0], $data);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/3'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(200, $response->get_status());
		$this->assertEquals($ids[2], $data);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/6'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(200, $response->get_status());
		$this->assertEquals($ids[5], $data);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/7'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(200, $response->get_status());
		$this->assertEquals($ids[6], $data);

		$request = new \WP_REST_Request(
			'GET', $this->api_baseroute . '/' . $group_id . '/8'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(404, $response->get_status());


	}



}
