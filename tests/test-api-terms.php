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
			'POST', $this->namespace . '/terms/' . $taxonomy->get_id()
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
}

?>