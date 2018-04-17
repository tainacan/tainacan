<?php

namespace Tainacan\Exposers\Types;

abstract class Type {
	
	public $mappers = true; // List of supported mapper, leave true for all
	
	/**
	 * @param \WP_REST_Response $response
	 * @param \WP_REST_Server $handler
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public abstract function rest_request_after_callbacks( $response, $handler, $request );
}