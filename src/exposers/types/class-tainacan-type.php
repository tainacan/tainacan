<?php

namespace Tainacan\Exposers\Types;

/**
 * abstract class for implement exposer types
 *
 */
abstract class Type {
	
	protected $mappers = true; // List of supported mapper, leave true for all
	protected $extension = 'tnc'; // extension sufix for multi operation system compatibility
	public $slug = ''; // type slug for url safe
	public $name = ''; // User friend Name
	
	/**
	 * Change response after api callbacks
	 * @param \WP_REST_Response $response
	 * @param \WP_REST_Server $handler
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public abstract function rest_request_after_callbacks( $response, $handler, $request );
	
	/**
	 * Return list of supported mappers for this type 
	 */
	public function get_mappers() {
		return apply_filters('tainacan-exporser-type-mappers', $this->mappers, $this);
	}
	
	public function get_extension() {
		return $this->extension;
	}
}