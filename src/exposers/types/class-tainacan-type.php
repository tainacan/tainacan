<?php

namespace Tainacan\Exposers\Types;

/**
 * abstract class for implement exposer types
 *
 */
abstract class Type {
	
	protected $mappers = true; // List of supported mapper, leave true for all
	
	/**
	 * Change response after api callbacks
	 * @param \WP_REST_Response $response
	 * @param \WP_REST_Server $handler
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public abstract function rest_request_after_callbacks( $response, $handler, $request );
	
	public function get_mappers() {
		return apply_filters('tainacan-exporser-type-mappers', $this->mappers, $this);
	}
}