<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadata_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_metadatum_in_a_collection() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection
			),
			true
		);

		$metadatum = json_encode(
			array(
				'name'        => 'Moeda',
				'description' => 'Descreve campo moeda.',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			)
		);
        
		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);
		$request->set_body($metadatum);

		$response = $this->server->dispatch($request);

		$metadatum_added = $response->get_data();
		$this->assertTrue(is_array($metadatum_added) && array_key_exists('name', $metadatum_added), sprintf('cannot create metadatum, response: %s', print_r($metadatum_added, true)));
		$this->assertEquals('Moeda', $metadatum_added['name']);

		$this->assertNotEquals('default', $metadatum_added['collection_id']);
	}

	public function test_fetch_a_metadatum_from_a_collection(){
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

		$request = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/metadata/' . $metadatumA->get_id());

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Data', $data['name']);
		$this->assertEquals($metadatumA->get_id(), $data['id']);
	}

	public function test_create_default_metadatum(){
		$metadatum = json_encode(
			array(
				'name'        => 'Ano de Publicação',
				'description' => 'Uma data no formato dd/mm/aaaa.',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/metadata'
		);
		$request->set_body($metadatum);

		$response = $this->server->dispatch($request);
		$metadatum_added = $response->get_data();

		$this->assertTrue(is_array($metadatum_added) && array_key_exists('name', $metadatum_added), sprintf('cannot create metadatum, response: %s', print_r($metadatum_added, true)));
		$this->assertEquals('Ano de Publicação', $metadatum_added['name']);

		$this->assertEquals('default', $metadatum_added['collection_id']);
	}

	public function test_fetch_default_metadata(){
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
				'name'        => 'Data 1',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'           => 'Data 2',
				'description'    => 'Descreve valor do campo data.',
				'collection_id'  => 'default',
				'status'         => 'publish',
				'metadata_type'     => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$request_fetch_defaults = new \WP_REST_Request('GET', $this->namespace . '/metadata');

		$response_defaults = $this->server->dispatch($request_fetch_defaults);

		$data = $response_defaults->get_data();

		$this->assertCount(1, $data);

		$this->assertEquals('default', $data[0]['collection_id']);
		$this->assertEquals('Data 2', $data[0]['name']);
	}

	public function test_get_item_and_collection_metadata(){
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
		$item_metadata->set_value('12/12/2017');

		$item_metadata->validate();
		$Tainacan_Item_Metadata->insert($item_metadata);

		#################### Get metadatum of collection ######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$metadata_names = array_map(function($metadatum) {return $metadatum['name'];}, $data);

		$this->assertContains('Data', $metadata_names);

		################### Get metadatum of item with value #######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/item/' . $item->get_id() . '/metadata'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
		$this->assertTrue(is_array($data) && array_key_exists(0, $data), sprintf('cannot read metadatum, response: %s', print_r($data, true)));


		$metadata_names = array_map(function($item_metadata) {return $item_metadata['metadatum']['name'];}, $data);
		$values = array_map(function($item_metadata) {return $item_metadata['value'];}, $data);

        $this->assertContains('Data', $metadata_names);
        $this->assertContains('12/12/2017', $values);
	}

	public function test_update_metadata(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection
			),
			true
		);

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

		$meta_values = json_encode(
			array(
				'values' => array(
					'19/01/2018',
					'19/02/2018',
				)
			)
		);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/item/' . $item->get_id() . '/metadata/' . $metadatum->get_id()
		);
		$request->set_body($meta_values);

		$response = $this->server->dispatch($request);

		$item_metadata_updated = $response->get_data();

		$metadatum_updated = $item_metadata_updated['metadatum'];

		$this->assertEquals($metadatum->get_id(), $metadatum_updated['id']);

		$this->assertEquals('19/01/2018', $item_metadata_updated['value'][0]);
		$this->assertEquals('19/02/2018', $item_metadata_updated['value'][1]);


		#### UPDATE METADATUM IN COLLECTION ####
        
		$values = json_encode([
			'name'        => 'Dia/Mês/Ano',
			'description' => 'Continua descrevendo o dado do campo.'
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

		// Mantém-se o valor antigo no item
		$metav = get_post_meta($item->get_id(), $data['id'], true);

		$this->assertEquals('19/01/2018', $metav);
        
	}

	public function test_trash_metadatum_in_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Metadatum Statement',
				'description' => 'No Statement',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'yes'
			),
			true
		);

		$trash_metadatum_request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/collection/'. $collection->get_id() . '/metadata/' . $metadatum->get_id()
		);

		$trash_metadatum_response = $this->server->dispatch($trash_metadatum_request);
		$data1 = $trash_metadatum_response->get_data();

		$this->assertEquals($metadatum->get_id(), $data1['id']);

		$metadatum_trashed = get_post($data1['id']);
		$this->assertEquals('trash', $metadatum_trashed->post_status);
	}

	public function test_trash_default_metadatum(){
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadatum Statement',
				'description'   => 'No Statement',
				'collection_id' => 'default',
				'status'        => 'publish',
				'metadata_type'    => 'Tainacan\Metadata_Types\Text',
				'multiple'      => 'yes'
			),
			true
		);

		$trash_metadatum_request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/metadata/' . $metadatum->get_id()
		);

		$trash_metadatum_response = $this->server->dispatch($trash_metadatum_request);
		$data1 = $trash_metadatum_response->get_data();

		$this->assertEquals($metadatum->get_id(), $data1['id']);

		$metadatum_trashed = get_post($data1['id']);
		$this->assertEquals('trash', $metadatum_trashed->post_status);
	}

	public function test_update_default_metadatum(){
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadatum Statement',
				'description'   => 'No Statement',
				'collection_id' => 'default',
				'status'        => 'publish',
				'metadata_type'    => 'Tainacan\Metadata_Types\Text',
				'multiple'      => 'no'
			),
			true
		);

		$new_attributes = json_encode([
			'name'        => 'No name',
			'description' => 'NOP!'
		]);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/metadata/' . $metadatum->get_id()
		);

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($metadatum->get_id(), $data['id']);
		$this->assertEquals('No name', $data['name']);
	}

	public function test_fetch_all_metadatum_values(){
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement',
				'status'      => 'publish'
			),
			true
		);

		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name1',
				'description' => 'No description1',
				'status'      => 'publish',
				'collection'  => $collection
			),
			true
		);

		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name2',
				'description' => 'No description2',
				'status'      => 'private',
				'collection'  => $collection
			),
			true
		);

		$item3 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name3',
				'description' => 'No description3',
				'status'      => 'private',
				'collection'  => $collection
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($item1, $metadatum);
		$item_metadata1->set_value('12/12/2017');

		$item_metadata1->validate();
		$Tainacan_Item_Metadata->insert($item_metadata1);

		$item_metadata2 = new \Tainacan\Entities\Item_Metadata_Entity($item2, $metadatum);
		$item_metadata2->set_value('02/03/2018');

		$item_metadata2->validate();
		$Tainacan_Item_Metadata->insert($item_metadata2);

		// Is repeated for test return of duplicates
		$item_metadata3 = new \Tainacan\Entities\Item_Metadata_Entity($item3, $metadatum);
		$item_metadata3->set_value('12/12/2017');

		$item_metadata3->validate();
		$Tainacan_Item_Metadata->insert($item_metadata3);

		//=======================

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/facets/' . $metadatum->get_id()
		);

		//=======================

		// Set no one user
		wp_set_current_user(0);

		$response1 = $this->server->dispatch($request);

		$data1 = $response1->get_data();

		$this->assertCount(1, $data1);
		$this->assertEquals('12/12/2017', $data1[0]['value']);

		//=======================

		$new_user1 = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user1);

		$response1 = $this->server->dispatch($request);

		$data1 = $response1->get_data();

		$this->assertCount(1, $data1);
		$this->assertEquals('12/12/2017', $data1[0]['value']);

		//=======================

		$new_user2 = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_user2);

		$response2 = $this->server->dispatch($request);

		$data2 = $response2->get_data();

		// Only two without duplicates
		$this->assertCount(2, $data2);
		$this->assertEquals('12/12/2017', $data2[0]['value']);
		$this->assertEquals('02/03/2018', $data2[1]['value']);
	}
}

?>