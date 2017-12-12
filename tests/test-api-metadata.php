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
			$this->namespaced_route . '/metadata/collection/' . $collection->get_id()
		);
		$request->set_body($metadata);

		$response = $this->server->dispatch($request);

		$metadata_added = json_decode($response->get_data(), true);

		$this->assertEquals('Moeda', $metadata_added['name']);


		####################

		$meta_values = json_encode(
			array(
				'metadata_id' => $metadata_added['id'],
				'values' => 'Valorado'
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespaced_route . '/metadata/item/' . $item->get_id()
		);
		$request->set_body($meta_values);

		$response = $this->server->dispatch($request);

		$metadata_updated = json_decode($response->get_data(), true);

		$this->assertEquals($metadata_added['id'], $metadata_updated['id']);

		$metav = get_post_meta($item->get_id(), $metadata_updated['id'], true);

		$this->assertEquals('Valorado', $metav);
	}

}

?>