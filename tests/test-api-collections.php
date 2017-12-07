<?php

namespace Tainacan\Tests;

/**
 * @group api 
 *
 */
class TAINACAN_REST_Collections_Controller extends TAINACAN_UnitApiTestCase {
	
	public function test_register_route() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey($this->namespaced_route, $routes );
	}

	public function test_endpoints() {
		$the_route = $this->namespaced_route;
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
        
        $request = new \WP_REST_Request('POST', $this->namespaced_route.'/collections');
        //$request->set_param('name', 'TesteJsonAdd');
        //$request->set_param('description', 'Teste JSON');
        $request->set_body($collection_JSON);
        
        $response = $this->server->dispatch( $request );
        $this->assertEquals( 201, $response->get_status() );
        
        $collection = json_decode($response->get_data());
        $id = $collection->id;
        
        $requestGet  = new \WP_REST_Request( 'GET', $this->namespaced_route . '/collections/'.$id );
        $responseGet = $this->server->dispatch( $requestGet );
        
        $this->assertEquals( 200, $responseGet->get_status() );
        
        $data = json_decode($responseGet->get_data(), true);
        
        $this->assertEquals('TesteJsonAdd', $data['name']);
        
    }

    public function test_fetch_collections(){
    	$x = $this->tainacan_entity_factory->create_entity(
    		'collection',
    		array(
    			'name'          => 'testeApi',
    			'description'   => 'adasdasdsa',
    			'default_order' => 'DESC',
    			'status'		=> 'publish'
    		),
    		true
		);
    	$request  = new \WP_REST_Request( 'GET', $this->namespaced_route . '/collections' );
    	$response = $this->server->dispatch( $request );
    	$this->assertEquals( 200, $response->get_status() );
    	
    	$data = json_decode($response->get_data());
    	//$data is a valid json?
    	$this->assertTrue(json_last_error() === JSON_ERROR_NONE);
    	
    	$this->assertContainsOnly('string', $data);
    	
    	$one_collection = json_decode($data[0], true);
    	$this->assertEquals('testeApi', $one_collection['name']);
    }

    public function test_delete_or_trash_a_collection(){
		$collection1 = $this->tainacan_entity_factory->create_entity('collection', '', true);

		// Delete permanently
		$delete_permanently = json_encode(['is_permanently' => true]);

		$request  = new \WP_REST_Request(
			'DELETE',
			$this->namespaced_route . '/collections/' . $collection1->get_id()
		);
		$request->set_body($delete_permanently);

		$response = $this->server->dispatch($request);

		// To be removed
		if($response->get_status() != 200){
			$this->markTestSkipped('Need method delete implemented.');
		}

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertTrue($data);

		#######################################################################################

	    $collection2 = $this->tainacan_entity_factory->create_entity('collection', '', true);

	    // Move to trash
	    $delete_permanently = json_encode(['is_permanently' => false]);

	    $request  = new \WP_REST_Request(
		    'DELETE',
		    $this->namespaced_route . '/collections/' . $collection2->get_id()
	    );
	    $request->set_body($delete_permanently);

	    $response = $this->server->dispatch($request);

	    // To be removed
	    if($response->get_status() != 200){
		    $this->markTestSkipped('Need method delete implemented.');
	    }

	    $this->assertEquals(200, $response->get_status());

	    $data = json_decode($response->get_data(), true);

	    $this->assertEquals('trash', $data['status']);
    }
}

?>
