<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Compound_Metadata_Controller extends TAINACAN_UnitApiTestCase {

	function setUp() {
		parent::setUp();

		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection', 
			array(
				'name'   => 'collection-1', 
				'status' => 'publish'
			),
			true
		);

		$this->item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item-1',
				'description' => 'description-1',
				'collection'  => $this->collection,
				'status' => 'publish'
			),
			true
		);

		// $this->item2 = $this->tainacan_entity_factory->create_entity(
		// 	'item',
		// 	array(
		// 		'title'       => 'item-2',
		// 		'description' => 'description-2',
		// 		'collection'  => $this->collection,
		// 		'status' => 'publish'
		// 	),
		// 	true
		// );

	}

	/**
	 * @group compound_metadatum
	 */
	public function test_create_compound_metadatum_API() {

		$metadatum_compound = json_encode(
			array(
				'name'        => 'quadrados',
				'description' => 'Descrição de um quadrado.',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Compound',
			)
		);

		$URL = $this->namespace . '/collection/' . $this->collection->get_id() . '/metadata';
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_compound);
		$response = $this->server->dispatch($request);

		$metadatum_compound_added = $response->get_data();
		$this->assertTrue(is_array($metadatum_compound_added) && array_key_exists('name', $metadatum_compound_added), sprintf('cannot create metadatum, response: %s', print_r($metadatum_compound_added, true)));
		$this->assertEquals('quadrados', $metadatum_compound_added['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_compound_added['collection_id']);

		$metadatum_id = json_encode(
			array(
				'name'        => 'id',
				'description' => 'id',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent'  => $metadatum_compound_added['id'],
				'collection_key' => 'yes',
				'required'  => 'yes'
			)
		);

		$metadatum_largura = json_encode(
			array(
				'name'        => 'largura',
				'description' => 'largura',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent' 	 => $metadatum_compound_added['id']
			)
		);

		$metadatum_altura = json_encode(
			array(
				'name'        => 'altura',
				'description' => 'altura',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent' 	 => $metadatum_compound_added['id']
			)
		);

		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_largura);
		$response = $this->server->dispatch($request);
		$metadatum_largura = $response->get_data();

		$this->assertTrue(is_array($metadatum_largura) && array_key_exists('name', $metadatum_largura), sprintf('cannot create metadatum, response: %s', print_r($metadatum_largura, true)));
		$this->assertEquals('largura', $metadatum_largura['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_largura['collection_id']);

		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_altura);
		$response = $this->server->dispatch($request);
		$metadatum_altura = $response->get_data();

		$this->assertTrue(is_array($metadatum_altura) && array_key_exists('name', $metadatum_altura), sprintf('cannot create metadatum, response: %s', print_r($metadatum_altura, true)));
		$this->assertEquals('altura', $metadatum_altura['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_altura['collection_id']);

		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_id);
		$response = $this->server->dispatch($request);
		$metadatum_id = $response->get_data();

		$this->assertTrue(is_array($metadatum_id) && array_key_exists('name', $metadatum_id), sprintf('cannot create metadatum, response: %s', print_r($metadatum_id, true)));
		$this->assertEquals('id', $metadatum_id['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_id['collection_id']);


		$insert_item_json = json_encode(
			array(
				'collection_id' => $this->collection->get_id(),
				'status' => 'auto-draft',
				'comment_status' => 'closed'
			)
		);

		$URL = $this->namespace . '/collection/' . $this->collection->get_id() . '/items';
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($insert_item_json);
		$response = $this->server->dispatch($request);
		$insert_item = $response->get_data();
		
		$update_item_json = json_encode(
			array(
				'id'     => $insert_item['id'],
				'status' => "publish",
				'comment_status' => 'closed'
			)
		);
		$URL = $this->namespace . '/items/' . $insert_item['id'];
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($update_item_json);
		$response = $this->server->dispatch($request);
		$insert_item_err = $response->get_data();
		$this->assertEquals(400, $response->get_status());
		$this->assertArrayHasKey($metadatum_id['id'], $insert_item_err['errors'][0]);


		$item_metadatum_id = json_encode(
			array(
				'values' => ['1']
			)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_id['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_id);
		$response = $this->server->dispatch($request);
		$item_metadatum_id = $response->get_data();
		$this->assertEquals(200, $response->get_status());

		$item_metadatum_largura = json_encode(
			array(
				'parent_meta_id' => $item_metadatum_id['parent_meta_id'],
				'values' => ['10']
		 	)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_largura['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_largura);
		$response = $this->server->dispatch($request);
		$item_metadatum_largura = $response->get_data();
		$this->assertEquals(200, $response->get_status());

		$item_metadatum_altura = json_encode(
			array(
				'parent_meta_id' => $item_metadatum_id['parent_meta_id'],
				'values' => ['10']
			)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_altura['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_altura);
		$response = $this->server->dispatch($request);
		$item_metadatum_altura = $response->get_data();
		$this->assertEquals(200, $response->get_status());


		$URL = $this->namespace . '/items/' . $insert_item['id'];
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($update_item_json);
		$response = $this->server->dispatch($request);
		$insert_item = $response->get_data();
		$this->assertEquals(200, $response->get_status());


		$item_metadatum_id_duplicate = json_encode(
			array(
				'values' => ['1']
			)
		);
		$URL = $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $metadatum_id['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_id_duplicate);
		$response = $this->server->dispatch($request);
		$item_metadatum_id_duplicate = $response->get_data();
		$this->assertEquals(400, $response->get_status());
	}

		/**
	 * @group compound_metadatum
	 */
	public function test_create_multiple_compound_metadatum_API() {

		$metadatum_compound = json_encode(
			array(
				'name'        => 'quadrados',
				'description' => 'Descrição de um quadrado.',
				'status' => 'publish',
				'multiple' => 'yes',
				'metadata_type'  => 'Tainacan\Metadata_Types\Compound',
			)
		);

		$URL = $this->namespace . '/collection/' . $this->collection->get_id() . '/metadata';
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_compound);
		$response = $this->server->dispatch($request);

		$metadatum_compound_added = $response->get_data();
		$this->assertTrue(is_array($metadatum_compound_added) && array_key_exists('name', $metadatum_compound_added), sprintf('cannot create metadatum, response: %s', print_r($metadatum_compound_added, true)));
		$this->assertEquals('quadrados', $metadatum_compound_added['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_compound_added['collection_id']);

		$metadatum_id = json_encode(
			array(
				'name'        => 'id',
				'description' => 'id',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent'  => $metadatum_compound_added['id'],
				'collection_key' => 'yes',
				'required'  => 'yes'
			)
		);

		$metadatum_largura = json_encode(
			array(
				'name'        => 'largura',
				'description' => 'largura',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent' 	 => $metadatum_compound_added['id']
			)
		);

		$metadatum_altura = json_encode(
			array(
				'name'        => 'altura',
				'description' => 'altura',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent' 	 => $metadatum_compound_added['id']
			)
		);

		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_largura);
		$response = $this->server->dispatch($request);
		$metadatum_largura = $response->get_data();

		$this->assertTrue(is_array($metadatum_largura) && array_key_exists('name', $metadatum_largura), sprintf('cannot create metadatum, response: %s', print_r($metadatum_largura, true)));
		$this->assertEquals('largura', $metadatum_largura['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_largura['collection_id']);

		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_altura);
		$response = $this->server->dispatch($request);
		$metadatum_altura = $response->get_data();

		$this->assertTrue(is_array($metadatum_altura) && array_key_exists('name', $metadatum_altura), sprintf('cannot create metadatum, response: %s', print_r($metadatum_altura, true)));
		$this->assertEquals('altura', $metadatum_altura['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_altura['collection_id']);

		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($metadatum_id);
		$response = $this->server->dispatch($request);
		$metadatum_id = $response->get_data();

		$this->assertTrue(is_array($metadatum_id) && array_key_exists('name', $metadatum_id), sprintf('cannot create metadatum, response: %s', print_r($metadatum_id, true)));
		$this->assertEquals('id', $metadatum_id['name']);
		$this->assertEquals($this->collection->get_id(), $metadatum_id['collection_id']);


		$insert_item_json = json_encode(
			array(
				'collection_id' => $this->collection->get_id(),
				'status' => 'auto-draft',
				'comment_status' => 'closed'
			)
		);

		$URL = $this->namespace . '/collection/' . $this->collection->get_id() . '/items';
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($insert_item_json);
		$response = $this->server->dispatch($request);
		$insert_item = $response->get_data();
		
		$update_item_json = json_encode(
			array(
				'id'     => $insert_item['id'],
				'status' => "publish",
				'comment_status' => 'closed'
			)
		);
		$URL = $this->namespace . '/items/' . $insert_item['id'];
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($update_item_json);
		$response = $this->server->dispatch($request);
		$insert_item_err = $response->get_data();
		$this->assertEquals(400, $response->get_status());
		$this->assertArrayHasKey($metadatum_id['id'], $insert_item_err['errors'][0]);

		//1º instance
		$item_metadatum_id = json_encode(
			array(
				'values' => ['1']
			)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_id['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_id);
		$response = $this->server->dispatch($request);
		$item_metadatum_id = $response->get_data();
		$this->assertEquals(200, $response->get_status());

		$item_metadatum_largura = json_encode(
			array(
				'parent_meta_id' => $item_metadatum_id['parent_meta_id'],
				'values' => ['10']
		 	)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_largura['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_largura);
		$response = $this->server->dispatch($request);
		$item_metadatum_largura = $response->get_data();
		$this->assertEquals(200, $response->get_status());

		$item_metadatum_altura = json_encode(
			array(
				'parent_meta_id' => $item_metadatum_id['parent_meta_id'],
				'values' => ['10']
			)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_altura['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_altura);
		$response = $this->server->dispatch($request);
		$item_metadatum_altura = $response->get_data();
		$this->assertEquals(200, $response->get_status());

		//2º instance
		$item_metadatum_id = json_encode(
			array(
				'values' => ['2']
			)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_id['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_id);
		$response = $this->server->dispatch($request);
		$item_metadatum_id = $response->get_data();
		$this->assertEquals(200, $response->get_status());

		$item_metadatum_largura = json_encode(
			array(
				'parent_meta_id' => $item_metadatum_id['parent_meta_id'],
				'values' => ['20']
		 	)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_largura['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_largura);
		$response = $this->server->dispatch($request);
		$item_metadatum_largura = $response->get_data();
		$this->assertEquals(200, $response->get_status());

		$item_metadatum_altura = json_encode(
			array(
				'parent_meta_id' => $item_metadatum_id['parent_meta_id'],
				'values' => ['20']
			)
		);
		$URL = $this->namespace . '/item/' . $insert_item['id'] . '/metadata/' . $metadatum_altura['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_altura);
		$response = $this->server->dispatch($request);
		$item_metadatum_altura = $response->get_data();
		$this->assertEquals(200, $response->get_status());


		$URL = $this->namespace . '/items/' . $insert_item['id'];
		$request = new \WP_REST_Request('POST', $URL);
		$request->set_body($update_item_json);
		$response = $this->server->dispatch($request);
		$insert_item = $response->get_data();
		$this->assertEquals(200, $response->get_status());


		$this->assertEquals(2, count($insert_item['metadata']['quadrados']['value']));

		$this->assertEquals(1, $insert_item['metadata']['quadrados']['value'][0][0]['value']);
		$this->assertEquals(10, $insert_item['metadata']['quadrados']['value'][0][1]['value']);
		$this->assertEquals(10, $insert_item['metadata']['quadrados']['value'][0][2]['value']);

		$this->assertEquals(2, $insert_item['metadata']['quadrados']['value'][1][0]['value']);
		$this->assertEquals(20, $insert_item['metadata']['quadrados']['value'][1][1]['value']);
		$this->assertEquals(20, $insert_item['metadata']['quadrados']['value'][1][2]['value']);

		$item_metadatum_id_duplicate = json_encode(
			array(
				'values' => ['1']
			)
		);
		$URL = $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $metadatum_id['id'];
		$request = new \WP_REST_Request('PATCH', $URL);
		$request->set_body($item_metadatum_id_duplicate);
		$response = $this->server->dispatch($request);
		$item_metadatum_id_duplicate = $response->get_data();
		$this->assertEquals(400, $response->get_status());
	}

}

?>
