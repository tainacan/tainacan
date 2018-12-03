<?php

namespace Tainacan\Exposers;

use Tainacan\Mappers_Handler;

/**
 * abstract class for implement exposer types
 *
 */
abstract class Exposer {
	
	protected $mappers = true; // List of supported mappers, leave true for all
	public $accept_no_mapper = true;
	public $slug = ''; // type slug for url safe
	private $name = ''; // User friendly Name
	private $description = ''; // User friendly Description
	
	/**
	 * Returns i18n exposer name
	 *
	 * Must be implemented by Exposer class
	 *
	 * @param string Name
	 * @return string
	 */
	protected function set_name($name) {
		$this->name = $name;
	}
	
	/**
	 * Sets i18n exposer description
	 *
	 * @param string Description 
	 * @return string
	 */
	protected function set_description($description) {
		$this->description = $description;
	}
	
	/**
	 * Gets the exposer name
	 * @return string exposer name
	 */
	public function get_name() {
		return $this->name;
	}
	
	/**
	 * Gets the exposer description
	 * @return string exposer description
	 */
	public function get_description() {
		return $this->description;
	}
	
	/**
	 * return exposer object as an array
	 * @return array
	 */
	public function _toArray() {
		return [
			'slug' => $this->slug,
			'name' => $this->get_name(),
			'description' => $this->get_description(),
			'mappers' => $this->get_mappers(),
			'accept_no_mapper' => $this->accept_no_mapper,
			'class_name' => get_class($this)
		];
	}
	
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
	 * @return array List of mappers
	 */
	public function get_mappers() {
		$mappers = apply_filters('tainacan-exporser-type-mappers', $this->mappers, $this);
		if ( true === $mappers ) {
			$mappers_handler = Mappers_Handler::get_instance();
			$registered_mappers = $mappers_handler->get_mappers();
			return array_keys($registered_mappers);
		} elseif (is_array($mappers)) {
			return $mappers;
		}
		return [];
	}

}