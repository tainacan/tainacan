<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadata_Sections_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_empty_metadata_section() {
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

		$metadata_section_added = $response->get_data();
		$this->assertTrue(is_array($metadata_section_added) && array_key_exists('name', $metadata_section_added), sprintf('cannot create metadata section, response: %s', print_r($metadata_section_added, true)));
		$this->assertEquals('Dados Pessoais', $metadata_section_added['name']);
		$this->assertTrue(empty($metadata_section_added['metadata_object_list']));
	}

	public function test_create_fill_metadata_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status'      => 'publish',
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
				'status'      => 'publish',
			),
			true
		);

		$metadata_section = json_encode(
			array(
				'name'        => 'Dados Pessoais',
				'description' => 'Informações e detalhes.',
				'collection_id' => $collection->get_id(),
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections'
		);
		$request->set_body($metadata_section);
		$response = $this->server->dispatch($request);
		$metadata_section_added = $response->get_data();
		$this->assertTrue(is_array($metadata_section_added) && array_key_exists('name', $metadata_section_added), sprintf('cannot create metadata section, response: %s', print_r($metadata_section_added, true)));
		$this->assertEquals('Dados Pessoais', $metadata_section_added['name']);

		$metadata_list = json_encode(
			array(
				'metadata_list' => [$metadatum_1->get_id(), $metadatum_2->get_id(), $metadatum_2->get_id()]
			)
		);
		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadata_section_added['id'] . '/metadata'
		);
		$request->set_body($metadata_list);
		$response = $this->server->dispatch($request);
		$metadata_section = $response->get_data();
		$this->assertEquals(2, count($metadata_section['metadata_object_list']));
	}

	public function test_add_metadata_metadata_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);


		$metadata_section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'status'      => 'publish',
			),
			true
		);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section->get_id()
			),
			true
		);

		$metadatum_2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-2',
				'description' => 'description-2',
				'collection' => $collection,
				'status'      => 'publish',
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
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadata_list = json_encode(
			array(
				'metadata_list' => [$metadatum_2->get_id(), $metadatum_3->get_id()]
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadata_section->get_id() . '/metadata'
		);
		$request->set_body($metadata_list);
		$response = $this->server->dispatch($request);
		$metadata_section_added = $response->get_data();

		$this->assertTrue(is_array($metadata_section_added) && array_key_exists('name', $metadata_section_added), sprintf('cannot create metadata section, response: %s', print_r($metadata_section_added, true)));
		$this->assertEquals('Section', $metadata_section_added['name']);
		$this->assertEquals(3, count($metadata_section_added['metadata_object_list']));
		$this->assertContains($metadatum_1->get_id(),  array_column($metadata_section_added['metadata_object_list'],'id'));
		$this->assertContains($metadatum_2->get_id(),  array_column($metadata_section_added['metadata_object_list'],'id'));
		$this->assertContains($metadatum_3->get_id(),  array_column($metadata_section_added['metadata_object_list'],'id'));

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'yes'
			),
			true
		);

		$values = json_encode([
			'name'        => 'Dia/Mês/Ano',
			'description' => 'Continua descrevendo o dado do campo.',
			'metadata_section_id' => $metadata_section->get_id()
		]);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata/' . $metadatum->get_id()
		);

		$request->set_body($values);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertEquals($metadatum->get_id(), $data['id']);
		$this->assertEquals('Dia/Mês/Ano', $data['name']);
		$this->assertEquals($metadata_section->get_id(), $data['metadata_section_id']);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadata_section->get_id() . '/metadata'
		);
		$response = $this->server->dispatch($request);
		$metadata_list = $response->get_data();
		$this->assertEquals(4, count($metadata_list));
		$this->assertNotNull(array_search($metadatum_1->get_id(), array_column($metadata_list, "id")));
		$this->assertNotNull(array_search($metadatum_2->get_id(), array_column($metadata_list, "id")));
		$this->assertNotNull(array_search($metadatum_3->get_id(), array_column($metadata_list, "id")));
		$this->assertNotNull(array_search($metadatum->get_id(), array_column($metadata_list, "id")));
	}

	public function test_delete_metadata_metadata_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadata_section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
			),
			true
		);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish',
				'metadata_section_id' => $metadata_section->get_id()
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
				'status' => 'publish',
				'metadata_section_id' => $metadata_section->get_id()
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
				'status' => 'publish',
				'metadata_section_id' => $metadata_section->get_id()
			),
			true
		);

		$metadata_list = json_encode(
			array(
				'metadata_list' => [$metadatum_1->get_id(), $metadatum_3->get_id()]
			)
		);

		$request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadata_section->get_id() . '/metadata'
		);
		$request->set_body($metadata_list);
		$response = $this->server->dispatch($request);
		$metadata_section_added = $response->get_data();

		$this->assertTrue(is_array($metadata_section_added) && array_key_exists('name', $metadata_section_added), sprintf('cannot create metadata section, response: %s', print_r($metadata_section_added, true)));
		$this->assertEquals('Section', $metadata_section_added['name']);
		$this->assertEquals(1, count($metadata_section_added['metadata_object_list']));
		$this->assertNotContains($metadatum_1->get_id(), array_column($metadata_section_added['metadata_object_list'], 'id'));
		$this->assertContains($metadatum_2->get_id(), array_column($metadata_section_added['metadata_object_list'], 'id'));
		$this->assertNotContains($metadatum_3->get_id(), array_column($metadata_section_added['metadata_object_list'], 'id'));

	}

	public function test_get_metadata_metadata_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$metadata_section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section->get_id()
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-2',
				'description' => 'description-2',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section->get_id()
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-3',
				'description' => 'description-3',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section->get_id()
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections/' . $metadata_section->get_id() . '/metadata'
		);
		$response = $this->server->dispatch($request);
		$metadata_list = $response->get_data();

		$this->assertEquals(3, count($metadata_list));
	}

	public function test_get_metadata_section() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);


		$section1 = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section-1',
				'description' => 'Section Description',
				'collection' => $collection,
				// 'metadata_list' => [$metadatum_1->get_id(), $metadatum_2->get_id()]
			),
			true,
			true
		);

		$section2 = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section-2',
				'description' => 'Section Description',
				'collection' => $collection,
				// 'metadata_list' => [$metadatum_3->get_id(), $metadatum_4->get_id()]
			),
			true,
			true
		);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'name-1',
				'description' => 'description-1',
				'collection' => $collection,
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $section1->get_id()
				
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
				'metadata_section_id' => $section1->get_id()
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
				'metadata_section_id' => $section2->get_id()
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
				'metadata_section_id' => $section2->get_id()
			),
			true
		);

		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$collection->set_metadata_section_order(
			[
				array( 'id' => $section1->get_id(), 'enabled' => true, 'metadata_order' => [
					array( 'id' => $metadatum_1->get_id(), 'enabled' => true ),
					array( 'id' => $metadatum_2->get_id(), 'enabled' => true )
				] ),
				array( 'id' => $section2->get_id(), 'enabled' => true, 'metadata_order' => [
					array( 'id' => $metadatum_3->get_id(), 'enabled' => true ),
					array( 'id' => $metadatum_4->get_id(), 'enabled' => true )
				])
			]
		);
		$Tainacan_Collections->update( $collection );

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata-sections'
		);
		$response = $this->server->dispatch($request);
		$response_data = $response->get_data();

		$this->assertEquals(3, count($response_data));

		$metadata_list_1 = $response_data[0]['metadata_object_list'];
		$metadata_list_2 = $response_data[1]['metadata_object_list'];

		$this->assertContains($metadatum_1->get_id(), array_column($metadata_list_1, 'id'), sprintf('Id: %s not found in array: %s', $metadatum_1->get_id(), print_r(array_column($metadata_list_1, 'id'), true)));
		$this->assertContains($metadatum_2->get_id(), array_column($metadata_list_1, 'id'), sprintf('Id: %s not found in array: %s', $metadatum_2->get_id(), print_r(array_column($metadata_list_1, 'id'), true)));
		$this->assertContains($metadatum_3->get_id(), array_column($metadata_list_2, 'id'), sprintf('Id: %s not found in array: %s', $metadatum_3->get_id(), print_r(array_column($metadata_list_2, 'id'), true)));
		$this->assertContains($metadatum_4->get_id(), array_column($metadata_list_2, 'id'), sprintf('Id: %s not found in array: %s', $metadatum_4->get_id(), print_r(array_column($metadata_list_2, 'id'), true)));
	}
}
