<?php

namespace Tainacan\Tests;

/**
 *
 * @group api
 *
 */
class TAINACAN_REST_Logs_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_logs(){
		$this->tainacan_entity_factory->create_entity(
			'log',
			[
				'title'       => 'Log 1',
				'description' => 'Log number 1',
			],
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'log',
			[
				'title'       => 'Log 2',
				'description' => 'Log number 2',
			],
			true
		);

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/logs'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Log 1', $data[1]['title']);
		$this->assertEquals('Log 2', $data[0]['title']);
	}

	public function test_get_a_log(){
		$log = $this->tainacan_entity_factory->create_entity(
			'log',
			[
				'title'       => 'Log',
				'description' => 'A description',
			],
			true
		);

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/logs/' . $log->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Log', $data['title']);
		$this->assertEquals($log->get_id(), $data['id']);
	}

}

?>