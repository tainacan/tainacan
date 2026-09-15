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
	public function test_collection_description_round_trips_allowed_markup_via_api() {
		$description = '<p>Intro</p><h1>Heading</h1><h2>Subheading</h2><ul><li><strong>Bold</strong> and <em>emphasis</em></li></ul><ol><li><b>Bold two</b> <i>Italic</i><br /><a href="geo:12.34,56.78" title="Map">Map label</a></li></ol>';

		$create = new \WP_REST_Request( 'POST', $this->namespace . '/collections' );
		$create->set_body(
			json_encode(
				array(
					'name'        => 'Formatted description',
					'description' => $description,
				)
			)
		);

		$created = $this->server->dispatch( $create );
		$this->assertEquals( 201, $created->get_status(), print_r( $created->get_data(), true ) );
		$collection_id = $created->get_data()['id'];
		$this->assertSame( $description, $created->get_data()['description'] );

		$get = new \WP_REST_Request( 'GET', $this->namespace . '/collections/' . $collection_id );
		$fetched = $this->server->dispatch( $get );
		$this->assertEquals( 200, $fetched->get_status() );
		$this->assertSame( $description, $fetched->get_data()['description'] );

		$updated_description = '<p>Updated</p><a href="https://example.com" title="Updated link">Read this</a>';
		$update = new \WP_REST_Request( 'PATCH', $this->namespace . '/collections/' . $collection_id );
		$update->set_body( json_encode( array( 'description' => $updated_description ) ) );
		$updated = $this->server->dispatch( $update );
		$this->assertEquals( 200, $updated->get_status(), print_r( $updated->get_data(), true ) );
		$this->assertSame( $updated_description, $updated->get_data()['description'] );

		$fetched_after_update = $this->server->dispatch( $get );
		$this->assertEquals( 200, $fetched_after_update->get_status() );
		$this->assertSame( $updated_description, $fetched_after_update->get_data()['description'] );

		$empty_update = new \WP_REST_Request( 'PATCH', $this->namespace . '/collections/' . $collection_id );
		$empty_update->set_body( json_encode( array( 'description' => '' ) ) );
		$empty = $this->server->dispatch( $empty_update );
		$this->assertEquals( 200, $empty->get_status(), print_r( $empty->get_data(), true ) );
		$this->assertSame( '', $empty->get_data()['description'] );

		$fetched_empty = $this->server->dispatch( $get );
		$this->assertEquals( 200, $fetched_empty->get_status() );
		$this->assertSame( '', $fetched_empty->get_data()['description'] );
	}

	public function test_collection_description_assignment_removes_disallowed_markup() {
		$collection = new \Tainacan\Entities\Collection();
		$collection->set_description(
			'<p class="unsafe" style="color:red" onclick="alert(1)">Safe</p>' .
			'<div>Wrong container</div><script>alert(2)</script>' .
			'<a href="https://example.com" title="Allowed" target="_blank" rel="nofollow">Label</a>' .
			'<a href="javascript:alert(3)">Bad protocol</a>' .
			'<iframe src="https://example.com">Frame</iframe>'
		);

		$description = $collection->get_description();
		$this->assertStringContainsString( '<p>Safe</p>', $description );
		$this->assertStringContainsString( '<a href="https://example.com" title="Allowed">Label</a>', $description );
		$this->assertStringNotContainsString( 'class=', $description );
		$this->assertStringNotContainsString( 'style=', $description );
		$this->assertStringNotContainsString( 'onclick=', $description );
		$this->assertStringNotContainsString( 'target=', $description );
		$this->assertStringNotContainsString( 'rel=', $description );
		$this->assertStringNotContainsString( '<div', $description );
		$this->assertStringNotContainsString( '<script', $description );
		$this->assertStringNotContainsString( '<iframe', $description );
		$this->assertStringNotContainsString( 'javascript:', $description );

		$collection->set_description( '' );
		$this->assertSame( '', $collection->get_description() );

		$collection->set_description( '<p><br data-mce-bogus="1"></p>' );
		$this->assertSame( '', $collection->get_description() );
	}
}

?>
