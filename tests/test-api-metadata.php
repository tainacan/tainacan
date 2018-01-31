<?php

namespace Tainacan\Tests;

use Tainacan\Repositories;

/**
 * @group api
 */
class TAINACAN_REST_Metadata_Controller extends TAINACAN_UnitApiTestCase {

	public function test_insert_metadata() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection
			),
			true
		);

		$field = $this->tainacan_field_factory->create_field('text', '', true);

		$field = json_encode(
			array(
				'name'        => 'Moeda',
				'description' => 'Descreve campo moeda.',
				'field_type'  => $field->get_primitive_type(),
			)
		);
        
		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/field'
		);
		$request->set_body($field);

		$response = $this->server->dispatch($request);

		$field_added = $response->get_data();
		$this->assertTrue(is_array($field_added) && array_key_exists('name', $field_added), sprintf('cannot create field, response: %s', print_r($field_added, true)));
		$this->assertEquals('Moeda', $field_added['name']);
        
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

		$field = $this->tainacan_field_factory->create_field('text', '', true);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => $field->get_primitive_type(),
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
			$this->namespace . '/collection/' . $collection->get_id() . '/field'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$field = $data[0];

		$this->assertEquals('Data', $field['name']);

		################### Get field of item with value #######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/item/' . $item->get_id() . '/field'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
		$this->assertTrue(is_array($data) && array_key_exists(0, $data), sprintf('cannot read field, response: %s', print_r($data, true)));
		$item_metadata = $data[0];
		$field = $item_metadata['field'];

		$this->assertEquals('Data', $field['name']);
		$this->assertEquals('12/12/2017', $item_metadata['value']);
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

		$field = $this->tainacan_field_factory->create_field('text', '', true);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'Data',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => $field->get_primitive_type(),
			),
			true
		);

		$meta_values = json_encode(
			array(
				'values' => '19/01/2018'
			)
		);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/item/' . $item->get_id() . '/field/' . $field->get_id()
		);
		$request->set_body($meta_values);

		$response = $this->server->dispatch($request);

		$item_metadata_updated = $response->get_data();

		$field_updated = $item_metadata_updated['field'];

		$this->assertEquals($field->get_id(), $field_updated['id']);

		$metav = get_post_meta($item->get_id(), $field_updated['id'], true);

		$this->assertEquals('19/01/2018', $metav);


		#### UPDATE FIELD IN COLLECTION ####
        
		$values = json_encode([
			'values'      => [
				'name'        => 'Dia/Mês/Ano',
				'description' => 'Continua descrevendo o dado do campo.'
			]
		]);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/collection/' . $collection->get_id() . '/field/' . $field->get_id()
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

}

?>