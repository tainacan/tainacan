<?php

namespace Tainacan\Tests;

class TAINACAN_REST_Terms extends TAINACAN_UnitApiTestCase {

	public function test_create_term(){
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => '1genero',
				'description'  => 'tipos de musica',
				'allow_insert' => 'yes',
				'status'       => 'publish'
			),
			true
		);

		$request = new \WP_REST_Request(
			'POST', $this->namespace . '/terms/taxonomy/' . $taxonomy->get_id()
		);

		$term = json_encode([
			'name' => 'Termo teste',
			'user' => get_current_user_id()
		]);

		$request->set_body($term);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Termo teste', $data['name']);
		$this->assertEquals($taxonomy->get_db_identifier(), $data['taxonomy']);
	}

	public function test_delete(){

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => '1genero',
				'description'  => 'tipos de musica',
				'allow_insert' => 'yes',
				'status'       => 'publish'
			),
			true
		);

		$term = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Rock',
				'user'     => get_current_user_id(),
			),
			true
		);

		$request = new \WP_REST_Request('DELETE', $this->namespace . '/terms/' . $term . '/taxonomy/' . $taxonomy->get_id());

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertTrue($data);

		$term = get_term($term);

		$this->assertNull($term);
	}

	public function test_update_term(){
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => '1genero',
				'description'  => 'tipos de musica',
				'allow_insert' => 'yes',
				'status'       => 'publish'
			),
			true
		);

		$term = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Rock',
				'user'     => get_current_user_id(),
			),
			true
		);

		$new_attributes = json_encode([
			'name' => 'Trap'
		]);

		$request = new \WP_REST_Request(
			'PATCH', $this->namespace . '/terms/' . $term . '/taxonomy/' . $taxonomy->get_id()
		);

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertNotEquals('Rock', $data['name']);
		$this->assertEquals('Trap', $data['name']);

		$this->assertEquals($taxonomy->get_db_identifier(), $data['taxonomy']);
	}
}

?>