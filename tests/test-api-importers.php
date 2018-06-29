<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Importers_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create(){
        
        $params = json_encode([
			'importer_slug'       => 'csv'
		]);


		$request  = new \WP_REST_Request('POST', $this->namespace . '/importers/session');
		$request->set_body($params);

		$response = $this->server->dispatch($request);

		$this->assertEquals(201, $response->get_status());

		$data = $response->get_data();


        $this->assertTrue( isset($data['id']) );
        $this->assertTrue( is_string($data['id']) );

        
    }
    
    public function test_update() {


        $importer = new \Tainacan\Importer\CSV();
        $session_id = $importer->get_id();

        $params = json_encode([
            'url'       => 'http://test.com',
            'options'   => ['delimiter' => ';'],
            'collection' => [
                'id' => 231,
                'map' => [
                    30 => 'column1',
                    31 => 'column2'
                ],
                'total_items' => 1234
            ]
		]);

        $request  = new \WP_REST_Request('PATCH', $this->namespace . '/importers/session/' . $session_id);
		$request->set_body($params);

        $response = $this->server->dispatch($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        
        $__importer = $_SESSION['tainacan_importer'][$session_id];

        $this->assertEquals('http://test.com', $__importer->get_url());
        $this->assertEquals(';', $__importer->get_option('delimiter'));

        $this->assertEquals($__importer->get_url(), $data['url']);

        $this->assertEquals(1234, $__importer->get_collections()[0]['total_items']);

		

    }

}

?>