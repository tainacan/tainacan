<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Taxonomies_Controller extends TAINACAN_UnitApiTestCase {

	public function test_delete_or_trash_a_taxonomy(){
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

		$permanently = [ 'permanently' => false ];

		$request_trash = new \WP_REST_Request(
			'DELETE', $this->namespace . '/taxonomies/' . $taxonomy->get_id()
		);

		$request_trash->set_query_params($permanently);

		$this->server->dispatch($request_trash);

		$taxmy = get_post($taxonomy->get_id());

		$this->assertEquals('trash', $taxmy->post_status);
		$this->assertEquals(true, taxonomy_exists($taxonomy->get_db_identifier()));

		################ DELETE ###

		$permanently = [ 'permanently' => true ];

		$request_delete = new \WP_REST_Request(
			'DELETE', $this->namespace . '/taxonomies/' . $taxonomy->get_id()
		);

		$request_delete->set_query_params($permanently);

		$this->server->dispatch($request_delete);

		$this->assertEquals(false, taxonomy_exists($taxonomy->get_db_identifier()));
	}

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
		$this->assertTrue(is_array($data) && array_key_exists('name', $data), sprintf('cannot create a range, response: %s', print_r($data, true)));
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

		$names = [ $data[1]['name'], $data[0]['name']];

		$this->assertContains($taxonomy1->get_name(), $names);
		$this->assertContains($taxonomy2->get_name(), $names);
	}

	public function test_update_taxonomy(){
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

		$new_attributes = json_encode([
			'name'        => 'People',
			'description' => 'Male or Female'
		]);

		$request = new \WP_REST_Request('PATCH', $this->namespace . '/taxonomies/' . $taxonomy->get_id());

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$args=array(
			'name' => $taxonomy->get_db_identifier()
		);
		$output = 'objects';

		$tax = get_taxonomies($args, $output);

		$this->assertNotEquals($taxonomy->get_name(), $data['name']);
		$this->assertEquals('People', $data['name']);

		$this->assertEquals('People', $tax[$taxonomy->get_db_identifier()]->label);
	}
}

?>