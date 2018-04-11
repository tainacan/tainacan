<?php
namespace Tainacan\Exposers;

use Tainacan\Exposers\Mappers\Mapper;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Load exposers classes
 */ 
class Exposers {
	
	private $types = [];
	private $mappers = [];
	
	public function __construct() {
		$this->register_exposer_type('Tainacan\Exposers\Types\Xml');
		$this->register_exposer_type('Tainacan\Exposers\Types\Txt');
		$this->register_exposer_type('Tainacan\Exposers\Types\Html');
		$this->register_exposer_type('Tainacan\Exposers\Types\Csv');
		$this->register_exposer_type('Tainacan\Exposers\Types\OAI_PMH');
		do_action('tainacan-register-exposer-types');
		$this->register_exposer_mapper('Tainacan\Exposers\Mappers\Dublin_Core');
		$this->register_exposer_mapper('Tainacan\Exposers\Mappers\Value');
		do_action('tainacan-register-exposer-mappers');
		
		
		add_filter( 'rest_request_after_callbacks', [$this, 'rest_request_after_callbacks'], 10, 3 ); //exposer mapping
		add_filter( 'tainacan-rest-response', [$this, 'rest_response'], 10, 2 ); // exposer types
	}
	
	/**
	 * register exposers types class on array of types
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function register_exposer_type( $class_name ){
		if( is_object( $class_name ) ){
			$class_name = get_class( $class_name );
		}
		
		if(!in_array( $class_name, $this->types)){
			$this->types[] = $class_name;
		}
	}
	
	/**
	 * register exposers mappers class on array of types
	 *
	 * @param $class_name string | object The class name or the object instance
	 */
	public function register_exposer_mapper( $class_name ){
		if( is_object( $class_name ) ){
			$class_name = get_class( $class_name );
		}
		
		if(!in_array( $class_name, $this->mappers)){
			$this->mappers[] = $class_name;
		}
	}
	
	protected function check_class_name($class_name, $root = false, $prefix = 'Tainacan\Exposers\Types\\') {
		$class = $prefix.sanitize_text_field($class_name);
		$class = str_replace(['-', ' '], ['_', '_'], $class);
		
		return ($root ? '\\' : '').$class;
	}
	
	public function rest_response($item_arr, $request) {
		if($request->get_method() == 'GET' && substr($request->get_route(), 0, strlen('/tainacan/v2')) == '/tainacan/v2') {
			if($exposer = $this->hasMapper($request)) {
				return $exposer->rest_response($item_arr, $request);
			}
		}
		return $item_arr;
	}
	
	/**
	 * 
	 * @param \WP_REST_Response $response
	 * @param \WP_REST_Server $handler
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		if($request->get_method() == 'GET' && substr($request->get_route(), 0, strlen('/tainacan/v2')) == '/tainacan/v2') {
			if($exposer = $this->hasType($request)) {
				return $exposer->rest_request_after_callbacks($response, $handler, $request);
			}
		}
		// default JSON response
		return $response;
	}
	
	/**
	 * Return Type with request has type, false otherwise
	 * @param \WP_REST_Request $request
	 * @return Types\Type|boolean false
	 */
	public function hasType($request) {
		$body = json_decode( $request->get_body(), true );
		if(
			is_array($body) && array_key_exists('exposer-type', $body) &&
			in_array($this->check_class_name($body['exposer-type']), $this->types)
		) {
			$type = $this->check_class_name($body['exposer-type'], true);
			return new $type;
		}
		return false;
	}
	
	/**
	 * Check if there is a mapper
	 * @param \WP_REST_Request $request
	 * @return Mappers/Mapper|boolean false
	 */
	public function hasMapper($request) {
		$body = json_decode( $request->get_body(), true );
		$class_prefix = 'Tainacan\Exposers\Mappers\\';
		$type = $this->hasType($request);
		if( // There are a defined mapper
			is_array($body) && array_key_exists('exposer-map', $body) &&
			in_array($this->check_class_name($body['exposer-map'], false, $class_prefix), $this->mappers)
		) {
			if(
				$type === false || // do not have a exposer type
				$type->mappers === true || // the type accept all mappers
				( is_array($type->mappers) && in_array($body['exposer-map'], $type->mappers) ) ) { // the current mapper is accepted by type
				$mapper = $this->check_class_name($body['exposer-map'], true, $class_prefix);
				return new $mapper;
			} 
		} elseif( is_array($type->mappers) && count($type->mappers) > 0 ) { //there are no defined mapper, let use the first one o list if has a list
			$mapper = $this->check_class_name($type->mappers[0], true, $class_prefix);
			return new $mapper;
		}
		return false; // No mapper need, using Tainacan defautls
	}
}