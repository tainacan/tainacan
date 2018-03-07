<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadata_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_field_in_a_collection() {
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

		$field = json_encode(
			array(
				'name'        => 'Moeda',
				'description' => 'Descreve campo moeda.',
				'field_type'  => 'Tainacan\Field_Types\Text',
			)
		);
        
		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/fields'
		);
		$request->set_body($field);

		$response = $this->server->dispatch($request);

		$field_added = $response->get_data();
		$this->assertTrue(is_array($field_added) && array_key_exists('name', $field_added), sprintf('cannot create field, response: %s', print_r($field_added, true)));
		$this->assertEquals('Moeda', $field_added['name']);

		$this->assertNotEquals('default', $field_added['collection_id']);
	}

	public function test_fetch_a_field_from_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$fieldA = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => 'Tainacan\Field_Types\Text',
			), true
		);

		$request = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/fields/' . $fieldA->get_id());

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Data', $data['name']);
		$this->assertEquals($fieldA->get_id(), $data['id']);
	}

	public function test_create_default_field(){
		$field = json_encode(
			array(
				'name'        => 'Ano de Publicação',
				'description' => 'Uma data no formato dd/mm/aaaa.',
				'field_type'  => 'Tainacan\Field_Types\Text',
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/fields'
		);
		$request->set_body($field);

		$response = $this->server->dispatch($request);
		$field_added = $response->get_data();

		$this->assertTrue(is_array($field_added) && array_key_exists('name', $field_added), sprintf('cannot create field, response: %s', print_r($field_added, true)));
		$this->assertEquals('Ano de Publicação', $field_added['name']);

		$this->assertEquals('default', $field_added['collection_id']);
	}

	public function test_fetch_default_fields(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$fieldA = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Data 1',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => 'Tainacan\Field_Types\Text',
			), true
		);

		$fieldB = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'           => 'Data 2',
				'description'    => 'Descreve valor do campo data.',
				'collection_id'  => 'default',
				'status'         => 'publish',
				'field_type'     => 'Tainacan\Field_Types\Text',
			), true
		);

		$request_fetch_defaults = new \WP_REST_Request('GET', $this->namespace . '/fields');

		$response_defaults = $this->server->dispatch($request_fetch_defaults);

		$data = $response_defaults->get_data();

		$this->assertCount(1, $data);

		$this->assertEquals('default', $data[0]['collection_id']);
		$this->assertEquals('Data 2', $data[0]['name']);
	}

	public function test_get_item_and_collection_metadata(){
		global $Tainacan_Item_Metadata;

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

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => 'Tainacan\Field_Types\Text',
			),
			true
		);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $field);
		$item_metadata->set_value('12/12/2017');

		$item_metadata->validate();
		$Tainacan_Item_Metadata->insert($item_metadata);

		#################### Get field of collection ######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/fields'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$fields_names = array_map(function($field) {return $field['name'];}, $data);

		$this->assertContains('Data', $fields_names);

		################### Get field of item with value #######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/item/' . $item->get_id() . '/metadata'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
		$this->assertTrue(is_array($data) && array_key_exists(0, $data), sprintf('cannot read field, response: %s', print_r($data, true)));


		$fields_names = array_map(function($item_metadata) {return $item_metadata['field']['name'];}, $data);
		$values = array_map(function($item_metadata) {return $item_metadata['value'];}, $data);

        $this->assertContains('Data', $fields_names);
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

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Data',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => 'Tainacan\Field_Types\Text',
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
			$this->namespace . '/item/' . $item->get_id() . '/metadata/' . $field->get_id()
		);
		$request->set_body($meta_values);

		$response = $this->server->dispatch($request);

		$item_metadata_updated = $response->get_data();

		$field_updated = $item_metadata_updated['field'];

		$this->assertEquals($field->get_id(), $field_updated['id']);

		$this->assertEquals('19/01/2018', $item_metadata_updated['value'][0]);
		$this->assertEquals('19/02/2018', $item_metadata_updated['value'][1]);


		#### UPDATE FIELD IN COLLECTION ####
        
		$values = json_encode([
			'name'        => 'Dia/Mês/Ano',
			'description' => 'Continua descrevendo o dado do campo.'
		]);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/collection/' . $collection->get_id() . '/fields/' . $field->get_id()
		);

		$request->set_body($values);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
        
		$this->assertEquals($field->get_id(), $data['id']);
		$this->assertEquals('Dia/Mês/Ano', $data['name']);

		// Mantém-se o valor antigo no item
		$metav = get_post_meta($item->get_id(), $data['id'], true);

		$this->assertEquals('19/01/2018', $metav);
        
	}

	public function test_trash_field_in_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Field Statement',
				'description' => 'No Statement',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => 'Tainacan\Field_Types\Text',
				'multiple'    => 'yes'
			),
			true
		);

		$trash_field_request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/collection/'. $collection->get_id() . '/fields/' . $field->get_id()
		);

		$trash_field_response = $this->server->dispatch($trash_field_request);
		$data1 = $trash_field_response->get_data();

		$this->assertEquals($field->get_id(), $data1['id']);

		$field_trashed = get_post($data1['id']);
		$this->assertEquals('trash', $field_trashed->post_status);
	}

	public function test_trash_default_field(){
		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'Field Statement',
				'description'   => 'No Statement',
				'collection_id' => 'default',
				'status'        => 'publish',
				'field_type'    => 'Tainacan\Field_Types\Text',
				'multiple'      => 'yes'
			),
			true
		);

		$trash_field_request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/fields/' . $field->get_id()
		);

		$trash_field_response = $this->server->dispatch($trash_field_request);
		$data1 = $trash_field_response->get_data();

		$this->assertEquals($field->get_id(), $data1['id']);

		$field_trashed = get_post($data1['id']);
		$this->assertEquals('trash', $field_trashed->post_status);
	}

	public function test_update_default_field(){
		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'Field Statement',
				'description'   => 'No Statement',
				'collection_id' => 'default',
				'status'        => 'publish',
				'field_type'    => 'Tainacan\Field_Types\Text',
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
			$this->namespace . '/fields/' . $field->get_id()
		);

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($field->get_id(), $data['id']);
		$this->assertEquals('No name', $data['name']);
	}

	public function test_fetch_all_field_values(){
		global $Tainacan_Item_Metadata;

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

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => 'Tainacan\Field_Types\Text',
			),
			true
		);

		$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($item1, $field);
		$item_metadata1->set_value('12/12/2017');

		$item_metadata1->validate();
		$Tainacan_Item_Metadata->insert($item_metadata1);

		$item_metadata2 = new \Tainacan\Entities\Item_Metadata_Entity($item2, $field);
		$item_metadata2->set_value('02/03/2018');

		$item_metadata2->validate();
		$Tainacan_Item_Metadata->insert($item_metadata2);

		//=======================

		$query = [
			'fetch' => 'all_field_values'
		];

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/fields/' . $field->get_id()
		);
		$request->set_query_params($query);

		//=======================

		// Set no one user
		wp_set_current_user(0);

		$response1 = $this->server->dispatch($request);

		$data1 = $response1->get_data();

		$this->assertCount(1, $data1);
		$this->assertEquals('12/12/2017', $data1[0]['mvalue']);

		//=======================

		$new_user1 = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user1);

		$response1 = $this->server->dispatch($request);

		$data1 = $response1->get_data();

		$this->assertCount(1, $data1);
		$this->assertEquals('12/12/2017', $data1[0]['mvalue']);

		//=======================

		$new_user2 = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_user2);

		$response2 = $this->server->dispatch($request);

		$data2 = $response2->get_data();

		$this->assertCount(2, $data2);
		$this->assertEquals('12/12/2017', $data2[0]['mvalue']);
		$this->assertEquals('02/03/2018', $data2[1]['mvalue']);
	}
}

?>