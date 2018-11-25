<?php
namespace Tainacan;

use Tainacan\Mappers\Mapper;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Load exposers classes
 */ 
class Exposers_Handler {
	
	protected $exposers = [];
	
	private static $instance = null;
	private static $request = null;
	
	
	const TYPE_PARAM = 'exposer';
	
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function __construct() {
	    self::$instance = $this;
		//$this->register_exposer('Tainacan\Exposers\Xml');
		//$this->register_exposer('Tainacan\Exposers\Txt');
		$this->register_exposer('Tainacan\Exposers\Html');
		$this->register_exposer('Tainacan\Exposers\Csv');
		//$this->register_exposer('Tainacan\Exposers\OAI_PMH');
		//$this->register_exposer('Tainacan\Exposers\JSON_LD');
		do_action('tainacan-register-exposer', $this);
		
		add_filter( 'rest_request_after_callbacks', [$this, 'rest_request_after_callbacks'], 10, 3 ); //exposer types
		// add_filter( 'tainacan-rest-response', [$this, 'rest_response'], 10, 2 ); // exposer mapper
		
	}
	
	/**
	 * register exposers type
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function register_exposer( $class_name ){
	    $obj = $class_name;
		if( is_object( $class_name ) ){
			$class_name = get_class( $class_name );
		} else {
		    $obj = new $class_name;
		}
		
		if(!in_array( $class_name, $this->exposers)){
		    $this->exposers[$obj->slug] = $class_name;
		}
	}
	
	/**
	 * unregister exposers type
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function unregister_exposer( $class_name ){
	    $obj = $class_name;
		if( is_object( $class_name ) ){
			$class_name = get_class( $class_name );
		} else {
		    $obj = new $class_name;
		}
		
		if ( array_key_exists($obj->slug, $this->exposers) ) {
			unset($this->exposers[$obj->slug]);
		}
		
	}
	
	
	
	/**
	 * Return namespaced class name 
	 * @param string $class_name
	 * @param boolean $root
	 * @param string $prefix
	 * @return string
	 */
	public function check_class_name($class_name, $root = false, $prefix = 'Tainacan\Exposer\\') {
	    if(is_string($class_name)) {
    	    if(array_key_exists($class_name, $this->exposers)) {
                $class_name = $this->exposers[$class_name];
                $prefix = '';
    	    }
	    }
		$class = $prefix.sanitize_text_field($class_name);
		$class = str_replace(['-', ' '], ['_', '_'], $class);
		
		return ($root ? '\\' : '').$class;
	}
	
	/**
	 * Check if rest response need mapper
	 * @param array $item_arr
	 * @param \WP_REST_Request $request
	 * @return array
	 */
	public function rest_response($item_arr, $request) {
		if($request->get_method() == 'GET' && $this->is_tainacan_request($request)) {
			if($exposer = $this->request_has_mapper($request)) {
				if(substr($request->get_route(), 0, strlen('/tainacan/v2/items')) == '/tainacan/v2/items') { //TODO do it at rest not here
					$repos_items = \Tainacan\Repositories\Items::get_instance();
					$item = $repos_items->fetch($item_arr['id']);
					$items_metadata = $item->get_metadata();
					$prepared_item = [];
					foreach ($items_metadata as $item_metadata){
						array_push($prepared_item, $item_metadata->_toArray());
					}
					$item_arr = $prepared_item;
				}
				return $this->map($item_arr, $exposer, $request); //TODO request -> args
			}
		}
		return $item_arr;
	}
	
	
	
	/**
	 * check if is a tainacan request
	 * @param \WP_REST_Request $request
	 * @return boolean
	 */
	public function is_tainacan_request($request) {
	    return substr($request->get_route(), 0, strlen('/tainacan/v2')) == '/tainacan/v2';
	}
	
	/**
	 * check if query came from url
	 * @param \WP_REST_Request $request
	 */
	public static function request_has_url_param($request) {
	    $Tainacan_Exposers = self::get_instance();
	    $query_url_params = $request->get_query_params();
	    if (
	        is_array($query_url_params) && array_key_exists(self::TYPE_PARAM, $query_url_params) &&
	        $Tainacan_Exposers->exposer_exists($query_url_params[self::TYPE_PARAM])
	    ) {
	        return true;
	    }
	    return false;
	}
	
	/**
	 * adapt request response to exposer type 
	 * @param \WP_REST_Response $response
	 * @param \WP_REST_Server $handler
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		if($this->is_tainacan_request($request) && $response instanceof \WP_REST_Response ) {
    		if($request->get_method() == 'GET') {
    			if($exposer = $this->request_has_exposer($request)) {
    				$type_responde = $exposer->rest_request_after_callbacks($response, $handler, $request);
    				if(self::request_has_url_param($request)) {
    				    header(implode('', $response->get_headers()));
    				    echo stripcslashes($response->get_data());
    				    exit();
    				}
    				return $type_responde;
    			}
    		} 
	    }
		// default JSON response
		return $response;
	}
	
	/**
	 * Return if exposer is registered
	 * @param string $exposer
	 * @return boolean
	 */
	public function exposer_exists($exposer) {
		return in_array($this->check_class_name($exposer), $this->exposers);
	}
	/**
	 * Return Exposer if request has exposer, false otherwise
	 * @param \WP_REST_Request $request
	 * @return Exposers\Exposer|boolean false
	 */
	public static function request_has_exposer($request) {
		$body = json_decode( $request->get_body(), true );
		$query_url_params = $request->get_query_params();
		$Tainacan_Exposers = self::get_instance();
		if(
    			is_array($body) && array_key_exists(self::TYPE_PARAM, $body) &&
    			$Tainacan_Exposers->exposer_exists($body[self::TYPE_PARAM])
		) {
			$exposer = $Tainacan_Exposers->check_class_name($body[self::TYPE_PARAM], true);
			return new $exposer;
		} elseif (
                is_array($query_url_params) && array_key_exists(self::TYPE_PARAM, $query_url_params) &&
                $Tainacan_Exposers->exposer_exists($query_url_params[self::TYPE_PARAM])
        ){
            $exposer = $Tainacan_Exposers->check_class_name($query_url_params[self::TYPE_PARAM], true);
		    return new $exposer;
		}
		return false;
	}
	
	
	
	/**
	 * Return list of registered exposers
	 * @param string $output output format, ARRAY_N or OBJECT
	 * @return array of slug or array of \Tainacan\Exposers\Exposer
	 */
	public function get_exposers($output = \ARRAY_N) {
	    $ret = [];
	    switch ($output) {
	        case \OBJECT:
	            foreach ($this->exposers as $type) {
	                $ret[] = new $type;
	            }
	            break;
	        case \ARRAY_N:
	        default:
	            return $this->exposers;
	            break;
	    }
	    return $ret;
	}
	
	
	
	/**
	 * 
	 * @param string $base_url url base for exposer parameters append
	 * @return string|string[][]
	 */
	public static function get_exposer_urls($base_url = '') {
	    return [];
		$Tainacan_Exposers = self::get_instance();
	    $mappers = $Tainacan_Exposers->get_mappers(\OBJECT);
	    $types = $Tainacan_Exposers->get_types(\OBJECT);
	    $urls = [];
	    foreach ($types as $type) {
	        $url = $base_url.(strpos($base_url, '?') === false ? '?' : '&').self::TYPE_PARAM.'='.$type->slug;
	        $urls[$type->slug] = [$url];
	        if(is_array($type->get_mappers())) {
	            $first = true; // first is default, jump
	            foreach ($type->get_mappers() as $type_mapper) {
	                if($first) {
	                    $first = false;
	                    continue;
	                }
	                $urls[$type->slug][] = $url.'&'.self::MAPPER_PARAM.'='.$type_mapper;
	            }
	        } else {
	            foreach ($mappers as $mapper) {
	                $urls[$type->slug][] = $url.'&'.self::MAPPER_PARAM.'='.$mapper->slug;
	            }
	        }
	    }
	    return $urls;
	}
}