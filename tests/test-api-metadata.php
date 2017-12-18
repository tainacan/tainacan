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

		$metadata_added = json_decode($response->get_data(), true);

		$this->assertEquals('Moeda', $metadata_added['name']);

		#################### Add value to metadata of item ##########################

		$meta_values = json_encode(
			array(
				'metadata_id' => $metadata_added['id'],
				'values' => 'Valorado'
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/metadata/item/' . $item->get_id()
		);
		$request->set_body($meta_values);

		$response = $this->server->dispatch($request);

		$item_metadata_updated = json_decode($response->get_data(), true);
		$metadata = json_decode($item_metadata_updated['metadata'], true);

		$this->assertEquals($metadata_added['id'], $metadata['id']);

		$metav = get_post_meta($item->get_id(), $metadata['id'], true);

		$this->assertEquals('Valorado', $metav);
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

		$this->assertContainsOnly('string', $data);

		$metadata = json_decode($data[0], true);

		$this->assertEquals('Data', $metadata['name']);

		################### Get metadata of item with value #######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/metadata/item/' . $item->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertContainsOnly('string', $data);

		$item_metadata = json_decode($data[0], true);
		$metadata = json_decode($item_metadata['metadata'], true);

		$this->assertEquals('Data', $metadata['name']);
		$this->assertEquals('12/12/2017', $item_metadata['value']);
	}

}

?>