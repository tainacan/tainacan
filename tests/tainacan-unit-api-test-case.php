<?php

namespace Tainacan\Tests;

/**
 * Basic test case for api calls
 * @author medialab
 *
 */
class TAINACAN_UnitApiTestCase extends TAINACAN_UnitTestCase {
	/**
	 * Test REST Server
	 * @var \WP_REST_Server
	 */
	protected $server;
	
	/**
	 * Default Tainacan Namespace
	 * @var string default '/tainacan/v2'
	 */
	protected $namespace = '/tainacan/v2';

	public function setUp(){
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;

		do_action( 'rest_api_init' );
	}
}