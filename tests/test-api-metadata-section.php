<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadata_Section_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_empty_metadatum_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadatum = json_encode(
			array(
				'name'        => 'Dados Pessoais',
				'description' => 'Informações e detalhes.',
				'collection_id' => $collection->get_id()
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections'
		);
		$request->set_body($metadatum);

		$response = $this->server->dispatch($request);

		$metadatum_section_added = $response->get_data();
		$this->assertTrue(is_array($metadatum_section_added) && array_key_exists('name', $metadatum_section_added), sprintf('cannot create metadatum section, response: %s', print_r($metadatum_section_added, true)));
		$this->assertEquals('Dados Pessoais', $metadatum_section_added['name']);
		$this->assertTrue(empty($metadatum_section_added['metadatum_list']));
	}

	public function test_create_fill_metadatum_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-2',
				'description' => 'description-2',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_section = json_encode(
			array(
				'name'        => 'Dados Pessoais',
				'description' => 'Informações e detalhes.',
				'collection_id' => $collection->get_id(),
				'metadatum_list' => [$metadatum_1->get_id(), $metadatum_2->get_id(), $metadatum_2->get_id()]
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections'
		);
		$request->set_body($metadatum_section);

		$response = $this->server->dispatch($request);

		$metadatum_section_added = $response->get_data();
		$this->assertTrue(is_array($metadatum_section_added) && array_key_exists('name', $metadatum_section_added), sprintf('cannot create metadatum section, response: %s', print_r($metadatum_section_added, true)));
		$this->assertEquals('Dados Pessoais', $metadatum_section_added['name']);
		$this->assertEquals(2, count($metadatum_section_added['metadatum_list']));
	}

	public function test_add_metadata_metadatum_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-2',
				'description' => 'description-2',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_3 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-3',
				'description' => 'description-3',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_section = $this->tainacan_entity_factory->create_entity(
			'Metadatum_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'metadatum_list' => [$metadatum_1->get_id(), $metadatum_1->get_id()]
			),
			true
		);

		$metadatum_list = json_encode(
			array(
				'metadatum_list' => [$metadatum_2->get_id(), $metadatum_3->get_id()]
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadatum_section->get_id() . '/metadatum'
		);
		$request->set_body($metadatum_list);
		$response = $this->server->dispatch($request);
		$metadatum_section_added = $response->get_data();

		$this->assertTrue(is_array($metadatum_section_added) && array_key_exists('name', $metadatum_section_added), sprintf('cannot create metadatum section, response: %s', print_r($metadatum_section_added, true)));
		$this->assertEquals('Section', $metadatum_section_added['name']);
		$this->assertEquals(3, count($metadatum_section_added['metadatum_list']));
		$this->assertContains($metadatum_1->get_id(), $metadatum_section_added['metadatum_list']);
		$this->assertContains($metadatum_2->get_id(), $metadatum_section_added['metadatum_list']);
		$this->assertContains($metadatum_3->get_id(), $metadatum_section_added['metadatum_list']);
	}

	public function test_delete_metadata_metadatum_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-2',
				'description' => 'description-2',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_3 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-3',
				'description' => 'description-3',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_section = $this->tainacan_entity_factory->create_entity(
			'Metadatum_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'metadatum_list' => [$metadatum_1->get_id(), $metadatum_2->get_id(), $metadatum_3->get_id()]
			),
			true
		);

		$metadatum_list = json_encode(
			array(
				'metadatum_list' => [$metadatum_1->get_id(), $metadatum_3->get_id()]
			)
		);

		$request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadatum_section->get_id() . '/metadatum'
		);
		$request->set_body($metadatum_list);
		$response = $this->server->dispatch($request);
		$metadatum_section_added = $response->get_data();

		$this->assertTrue(is_array($metadatum_section_added) && array_key_exists('name', $metadatum_section_added), sprintf('cannot create metadatum section, response: %s', print_r($metadatum_section_added, true)));
		$this->assertEquals('Section', $metadatum_section_added['name']);
		$this->assertEquals(1, count($metadatum_section_added['metadatum_list']));
		$this->assertNotContains($metadatum_1->get_id(), $metadatum_section_added['metadatum_list']);
		$this->assertContains($metadatum_2->get_id(), $metadatum_section_added['metadatum_list']);
		$this->assertNotContains($metadatum_3->get_id(), $metadatum_section_added['metadatum_list']);

	}

	public function test_get_metadata_metadatum_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-2',
				'description' => 'description-2',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_3 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-3',
				'description' => 'description-3',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_section = $this->tainacan_entity_factory->create_entity(
			'Metadatum_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'metadatum_list' => [$metadatum_1->get_id(), $metadatum_2->get_id(), $metadatum_3->get_id()]
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadatum_section->get_id() . '/metadatum'
		);
		$response = $this->server->dispatch($request);
		$metadata_list = $response->get_data();

		$this->assertEquals(3, count($metadata_list));
	}

	public function test_get_metadatum_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-2',
				'description' => 'description-2',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_3 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-3',
				'description' => 'description-3',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_4 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-4',
				'description' => 'description-4',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'Metadatum_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'metadatum_list' => [$metadatum_1->get_id(), $metadatum_2->get_id()]
			),
			true,
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'Metadatum_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'metadatum_list' => [$metadatum_3->get_id(), $metadatum_4->get_id()]
			),
			true,
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections'
		);
		$response = $this->server->dispatch($request);
		$response_data = $response->get_data();
		// echo json_encode( $response_data );

		$this->assertEquals(2, count($response_data));
	}
}
