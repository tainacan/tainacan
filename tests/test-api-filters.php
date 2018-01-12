<?php

namespace Tainacan\Tests;

class TAINACAN_REST_Terms_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_filter(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Collection filtered',
				'description' => 'Is filtered'
			),
			true
		);

		$metadata = $this->tainacan_entity_factory->create_entity(
			'metadata',
			array(
				'name'        => 'Metadata filtered',
				'description' => 'Is filtered'
			)
		);

		$request_body = json_encode(
			array(
				'collection_id' => $collection->get_id(),
				'metadata_id'   => $metadata->get_id(),
				'filter_type'   => 'range',
				'filter'        => [
					'name'        => 'Filter name',
					'description' => 'This is RANGE!',
				]
			)
		);

		$request = new \WP_REST_Request('POST', $this->namespace . '/filters');

		$request->set_body($request_body);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Tainacan\Filter_Types\Range', $data['filter_type']);
		$this->assertEquals('Filter name', $data['name']);
	}

	public function test_delete_or_trash_filter(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Collection filtered',
				'description' => 'Is filtered'
			),
			true
		);

		$metadata = $this->tainacan_entity_factory->create_entity(
			'metadata',
			array(
				'name'        => 'Metadata filtered',
				'description' => 'Is filtered'
			)
		);

		$filter_type = $this->tainacan_filter_factory->create_filter('range');

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'metadata'    => $metadata,
				'filter_type' => $filter_type
			),
			true
		);

		$is_permanently = json_encode([
			'is_permanently' => true
		]);

		$request = new \WP_REST_Request(
			'DELETE', $this->namespace . '/filters/' . $filter->get_id());

		$request->set_body($is_permanently);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->markTestIncomplete('Incomplete');

		$this->assertEquals('filtro', $data['name']);

		$filter_status = get_post($filter->get_id())->post_status;

		$this->assertEquals('trash', $filter_status);

		##### TRASH #####

		$is_permanently = json_encode([
			'is_permanently' => false
		]);

		$request = new \WP_REST_Request(
			'DELETE', $this->namespace . '/filters/' . $filter->get_id());

		$request->set_body($is_permanently);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('filtro', $data['name']);
		$this->assertNull(get_post($filter->get_id()));
	}

}

?>