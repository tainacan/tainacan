<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Visibilility_Controller extends TAINACAN_UnitApiTestCase {


	/*
		setup initail:
			create taxonomy_public
				create term-a-public
				create term-b-public
			create taxonomy_private
				create term-a-private
				create term-b-private
			
			create collection
				create a metadata-public (taxonomy_public)
				create a metadata-private (taxonomy_private)
				create item-a
				create item-b

		first-test:
			user not logged
				get terms of taxonomy_public = 200
				get terms of taxonomy_private = 401
				get terms on context=edit of taxonomy_public = 401
				get terms on context=edit of taxonomy_private = 401

		second-test:
			user logged
				get terms of taxonomy_public = 200
				get terms of taxonomy_private = 200
				get terms on context=edit of taxonomy_public = 200
				get terms on context=edit of taxonomy_private = 200

		third-test:
			user not logged
				get taxonomies = 200 - 1 taxonomy
				get taxonomies on context=edit of taxonomy_public = 401

		fourth-test:
			user logged
				get taxonomies = 200 - 2 taxonomies
				get taxonomies on context=edit = 200 - 2 taxonomies

		fifth-test:
			user logged
				get items filter by taxonomy_public = 200
				get items filter by taxonomy_private = 200
				get items on context=edit filter by taxonomy_public = 200
				get items on context=edit filter by taxonomy_private = 200

		fifth-test:
			user not logged
				get items filter by taxonomy_public = 200
				get items filter by taxonomy_private = 401
				get items on context=edit filter by taxonomy_public = 401
				get items on context=edit filter by taxonomy_private = 401
	*/

	public $collection;
	public $taxonomy_private;
	public $taxonomy_public;
	public $term_public;
	public $term_private;

	function setUp() {
		parent::setUp();

		$taxonomy_public = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'taxonomy_public',
				'description'  => 'taxonomy_public',
				'status' => 'publish'
			),
			true
		);
		$this->taxonomy_public = $taxonomy_public;

		$term_a_public = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy_public->get_db_identifier(),
				'name'     => 'term_a_public'
			),
			true
		);
		$this->term_public = $term_a_public;

		$term_b_public = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy_public->get_db_identifier(),
				'name'     => 'term_b_public'
			),
			true
		);

		$taxonomy_private = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'taxonomy_private',
				'description'  => 'taxonomy_private',
				'status' => 'private'
			),
			true
		);
		$this->taxonomy_private = $taxonomy_private;

		$term_a_private = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy_private->get_db_identifier(),
				'name'     => 'term_a_private'
			),
			true
		);
		$this->term_private = $term_a_private;

		$term_b_private = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy_private->get_db_identifier(),
				'name'     => 'term_b_private'
			),
			true
		);

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'collection',
				'status' => 'publish'
			),
			true
		);
		$this->collection = $collection;

		$metadata_tax_public = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadata-public',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy_public->get_id()
				],
				'multiple' => 'yes'
			),
			true
		);

		$metadata_tax_private = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadata-private',
				'status' => 'private',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy_private->get_id()
				],
				'multiple' => 'yes'
			),
			true
		);

		$item_a = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'item-a',
				'collection' => $collection,
				'status' => 'publish'
			),
			true
		);

		$item_b = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'item-b',
				'collection' => $collection,
				'status' => 'publish'
			),
			true
		);

		wp_set_post_terms($item_a->get_id(), [$term_a_public->get_id(), $term_b_public->get_id()], $taxonomy_public->get_db_identifier());
		wp_set_post_terms($item_b->get_id(), [$term_a_private->get_id(), $term_b_private->get_id()], $taxonomy_private->get_db_identifier());
		
	}

	public function test_get_terms_of_taxonomy_logged() {
		//tax public
		$request_public = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_public->get_id() . '/terms'
		);
		$request_public->set_query_params(['hideempty' => false]);
		$response = $this->server->dispatch($request_public);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(2, sizeof($data));

		//tax private:
		$request_private = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_private->get_id() . '/terms'
		);
		$request_private->set_query_params(['hideempty' => false]);
		$response = $this->server->dispatch($request_private);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(2, sizeof($data));

		//tax public - context=edit:
		$request_public_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_public->get_id() . '/terms'
		);
		$request_public_edit->set_query_params(['context' => 'edit']);
		$response = $this->server->dispatch($request_public_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		// $this->assertEquals(2, sizeof($data));

		//tax private - context=edit:
		$request_private_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_private->get_id() . '/terms'
		);
		$request_public->set_query_params(['context' => 'edit']);
		$response = $this->server->dispatch($request_private_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		//$this->assertEquals(2, sizeof($data));
	}

	public function test_get_terms_of_taxonomy_not_logged() {
		wp_logout();
		wp_set_current_user(0);
		//tax public
		$request_public = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_public->get_id() . '/terms'
		);
		$request_public->set_query_params(['hideempty' => false]);
		$response = $this->server->dispatch($request_public);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(2, sizeof($data));

		//tax private:
		$request_private = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_private->get_id() . '/terms'
		);
		$request_private->set_query_params(['hideempty' => false]);
		$response = $this->server->dispatch($request_private);
		$status = $response->status;
		$this->assertEquals(401, $status);

		//tax public - context=edit:
		$request_public_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_public->get_id() . '/terms'
		);
		$request_public_edit->set_query_params(['context' => 'edit']);
		$response = $this->server->dispatch($request_public_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(401, $status);

		//tax private - context=edit:
		$request_private_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomy/' . $this->taxonomy_private->get_id() . '/terms'
		);
		$request_public->set_query_params(['context' => 'edit']);
		$response = $this->server->dispatch($request_private_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(401, $status);
	}

	public function test_get_taxonomies_logged() {
		
		$request_public = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomies'
		);
		$response = $this->server->dispatch($request_public);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(2, sizeof($data));

		
		$request_public_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomies'
		);
		$request_public_edit->set_query_params(['context' => 'edit']);
		$response = $this->server->dispatch($request_public_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(2, sizeof($data));
	}

	public function test_get_taxonomies_not_logged() {
		wp_logout();
		wp_set_current_user(0);

		$request_public = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomies'
		);
		$response = $this->server->dispatch($request_public);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(1, sizeof($data));

		$request_public_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/taxonomies'
		);
		$request_public_edit->set_query_params(['context' => 'edit']);
		$response = $this->server->dispatch($request_public_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(401, $status);
	}

	public function test_get_items_logged() {
		//tax public
		$request_public = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);

		$tax_query = [[
			'taxonomy'=> $this->taxonomy_public->get_db_identifier(),
			'terms' => [$this->term_public->get_id()],
			'compare' => 'IN'
		]];

		$request_public->set_query_params(['taxquery' => $tax_query]);
		$response = $this->server->dispatch($request_public);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(1, sizeof($data['items']));

		//tax public - context=edit:
		$request_public_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);
		$request_public_edit->set_query_params(['context' => 'edit']);
		$request_public_edit->set_query_params(['taxquery' => $tax_query]);
		$response = $this->server->dispatch($request_public_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(1, sizeof($data['items']));

		//tax private:
		$request_private = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);

		$tax_query = [[
			'taxonomy'=> $this->taxonomy_private->get_db_identifier(),
			'terms' => [$this->term_private->get_id()],
			'compare' => 'IN'
		]];

		$request_private->set_query_params(['taxquery' => $tax_query]);
		$response = $this->server->dispatch($request_private);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(1, sizeof($data['items']));

		//tax private - context=edit:
		$request_private_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);
		$request_private_edit->set_query_params(['context' => 'edit']);
		$request_private_edit->set_query_params(['taxquery' => $tax_query]);
		$response = $this->server->dispatch($request_private_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(1, sizeof($data['items']));
	}

	public function test_get_items_not_logged() {
		wp_logout();
		wp_set_current_user(0);
		//tax public
		$request_public = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);

		$tax_query = [[
			'taxonomy'=> $this->taxonomy_public->get_db_identifier(),
			'terms' => [$this->term_public->get_id()],
			'compare' => 'IN'
		]];

		$request_public->set_query_params(['taxquery' => $tax_query]);
		$response = $this->server->dispatch($request_public);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(200, $status);
		$this->assertEquals(1, sizeof($data['items']));

		//tax public - context=edit:
		$request_public_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);
		$request_public_edit->set_query_params(['taxquery' => $tax_query]);
		$request_public_edit->set_query_params(['context' => 'edit']);
		$response = $this->server->dispatch($request_public_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(401, $status);

		//tax private:
		$request_private = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);

		$tax_query = [[
			'taxonomy'=> $this->taxonomy_private->get_db_identifier(),
			'terms' => [$this->term_private->get_id()],
			'compare' => 'IN'
		]];

		$request_private->set_query_params(['taxquery' => $tax_query]);
		$response = $this->server->dispatch($request_private);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(401, $status);

		//tax private - context=edit:
		$request_private_edit = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);
		$request_private_edit->set_query_params(['context' => 'edit']);
		$request_private_edit->set_query_params(['taxquery' => $tax_query]);
		$response = $this->server->dispatch($request_private_edit);
		$status = $response->status;
		$data = $response->get_data();
		$this->assertEquals(401, $status);
	}
}

?>