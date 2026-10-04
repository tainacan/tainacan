<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Importers_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create() {
		$params = json_encode([ 'importer_slug' => 'csv' ]);

		$request  = new \WP_REST_Request('POST', $this->namespace . '/importers/session');
		$request->set_body($params);
		$response = $this->server->dispatch($request);
		$this->assertEquals(201, $response->get_status());
		$data = $response->get_data();
		$this->assertTrue( isset($data['id']) );
		$this->assertTrue( is_string($data['id']) );
	}

	public function test_update() {
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$slug='csv';
		$importer = $Tainacan_Importer_Handler->initialize_importer($slug);
		$Tainacan_Importer_Handler->save_importer_instance($importer);
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
		$__importer = $Tainacan_Importer_Handler->get_importer_instance_by_session_id($session_id);
		$this->assertEquals('http://test.com', $__importer->get_url());
		$this->assertEquals(';', $__importer->get_option('delimiter'));
		$this->assertEquals($__importer->get_url(), $data['url']);
		$this->assertEquals(1234, $__importer->get_collections()[0]['total_items']);
	}

	public function test_update_rejects_tmp_file_and_does_not_apply_other_fields() {
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$importer = $Tainacan_Importer_Handler->initialize_importer( 'csv' );
		$Tainacan_Importer_Handler->save_importer_instance( $importer );
		$session_id = $importer->get_id();

		$params = json_encode([
			'url'      => 'http://should-not-stick.example',
			'tmp_file' => '/etc/passwd',
		]);
		$request = new \WP_REST_Request( 'PATCH', $this->namespace . '/importers/session/' . $session_id );
		$request->set_body( $params );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'Unknown importer attribute.', $response->get_data()['error_message'] );

		$stored = $Tainacan_Importer_Handler->get_importer_instance_by_session_id( $session_id );
		$this->assertEmpty( $stored->get_url() );
		$this->assertNull( $stored->get_tmp_file() );

		$source = new \WP_REST_Request( 'GET', $this->namespace . '/importers/session/' . $session_id . '/source_info' );
		$source_response = $this->server->dispatch( $source );
		$this->assertEquals( 200, $source_response->get_status() );
		$this->assertStringNotContainsString( 'root:x:0:0', wp_json_encode( $source_response->get_data() ) );
	}

}