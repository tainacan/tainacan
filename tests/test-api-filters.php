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

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadata filtered',
				'description'   => 'Is filtered',
				'collection_id' => $collection->get_id(),
				'metadata_type'    => 'Tainacan\Metadata_Types\Numeric',
				'status'      => 'publish'
			),
			true,
			true
		);

		$request_body = json_encode(
			array(
				'filter_type'   => 'Tainacan\Filter_Types\Numeric_Interval',
				'filter'        => [
					'name'        => 'Filter name',
					'description' => 'This is CUSTOM INTERVAL!',
				]
			)
		);

		$request = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $collection->get_id() . '/metadatum/' . $metadatum->get_id(). '/filters');

		$request->set_body($request_body);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
		$this->assertTrue(is_array($data) && array_key_exists('filter_type', $data), sprintf('cannot create a custom interval, response: %s', print_r($data, true)));
		$this->assertEquals('Tainacan\Filter_Types\Numeric_Interval', $data['filter_type']);
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

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Metadatum filtered',
				'description' => 'Is filtered',
				'collection_id' => $collection->get_id(),
				'metadata_type'    => 'Tainacan\Metadata_Types\Numeric',
				'status'      => 'publish'
			),
			true
		);

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'metadatum_id'    => $metadatum->get_id(),
				'filter_type' => 'Tainacan\Filter_Types\Numeric_Interval',
			),
			true
		);

		$permanently = [ 'permanently' => false ];

		$request = new \WP_REST_Request(
			'DELETE', $this->namespace . '/filters/' . $filter->get_id());

		$request->set_query_params($permanently);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('filtro', $data['name']);

		$filter_status = get_post($filter->get_id())->post_status;

		$this->assertEquals('trash', $filter_status);

		##### DELETE #####

		$permanently = [ 'permanently' => true ];

		$request = new \WP_REST_Request(
			'DELETE', $this->namespace . '/filters/' . $filter->get_id());

		$request->set_query_params($permanently);

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

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Metadatum filtered',
				'description' => 'Is filtered',
				'status' => 'publish',
				'collection_id' => $collection->get_id(),
				'metadata_type'    => 'Tainacan\Metadata_Types\Numeric',
			),
			true
		);

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'metadatum_id'    => $metadatum->get_id(),
				'filter_type' => 'Tainacan\Filter_Types\Numeric_Interval',
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

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadatum filtered',
				'description'   => 'Is filtered',
				'status' => 'publish',
				'collection_id' => $collection->get_id(),
				'metadata_type'    => 'Tainacan\Metadata_Types\Numeric'
			),
			true
		);

		$metadatum2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Other filtered',
				'description'   => 'Is filtered',
				'collection_id' => $collection->get_id(),
				'metadata_type'    => 'Tainacan\Metadata_Types\Numeric',
				'status'      => 'publish'
			),
			true
		);

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'metadatum'       => $metadatum,
				'filter_type' => 'Tainacan\Filter_Types\Numeric_Interval',
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
				'metadatum'       => $metadatum2,
				'filter_type' => 'Tainacan\Filter_Types\Numeric_Interval',
				'status'      => 'publish'
			),
			true
		);

		$request = new \WP_REST_Request('GET', $this->namespace . '/collection/'.  $collection->get_id() .'/filters');

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$first_filter = $data[0];
		$second_filter = $data[1];

		$names = [$first_filter['name'], $second_filter['name']];

		$this->assertContains($filter->get_name(), $names);
		$this->assertContains($filter2->get_name(), $names);

		#### FETCH A FILTER ####

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/filters/' . $filter->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('filtro', $data['name']);
	}

	public function test_create_and_fetch_filter_in_repository(){

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Collection filtered',
				'description' => 'Is filtered',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadatum filtered',
				'description'   => 'Is filtered',
				'collection_id' => $collection->get_id(),
				'metadata_type'    => 'Tainacan\Metadata_Types\Text',
				'status'      => 'publish'
			),
			true
		);

		$metadatum2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadatum filtered',
				'description'   => 'Is filtered',
				'collection_id' => 'default',
				'metadata_type'    => 'Tainacan\Metadata_Types\Text',
				'status'      => 'publish'
			),
			true
		);

		$filter_attr = json_encode([
			'filter_type' => '\Tainacan\Filter_Types\Autocomplete',
			'filter'      => [
				'name'        => '2x Filter',
				'description' => 'Description of 2x Filter',
				'status'      => 'publish'
			],
			'metadatum_id'       => $metadatum2->get_id()
		]);

		$filter_attr2 = json_encode([
			'filter_type' => '\Tainacan\Filter_Types\Autocomplete',
			'filter'      => [
				'name'        => '4x Filter',
				'description' => 'Description of 4x Filter',
				'status'      => 'publish'
			],
			'metadatum_id'       => $metadatum->get_id()
		]);

		#### CREATE A FILTER IN REPOSITORY ####

		$request_create = new \WP_REST_Request('POST', $this->namespace . '/filters');
		$request_create->set_body($filter_attr);

		$response_create = $this->server->dispatch($request_create);

		$data = $response_create->get_data();

		$this->assertEquals('default', $data['collection_id']);


		#### CREATE A FILTER IN COLLECTION WITHOUT METADATUM ASSOCIATION ####

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

		$this->assertCount(2, $data4);
		//$this->assertEquals('4x Filter', $data4[0]['name']);
	}

	public function test_return_filter_type_options_in_get_item() {

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'number',
				'status' => 'publish',
				'collection' => $collection1,
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
			),
			true
		);

		$filter_numeric = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'numeric',
				'status' => 'publish',
				'collection' => $collection1,
				'metadatum' => $meta,
				'filter_type'  => 'Tainacan\Filter_Types\Numeric_Interval',
				'filter_type_options' => [
					'step' => 3,
				]
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/filters/' . $filter_numeric->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($filter_numeric->get_id(), $data['id']);
		$this->assertEquals('numeric', $data['name']);
		$this->assertEquals(3, $data['filter_type_options']['step']);

	}

	public function test_return_filter_type_options_in_get_items() {

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'number',
				'status' => 'publish',
				'collection' => $collection1,
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
			),
			true
		);

		$filter_numeric = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'numeric',
				'status' => 'publish',
				'collection' => $collection1,
				'metadatum' => $meta,
				'filter_type'  => 'Tainacan\Filter_Types\Numeric_Interval',
				'filter_type_options' => [
					'step' => 3,
				]
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection1->get_id() . '/filters'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		//var_dump($data, $this->namespace . '/collection/' . $collection2->get_id() . '/metadata/');
		foreach ($data as $d) {
			if ($d['id'] == $filter_numeric->get_id()) {
				$meta = $d;
				break;
			}
		}

		$this->assertEquals($filter_numeric->get_id(), $meta['id']);
		$this->assertEquals('numeric', $meta['name']);
		$this->assertEquals(3, $meta['filter_type_options']['step']);

	}

	public function test_return_filter_type_options_in_get_item_default_value() {

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'number',
				'status' => 'publish',
				'collection' => $collection1,
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
			),
			true
		);

		$filter_numeric = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'numeric',
				'status' => 'publish',
				'collection' => $collection1,
				'metadatum' => $meta,
				'filter_type'  => 'Tainacan\Filter_Types\Numeric_Interval',
				// 'filter_type_options' => [
				// 	'step' => 3,
				// ]
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/filters/' . $filter_numeric->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($filter_numeric->get_id(), $data['id']);
		$this->assertEquals('numeric', $data['name']);
		$this->assertEquals(1, $data['filter_type_object']['options']['step']);
		$this->assertEquals(1, $data['filter_type_options']['step']);

	}

	public function test_return_metadata_type_options_inside_metadatum_property() {

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$core1 = $collection1->get_core_title_metadatum();

		$meta_relationship = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'relationship',
				'status' => 'publish',
				'collection' => $collection2,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'metadata_type_options' => [
					'repeated' => 'yes',
					'collection_id' => $collection1->get_id(),
					'search' => $core1->get_id()
				]
			),
			true
		);

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'test',
				'status' => 'publish',
				'collection' => $collection2,
				'metadatum' => $meta_relationship,
				'filter_type'  => 'Tainacan\Filter_Types\Autocomplete',
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/filters/' . $filter->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
		$this->assertEquals($filter->get_id(), $data['id']);
		$this->assertEquals('test', $data['name']);
		$this->assertEquals('yes', $data['metadatum']['metadata_type_object']['options']['repeated']);
		$this->assertEquals($collection1->get_id(), $data['metadatum']['metadata_type_object']['options']['collection_id']);
		$this->assertEquals($core1->get_id(), $data['metadatum']['metadata_type_object']['options']['search']);

	}

	public function test_visibility_the_filter_from_in_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'private',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$filterA = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'test',
				'status' => 'publish',
				'collection' => $collection,
				'metadatum' => $metadatumA,
				'filter_type'  => 'Tainacan\Filter_Types\Autocomplete',
			),
			true
		);

		$filterB = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'test',
				'status' => 'private',
				'collection' => $collection,
				'metadatum' => $metadatumA,
				'filter_type'  => 'Tainacan\Filter_Types\Autocomplete',
			),
			true
		);

		wp_logout();
		wp_set_current_user(0);

		$requestA = new \WP_REST_Request('GET', $this->namespace . '/filters/' . $filterA->get_id());
		$requestB = new \WP_REST_Request('GET', $this->namespace . '/filters/' . $filterB->get_id());

		$response = $this->server->dispatch($requestA);
		$status = $response->status;
		$this->assertEquals(200, $status);

		$response = $this->server->dispatch($requestB);
		$status = $response->status;
		$this->assertEquals(401, $status);
	}

	public function test_private_filter_ids_not_in_filters_list(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement',
				'status'      => 'publish',
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'private',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$filterA = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'test',
				'status' => 'publish',
				'collection' => $collection,
				'metadatum' => $metadatumA,
				'filter_type'  => 'Tainacan\Filter_Types\Autocomplete',
			),
			true
		);

		$filterB = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'test',
				'status' => 'private',
				'collection' => $collection,
				'metadatum' => $metadatumA,
				'filter_type'  => 'Tainacan\Filter_Types\Autocomplete',
			),
			true
		);

		wp_logout();
		wp_set_current_user(0);

		$requestA = new \WP_REST_Request('GET', $this->namespace . '/filters/' . $filterA->get_id());
		$requestB = new \WP_REST_Request('GET', $this->namespace . '/filters/' . $filterB->get_id());
		$requestC = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/filters');

		$response = $this->server->dispatch($requestA);
		$status = $response->status;
		$this->assertEquals(200, $status);

		$response = $this->server->dispatch($requestB);
		$status = $response->status;
		$this->assertEquals(401, $status);

		$response = $this->server->dispatch($requestC);
		$data = $response->get_data();
		$this->assertEquals(1, count($data));
		$this->assertEquals($filterA->get_id(), $data[0]['id']);

	}

	public function test_private_filter_ids_not_in_filter_order(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement',
				'status'      => 'publish',
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'private',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$filterA = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'test',
				'status' => 'publish',
				'collection' => $collection,
				'metadatum' => $metadatumA,
				'filter_type'  => 'Tainacan\Filter_Types\Autocomplete',
			),
			true
		);

		$filterB = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'   => 'test',
				'status' => 'private',
				'collection' => $collection,
				'metadatum' => $metadatumA,
				'filter_type'  => 'Tainacan\Filter_Types\Autocomplete',
			),
			true
		);

		$order = array();

		$filters = $collection->get_filters();

		foreach ( $filters as $f ) {
			$order[] = [
				'id' => $f->get_id(),
				'enabled' => true,
			];
		}

		$collection->set_filters_order($order);
		$collection->validate();
		\tainacan_collections()->insert($collection);

		$request = new \WP_REST_Request('GET', $this->namespace . '/collections/' . $collection->get_id());

		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertEquals(2, count($data['filters_order']));

		wp_logout();
		wp_set_current_user(0);

		$request = new \WP_REST_Request('GET', $this->namespace . '/collections/' . $collection->get_id());

		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertEquals(1, count($data['filters_order']));
		$this->assertNotEquals($filterB->get_id(), $data['filters_order'][0]['id']);

	}

}

?>
