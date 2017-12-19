<?php

namespace Tainacan\Tests;

class TAINACAN_REST_Taxonomies_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_taxonomy(){
		$taxonomy_json = json_encode([
			'name'         => 'Nome',
			'description'  => 'desc',
			'allow_insert' => 'yes',
			'status'       => 'publish'
		]);

		$request = new \WP_REST_Request(
			'POST', $this->namespace . '/taxonomies'
		);
		$request->set_body($taxonomy_json);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Nome', $data['name']);
	}

	public function test_get_taxonomy_by_id(){
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => '1genero',
				'description'  => 'tipos',
				'allow_insert' => 'yes'
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomies/' . $taxonomy->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($taxonomy->get_name(), $data['name']);
	}

	public function test_get_all_taxonomies(){
		$taxonomy1 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => '1genero',
				'description'  => 'tipos de musica',
				'allow_insert' => 'yes',
				'status'       => 'publish'
			),
			true
		);

		$taxonomy2 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => '2genero',
				'description'  => 'tipos',
				'allow_insert' => 'yes',
				'status'       => 'publish'
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomies'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($taxonomy1->get_name(), $data[1]['name']);
		$this->assertEquals($taxonomy2->get_name(), $data[0]['name']);
	}
}

?>