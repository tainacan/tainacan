<?php

namespace Tainacan\Tests;

/**
 * Basic test case for api calls
 * @author jacson
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

	protected $user_id;

	public function setUp(){
		parent::setUp();

		// Create a Administrator user for test api with cookie authentication
		$this->user_id = $this->factory->user->create(
			array(
				'role' => 'administrator'
			)
		);

		// Set that user as current user
		wp_set_current_user( $this->user_id );

		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;

		do_action( 'rest_api_init' );
	}
}