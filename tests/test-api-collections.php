<?php

namespace Tainacan\Tests;

/**
 * @group api 
 *
 */
class TAINACAN_REST_Collections_Controller extends TAINACAN_UnitApiTestCase {
	
	public function test_register_route() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey($this->namespace, $routes );
	}

	public function test_endpoints() {
		$the_route = $this->namespace;
		$routes = $this->server->get_routes();
		foreach( $routes as $route => $route_config ) {
			if( 0 === strpos( $the_route, $route ) ) {
				$this->assertTrue( is_array( $route_config ) );
				foreach( $route_config as $i => $endpoint ) {
					$this->assertArrayHasKey( 'callback', $endpoint );
					$this->assertArrayHasKey( 0, $endpoint[ 'callback' ], get_class( $this ) );
					$this->assertArrayHasKey( 1, $endpoint[ 'callback' ], get_class( $this ) );
					$this->assertTrue( is_callable( array( $endpoint[ 'callback' ][0], $endpoint[ 'callback' ][1] ) ) );
				}
			}
		}
	}

    public function test_create_and_fetch_collection_by_id(){
	    $collection_JSON = json_encode([
            'name'         => 'TesteJsonAdd',
            'description'  => 'Teste JSON',
        ]);
        
        $request = new \WP_REST_Request('POST', $this->namespace . '/collections');
        $request->set_body($collection_JSON);

        $response = $this->server->dispatch( $request );
        $this->assertEquals( 201, $response->get_status(), sprintf('response: %s', print_r($response, true)) );

        $collection = $response->get_data();
        $id = $collection['id'];
        
        $requestGet  = new \WP_REST_Request( 'GET', $this->namespace . '/collections/' . $id );
        $responseGet = $this->server->dispatch( $requestGet );
        
        $this->assertEquals( 200, $responseGet->get_status() );
        
        $data = $responseGet->get_data();
        
        $this->assertEquals('TesteJsonAdd', $data['name']);
    }

	public function test_default_per_page_via_api() {
		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collections' );
		$request->set_body(
			json_encode(
				array(
					'name'             => 'PerPageApi',
					'status'           => 'publish',
					'default_per_page' => 24,
				)
			)
		);

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 201, $response->get_status(), sprintf( 'response: %s', print_r( $response, true ) ) );
		$this->assertEquals( 24, $response->get_data()['default_per_page'] );

		global $TAINACAN_API_MAX_ITEMS_PER_PAGE;
		$cap = isset( $TAINACAN_API_MAX_ITEMS_PER_PAGE )
			? (int) $TAINACAN_API_MAX_ITEMS_PER_PAGE
			: (int) get_option( 'tainacan_option_search_results_per_page', 96 );

		$bad = new \WP_REST_Request( 'POST', $this->namespace . '/collections' );
		$bad->set_body(
			json_encode(
				array(
					'name'             => 'PerPageApiBad',
					'status'           => 'publish',
					'default_per_page' => $cap + 1,
				)
			)
		);

		$this->assertEquals( 400, $this->server->dispatch( $bad )->get_status() );
	}

    public function test_fetch_collections(){
    	$this->tainacan_entity_factory->create_entity(
    		'collection',
    		array(
    			'name'          => 'testeApi',
    			'description'   => 'adasdasdsa',
    			'default_order' => 'DESC',
    			'status'		=> 'publish'
    		),
    		true
		);

	    $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'          => 'Other',
			    'description'   => 'adasdasdsa',
			    'default_order' => 'DESC',
			    'status'		=> 'publish'
		    ),
		    true
	    );

	    $request  = new \WP_REST_Request( 'GET', $this->namespace . '/collections' );

    	$response = $this->server->dispatch( $request );

    	$this->assertEquals( 200, $response->get_status() );
    	
    	$data = $response->get_data();
    	//$data is a valid json?
    	//$this->assertTrue(json_last_error() === JSON_ERROR_NONE);

        $collectionsNames = array_map(function($data) {return $data['name'];}, $data);
        
        $this->assertContains('testeApi', $collectionsNames);
        $this->assertContains('Other', $collectionsNames);
    }

    public function test_delete_or_trash_a_collection(){
		$collection1 = $this->tainacan_entity_factory->create_entity('collection', '', true);

		// Delete permanently
		$delete_permanently = ['permanently' => true];

		$request  = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/collections/' . $collection1->get_id()
		);
		$request->set_query_params($delete_permanently);

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertEquals($collection1->get_id(), $data['id']);

		$no_post = get_post($collection1->get_id());

		$this->assertNull($no_post);

		#######################################################################################

	    $collection2 = $this->tainacan_entity_factory->create_entity('collection', '', true);

	    // Move to trash
	    $delete_permanently = ['permanently' => false];

	    $request  = new \WP_REST_Request(
		    'DELETE',
		    $this->namespace . '/collections/' . $collection2->get_id()
	    );
	    $request->set_query_params($delete_permanently);

	    $response = $this->server->dispatch($request);

	    $this->assertEquals(200, $response->get_status());

	    $data = $response->get_data();

	    $this->assertEquals($collection2->get_id(), $data['id']);

	    $post_meta = get_post_meta($collection2->get_id(), '_wp_trash_meta_status', true);

	    $this->assertNotEmpty($post_meta);
    }

    public function test_update_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'          => 'testeApi',
			    'description'   => 'adasdasdsa',
			    'default_order' => 'DESC',
			    'status'		=> 'publish'
		    ),
		    true
	    );

		$new_values = json_encode([
			'name'        => 'Test API',
			'description' => 'Collection for test.'
		]);

		$request = new \WP_REST_Request(
			'PATCH', $this->namespace . '/collections/' . $collection->get_id()
		);

		$request->set_body($new_values);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertNotEquals($collection->get_name(), $data['name']);
		$this->assertEquals('Test API', $data['name']);
    }

	public function test_create_collection_rejects_serialized_filters_order() {
		$author = $this->factory()->user->create( array( 'role' => 'tainacan-author' ) );
		wp_set_current_user( $author );

		Tainacan_POI_Canary::reset();
		$poison = serialize( new Tainacan_POI_Canary() );

		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collections' );
		$request->set_header( 'Content-Type', 'text/plain' );
		$request->set_body(
			wp_json_encode(
				array(
					'name'          => 'poi-collection',
					'status'        => 'publish',
					'filters_order' => $poison,
				)
			)
		);

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
		$this->assertFalse( Tainacan_POI_Canary::$woke );
	}

	public function test_public_items_list_does_not_instantiate_poisoned_filters_order() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'poi-items',
				'status' => 'publish',
			),
			true
		);

		Tainacan_POI_Canary::reset();
		update_post_meta( $collection->get_id(), 'filters_order', serialize( new Tainacan_POI_Canary() ) );

		wp_set_current_user( 0 );

		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $collection->get_id() . '/items' );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertFalse( Tainacan_POI_Canary::$woke );
	}

	public function test_create_collection_accepts_array_filters_order() {
		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collections' );
		$request->set_body(
			wp_json_encode(
				array(
					'name'          => 'valid-order',
					'status'        => 'publish',
					'filters_order' => array(
						array(
							'id'      => 1,
							'enabled' => true,
						),
					),
				)
			)
		);

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 201, $response->get_status(), sprintf( 'response: %s', print_r( $response, true ) ) );

		$order = $response->get_data()['filters_order'];
		$this->assertIsArray( $order );
		$this->assertCount( 1, $order );
		$this->assertEquals( 1, $order[0]['id'] );
		$this->assertTrue( (bool) $order[0]['enabled'] );
	}

	public function test_collection_description_denial_and_failed_save_preserve_existing_value() {
		$existing_description = '<p>Existing description</p>';
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'R4 authorization fixture',
				'description' => $existing_description,
				'status'      => 'publish',
			),
			true
		);

		$unauthorized_user = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $unauthorized_user );

		$unauthorized_update = new \WP_REST_Request(
			'PUT',
			$this->namespace . '/collections/' . $collection->get_id()
		);
		$unauthorized_update->set_body(
			json_encode(
				array(
					'description' => '<p>Unauthorized replacement</p>',
				)
			)
		);

		$denied = $this->server->dispatch( $unauthorized_update );
		$this->assertEquals( 403, $denied->get_status() );

		wp_set_current_user( $this->user_id );
		$fetch = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collections/' . $collection->get_id()
		);
		$after_denial = $this->server->dispatch( $fetch );
		$this->assertEquals( 200, $after_denial->get_status() );
		$this->assertSame( $existing_description, $after_denial->get_data()['description'] );

		$failed_update = new \WP_REST_Request(
			'PUT',
			$this->namespace . '/collections/' . $collection->get_id()
		);
		$failed_update->set_body(
			json_encode(
				array(
					'name'        => '',
					'description' => '<p>Invalid replacement</p>',
				)
			)
		);

		$failed = $this->server->dispatch( $failed_update );
		$this->assertEquals( 400, $failed->get_status() );
		$this->assertArrayHasKey( 'error_message', $failed->get_data() );

		$after_failure = $this->server->dispatch( $fetch );
		$this->assertEquals( 200, $after_failure->get_status() );
		$this->assertSame( $existing_description, $after_failure->get_data()['description'] );
	}
}

?>
