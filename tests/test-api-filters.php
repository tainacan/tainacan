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
				'filter'        => [
					'name'        => 'Filter name',
					'description' => 'This is RANGE!',
					'filter_type' => 'range'
				]
			)
		);

		$request = new \WP_REST_Request('POST', $this->namespace . '/filters');

		$request->set_body($request_body);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('range', $data['filter_type']);
		$this->assertEquals('Filter name', $data['name']);
	}

}

?>