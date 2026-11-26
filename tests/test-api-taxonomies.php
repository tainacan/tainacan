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

	/**
	 * Creates a published taxonomy for the collection filter tests.
	 *
	 * @param string $name Taxonomy name.
	 *
	 * @return \Tainacan\Entities\Taxonomy
	 */
	private function create_taxonomy_for_collection_filter( $name ) {
		return $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => $name,
				'status' => 'publish',
			),
			true
		);
	}

	/**
	 * Creates a published taxonomy metadatum that uses a taxonomy.
	 *
	 * @param string                      $name       Metadatum name.
	 * @param \Tainacan\Entities\Taxonomy $taxonomy   The taxonomy used by the metadatum.
	 * @param array                       $extra_args Where the metadatum is created, such as collection or parent.
	 *
	 * @return \Tainacan\Entities\Metadatum
	 */
	private function create_taxonomy_metadatum( $name, $taxonomy, $extra_args ) {
		return $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array_merge(
				array(
					'name'                  => $name,
					'status'                => 'publish',
					'metadata_type'         => 'Tainacan\Metadata_Types\Taxonomy',
					'metadata_type_options' => array(
						'taxonomy_id'     => $taxonomy->get_id(),
						'allow_new_terms' => 'yes',
					),
				),
				$extra_args
			),
			true
		);
	}

	/**
	 * Lists the taxonomies filtered by a collection through the API.
	 *
	 * @param int   $collection_id The collection_id query param.
	 * @param array $query_params  Other query params.
	 *
	 * @return array The response status, the listed taxonomy ids and the X-WP-Total header.
	 */
	private function get_taxonomy_ids_by_collection( $collection_id, $query_params = array() ) {
		$request = new \WP_REST_Request( 'GET', $this->namespace . '/taxonomies' );
		$request->set_query_params(
			array_merge(
				array(
					'collection_id' => $collection_id,
					'perpage'       => 50,
				),
				$query_params
			)
		);

		$response = $this->server->dispatch( $request );

		return array(
			'status' => $response->get_status(),
			'ids'    => is_array( $response->get_data() ) ? array_map( 'intval', array_column( $response->get_data(), 'id' ) ) : array(),
			'total'  => isset( $response->get_headers()['X-WP-Total'] ) ? (int) $response->get_headers()['X-WP-Total'] : null,
		);
	}

	/**
	 * The collection filter lists the taxonomies used directly, inside a compound metadatum
	 * and by repository level metadata, and still applies the other filters.
	 */
	public function test_get_taxonomies_by_collection() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Taxonomies filter collection',
				'status' => 'publish',
			),
			true
		);

		$other_collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Taxonomies filter other collection',
				'status' => 'publish',
			),
			true
		);

		$used_taxonomy       = $this->create_taxonomy_for_collection_filter( 'Used by the collection' );
		$compound_taxonomy   = $this->create_taxonomy_for_collection_filter( 'Used inside a compound' );
		$repository_taxonomy = $this->create_taxonomy_for_collection_filter( 'Used by the repository' );
		$other_taxonomy      = $this->create_taxonomy_for_collection_filter( 'Used by another collection' );
		$unused_taxonomy     = $this->create_taxonomy_for_collection_filter( 'Not used' );

		$this->create_taxonomy_metadatum( 'Collection taxonomy', $used_taxonomy, array( 'collection' => $collection ) );
		$this->create_taxonomy_metadatum( 'Repository taxonomy', $repository_taxonomy, array( 'collection_id' => 'default' ) );
		$this->create_taxonomy_metadatum( 'Other collection taxonomy', $other_taxonomy, array( 'collection' => $other_collection ) );

		$compound = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Compound',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
			),
			true
		);
		$this->create_taxonomy_metadatum(
			'Compound child taxonomy',
			$compound_taxonomy,
			array(
				'collection' => $collection,
				'parent'     => $compound->get_id(),
			)
		);

		$result = $this->get_taxonomy_ids_by_collection( $collection->get_id() );

		$this->assertEquals( 200, $result['status'] );
		$this->assertContains( $used_taxonomy->get_id(), $result['ids'] );
		$this->assertContains( $compound_taxonomy->get_id(), $result['ids'] );
		$this->assertContains( $repository_taxonomy->get_id(), $result['ids'] );
		$this->assertNotContains( $other_taxonomy->get_id(), $result['ids'] );
		$this->assertNotContains( $unused_taxonomy->get_id(), $result['ids'] );
		$this->assertEquals( count( $result['ids'] ), $result['total'] );

		// The other filters still apply, such as postin.
		$result = $this->get_taxonomy_ids_by_collection( $collection->get_id(), array( 'postin' => array( $used_taxonomy->get_id(), $other_taxonomy->get_id() ) ) );

		$this->assertEquals( array( $used_taxonomy->get_id() ), $result['ids'] );
	}

	/**
	 * A collection without taxonomy metadata lists no taxonomy.
	 */
	public function test_get_taxonomies_by_collection_without_taxonomies() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Collection without taxonomies',
				'status' => 'publish',
			),
			true
		);
		$this->create_taxonomy_for_collection_filter( 'Taxonomy of nobody' );

		$result = $this->get_taxonomy_ids_by_collection( $collection->get_id() );

		$this->assertEquals( 200, $result['status'] );
		$this->assertEmpty( $result['ids'] );
		$this->assertEquals( 0, $result['total'] );
	}

	/**
	 * A collection that does not exist is a bad request.
	 */
	public function test_get_taxonomies_by_missing_collection() {
		$result = $this->get_taxonomy_ids_by_collection( 999999 );

		$this->assertEquals( 400, $result['status'] );
	}

	/**
	 * A private collection is answered as not found to a user who cannot read it.
	 */
	public function test_get_taxonomies_by_private_collection_is_not_found_without_read_permission() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Private collection',
				'status' => 'private',
			),
			true
		);

		$this->assertEquals( 200, $this->get_taxonomy_ids_by_collection( $collection->get_id() )['status'] );

		wp_set_current_user( 0 );

		// Same answer as a missing collection, so a visitor cannot tell that the private collection exists.
		$this->assertEquals( 400, $this->get_taxonomy_ids_by_collection( $collection->get_id() )['status'] );
	}
}


?>