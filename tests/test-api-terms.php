<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
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
			'POST', $this->namespace . '/taxonomy/' . $taxonomy->get_id() . '/terms'
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

		$request = new \WP_REST_Request('DELETE', $this->namespace . '/taxonomy/' . $taxonomy->get_id() . '/terms/' . $term->get_term_id());

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertTrue($data);

		$term = get_term($term->get_term_id());

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
			'name' => 'Trap',
			'user' => 7
		]);

		$request = new \WP_REST_Request(
			'PATCH', $this->namespace . '/taxonomy/' . $taxonomy->get_id() . '/terms/' . $term->get_term_id()
		);

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertNotEquals('Rock', $data['name']);
		$this->assertEquals('Trap', $data['name']);
		$this->assertEquals(7, $data['user']);

		$this->assertEquals($taxonomy->get_db_identifier(), $data['taxonomy']);
	}

	public function test_fetch_terms(){
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'Gender',
				'description'  => 'Music types',
				'allow_insert' => 'yes',
				'status'       => 'publish'
			),
			true
		);

		$term1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Rock',
				'user'     => get_current_user_id(),
			),
			true
		);

		$term2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Trap',
				'user'     => get_current_user_id(),
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $taxonomy->get_id() . '/terms'
		);

		$request->set_query_params([
			'hideempty' => false
		]);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$terms_name = [$data[0]['name'], $data[1]['name']];

		$this->assertContains('Rock', $terms_name);
		$this->assertContains('Trap', $terms_name);

		#### FETCH A TERM ####

		$request = new \WP_REST_Request(
			'GET', $this->namespace  . '/taxonomy/' . $taxonomy->get_id() . '/terms/' . $term2->get_term_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Trap', $data['name']);

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $taxonomy->get_id() . '/terms'
		);
		$request->set_query_params([
			'hideempty' => false,
			'include' => [$term2->get_term_id()]
		]);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(1, sizeof($data));

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $taxonomy->get_id() . '/terms'
		);
		$request->set_query_params([
			'hideempty' => false,
			'include' => [$term1->get_term_id(), $term2->get_term_id()]
		]);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();
		$this->assertEquals(2, sizeof($data));
	}
	
	
	function test_terms_of_draft_taxonomy() {
		
		$taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes',
				'status'       => 'auto-draft'
	        ),
	        true
        );
		
		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();
		
		$new_attributes = [
			'hideempty' => false,
		];
		$request = new \WP_REST_Request(
			'GET', $this->namespace  . '/taxonomy/' . $taxonomy->get_id() . '/terms'
		);
		
		$request->set_query_params($new_attributes);
		
		$response = $this->server->dispatch($request);
		
		$data = $response->get_data();
		
		$this->assertEquals(0, sizeof($data), 'new auto draft taxonomy should return 0 terms');
		
	}
	
}

?>