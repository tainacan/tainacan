<?php

namespace Tainacan\Tests;

use Tainacan\Repositories;

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

		$metadata = json_encode(
			array(
				'name'        => 'Moeda',
				'description' => 'Descreve campo moeda.',
				'field_type'  => $field->get_primitive_type(),
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/metadata/collection/' . $collection->get_id()
		);
		$request->set_body($metadata);

		$response = $this->server->dispatch($request);

		$metadata_added = $response->get_data();

		$this->assertEquals('Moeda', $metadata_added['name']);
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

		$metadata = $this->tainacan_entity_factory->create_entity(
			'metadata',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'field_type'  => $field->get_primitive_type(),
			),
			true
		);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadata);
		$item_metadata->set_value('12/12/2017');

		$item_metadata->validate();
		$Tainacan_Item_Metadata->insert($item_metadata);

		#################### Get metadata of collection ######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/metadata/collection/' . $collection->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$metadata = $data[0];

		$this->assertEquals('Data', $metadata['name']);

		################### Get metadata of item with value #######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/metadata/item/' . $item->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$item_metadata = $data[0];
		$metadata = $item_metadata['metadata'];

		$this->assertEquals('Data', $metadata['name']);
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

		$metadata = $this->tainacan_entity_factory->create_entity(
			'metadata',
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
				'metadata_id' => $metadata->get_id(),
				'values' => '19/01/2018'
			)
		);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/metadata/item/' . $item->get_id()
		);
		$request->set_body($meta_values);

		$response = $this->server->dispatch($request);

		$item_metadata_updated = $response->get_data();

		$metadata_updated = $item_metadata_updated['metadata'];

		$this->assertEquals($metadata->get_id(), $metadata_updated['id']);

		$metav = get_post_meta($item->get_id(), $metadata_updated['id'], true);

		$this->assertEquals('19/01/2018', $metav);
	}

}

?>