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
}

?>
