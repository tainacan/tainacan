<?php
namespace Tainacan\Exposers;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Load exposers classes
 */ 
class Exposers {
	
	private $types = [];
	private $mappers = [];
	
	public function __construct() {
		add_filter( 'rest_request_after_callbacks', [$this, 'rest_request_after_callbacks'], 10, 3 );
		add_filter( 'tainacan-rest-response', [$this, 'rest_response'], 10, 2 );
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
	
	public function rest_response($item_arr, $request) {
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
			$body = json_decode( $request->get_body(), true );
			if(is_array($body) && array_key_exists('exposer-type', $body) &&
			in_array('Tainacan\Exposers\Types\\'.sanitize_text_field($body['exposer-type']), $this->types) ) {
				$type = '\Tainacan\Exposers\Types\\'.sanitize_text_field($body['exposer-type']);
				$exposer = new $type;
				return $exposer->rest_request_after_callbacks($response, $handler, $request);
			}
		}
		// default JSON response
		return $response;
	}
}