<?php

namespace Tainacan\Tests;

/**
 * @group api 
 */
class TAINACAN_REST_Terms_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_filter(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Collection filtered',
				'description' => 'Is filtered'
			),
			true,
			true
		);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'Metadata filtered',
				'description'   => 'Is filtered',
				'collection_id' => $collection->get_id(),
				'field_type'    => 'Tainacan\Field_Types\Numeric',
			),
			true,
			true
		);

		$request_body = json_encode(
			array(
				'filter_type'   => 'custom_interval',
				'filter'        => [
					'name'        => 'Filter name',
					'description' => 'This is CUSTOM INTERVAL!',
				]
			)
		);

		$request = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $collection->get_id() . '/field/' . $field->get_id(). '/filters');

		$request->set_body($request_body);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
		$this->assertTrue(is_array($data) && array_key_exists('filter_type', $data), sprintf('cannot create a custom interval, response: %s', print_r($data, true)));
		$this->assertEquals('Tainacan\Filter_Types\Custom_Interval', $data['filter_type']);
		$this->assertEquals('Filter name', $data['name']);
	}

	public function test_delete_or_trash_filter(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Collection filtered',
				'description' => 'Is filtered',
			),
			true
		);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Field filtered',
				'description' => 'Is filtered',
			)
		);

		$filter_type = $this->tainacan_filter_factory->create_filter('custom_interval');

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'field'    => $field,
				'filter_type' => $filter_type,
			),
			true
		);

		$is_permanently = json_encode([
			'is_permanently' => false
		]);

		$request = new \WP_REST_Request(
			'DELETE', $this->namespace . '/filters/' . $filter->get_id());

		$request->set_body($is_permanently);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('filtro', $data['name']);

		$filter_status = get_post($filter->get_id())->post_status;

		$this->assertEquals('trash', $filter_status);

		##### DELETE #####

		$is_permanently = json_encode([
			'is_permanently' => true
		]);

		$request = new \WP_REST_Request(
			'DELETE', $this->namespace . '/filters/' . $filter->get_id());

		$request->set_body($is_permanently);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('filtro', $data['name']);
		$this->assertNull(get_post($filter->get_id()));
	}

	public function test_update_filter(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Collection filtered',
				'description' => 'Is filtered',
			),
			true
		);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Field filtered',
				'description' => 'Is filtered',
				'collection_id' => $collection->get_id()
			)
		);

		$filter_type = $this->tainacan_filter_factory->create_filter('custom_interval');

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'field'    => $field,
				'filter_type' => $filter_type,
			),
			true
		);

		$new_attributes = json_encode([
			'name' => 'Faceta',
		]);

		$request = new \WP_REST_Request(
			'PATCH', $this->namespace . '/filters/' . $filter->get_id()
		);

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertNotEquals('filtro', $data['name']);
		$this->assertEquals('Faceta', $data['name']);
	}

	public function test_fetch_filters(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Collection filtered',
				'description' => 'Is filtered',
			),
			true
		);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'Field filtered',
				'description'   => 'Is filtered',
				'collection_id' => $collection->get_id(),
				'field_type'    => 'Tainacan\Field_Types\Numeric'
			),
			true
		);

		$field2 = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'Other filtered',
				'description'   => 'Is filtered',
				'collection_id' => $collection->get_id(),
				'field_type'    => 'Tainacan\Field_Types\Numeric'
			),
			true
		);

		$filter_type = $this->tainacan_filter_factory->create_filter('custom_interval');

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'field'       => $field,
				'filter_type' => $filter_type,
				'status'      => 'publish'
			),
			true
		);

		$filter2 = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro2',
				'collection'  => $collection,
				'description' => 'descricao',
				'field'       => $field2,
				'filter_type' => $filter_type,
				'status'      => 'publish'
			),
			true
		);

		$request = new \WP_REST_Request('GET', $this->namespace . '/collection/'.  $collection->get_id() .'/filters');

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$first_filter = $data[0];
		$second_filter = $data[1];

		$this->assertEquals($filter->get_name(), $first_filter['name']);
		$this->assertEquals($filter2->get_name(), $second_filter['name']);

		#### FETCH A FILTER ####

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/filters/' . $filter->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('filtro', $data['name']);
	}

	public function test_create_and_fetch_filter_in_repository(){
		$filter_attr = json_encode([
			'filter_type' => 'autocomplete',
			'filter'      => [
				'name'        => '2x Filter',
				'description' => 'Description of 2x Filter',
				'status'      => 'publish'
			]
		]);

		$filter_attr2 = json_encode([
			'filter_type' => 'autocomplete',
			'filter'      => [
				'name'        => '4x Filter',
				'description' => 'Description of 4x Filter',
				'status'      => 'publish'
			]
		]);

		#### CREATE A FILTER IN REPOSITORY ####

		$request_create = new \WP_REST_Request('POST', $this->namespace . '/filters');
		$request_create->set_body($filter_attr);

		$response_create = $this->server->dispatch($request_create);

		$data = $response_create->get_data();

		$this->assertEquals('filter_in_repository', $data['collection_id']);


		#### CREATE A FILTER IN COLLECTION WITHOUT FIELD ASSOCIATION ####

		$collection = $this->tainacan_entity_factory->create_entity('collection', [], true);

		$request_create2 = new \WP_REST_Request('POST', $this->namespace . '/collection/'. $collection->get_id() .'/filters');
		$request_create2->set_body($filter_attr2);

		$response_create2 = $this->server->dispatch($request_create2);

		$data2 = $response_create2->get_data();

		$this->assertEquals($collection->get_id(), $data2['collection_id']);

		#### GET A FILTER FROM A REPOSITORY CONTEXT ####

		$request_get1 = new \WP_REST_Request('GET', $this->namespace . '/filters');

		$response_get1 = $this->server->dispatch($request_get1);

		$data3 = $response_get1->get_data();

		$this->assertCount(1, $data3);
		$this->assertEquals('2x Filter', $data3[0]['name']);

		#### GET A FILTER FROM A COLLECTION CONTEXT ####

		$request_get2 = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/filters');

		$response_get2 = $this->server->dispatch($request_get2);

		$data4 = $response_get2->get_data();

		$this->assertCount(1, $data4);
		$this->assertEquals('4x Filter', $data4[0]['name']);
	}
}

?>