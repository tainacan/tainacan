<?php
namespace Tainacan\Exposers;

use Tainacan\Exposers\Mappers\Mapper;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Load exposers classes
 */ 
class Exposers {
	
	protected $types = [];
	protected $mappers = [];
	private static $instance = null;
	private static $request = null;
	const MAPPER_CLASS_PREFIX = 'Tainacan\Exposers\Mappers\\';
	
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function __construct() {
	    self::$instance = $this;
		$this->register_exposer_type('Tainacan\Exposers\Types\Xml');
		$this->register_exposer_type('Tainacan\Exposers\Types\Txt');
		$this->register_exposer_type('Tainacan\Exposers\Types\Html');
		$this->register_exposer_type('Tainacan\Exposers\Types\Csv');
		$this->register_exposer_type('Tainacan\Exposers\Types\OAI_PMH');
		$this->register_exposer_type('Tainacan\Exposers\Types\JSON_LD');
		do_action('tainacan-register-exposer-types');
		$this->register_exposer_mapper('Tainacan\Exposers\Mappers\Dublin_Core');
		$this->register_exposer_mapper('Tainacan\Exposers\Mappers\Value');
		do_action('tainacan-register-exposer-mappers');
		
		
		add_filter( 'rest_request_after_callbacks', [$this, 'rest_request_after_callbacks'], 10, 3 ); //exposer types
		add_filter( 'tainacan-rest-response', [$this, 'rest_response'], 10, 2 ); // exposer mapper
		add_filter( 'tainacan-admin-i18n', [$this, 'mappers_i18n']);
	}
	
	/**
	 * register exposers types class on array of types
	 *
	 * @param $class_name string | object The class name or the instance
	 */
	public function register_exposer_type( $class_name ){
	    $obj = $class_name;
		if( is_object( $class_name ) ){
			$class_name = get_class( $class_name );
		} else {
		    $obj = new $class_name;
		}
		
		if(!in_array( $class_name, $this->types)){
		    $this->types[$obj->slug] = $class_name;
		}
	}
	
	/**
	 * register exposers mappers class on array of types
	 *
	 * @param $class_name string | object The class name or the object instance
	 */
	public function register_exposer_mapper( $class_name ){
	    $obj = $class_name;
		if( is_object( $class_name ) ){
			$class_name = get_class( $class_name );
		} else {
		    $obj = new $class_name;
		}
		
		if(!in_array( $class_name, $this->mappers)){
			$this->mappers[$obj->slug] = $class_name;
		}
	}
	
	/**
	 * Return namespaced class name 
	 * @param string $class_name
	 * @param boolean $root
	 * @param string $prefix
	 * @return string
	 */
	public function check_class_name($class_name, $root = false, $prefix = 'Tainacan\Exposers\Types\\') {
	    if(is_string($class_name)) {
    	    if(array_key_exists($class_name, $this->types)) {
                $class_name = $this->types[$class_name];
                $prefix = '';
    	    } elseif( array_key_exists($class_name, $this->mappers)) {
    	        $class_name = $this->mappers[$class_name];
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
	 * Return array of mapped metadatum 
	 * @param array $item_arr
	 * @param Mappers\Mapper $mapper
	 * @return array
	 */
	protected function map_metadatum($item_arr, $mapper) {
		$ret = $item_arr;
		$metadatum_mapping = $item_arr['metadatum']['exposer_mapping'];
		if(array_key_exists($mapper->slug, $metadatum_mapping)) {
			if(
			    is_string($metadatum_mapping[$mapper->slug]) && is_array($mapper->metadata) && !array_key_exists( $metadatum_mapping[$mapper->slug], $mapper->metadata) ||
			    is_array($metadatum_mapping[$mapper->slug]) && $mapper->allow_extra_metadata != true
			) {
				throw new \Exception('Invalid Mapper Option');
			}
			$slug = '';
			if(is_string($metadatum_mapping[$mapper->slug])) {
			    $slug = $metadatum_mapping[$mapper->slug];
			} else {
			    $slug = $metadatum_mapping[$mapper->slug]['slug'];
			}
			$ret = [$mapper->prefix.$slug.$mapper->sufix => $item_arr['value']]; //TODO Validate option
		} elseif($mapper->slug == 'value') {
			$ret = [$item_arr['metadatum']['name'] => $item_arr['value']];
		} else {
		    $ret = [];
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param array $item_arr
	 * @param Mappers\Mapper $mapper
	 * @param \WP_REST_Request $resquest
	 * @return array
	 */
	protected function map($item_arr, $mapper, $resquest) {
		$ret = $item_arr;
		if(array_key_exists('metadatum', $item_arr)){ // getting a unique metadatum
			$ret = $this->map_metadatum($item_arr, $mapper);
		} else { // array of elements
			$ret = [];
			foreach ($item_arr as $item) {
				if(array_key_exists('metadatum', $item)) {
					$ret = array_merge($ret, $this->map($item, $mapper, $resquest) );
				} else {
					$ret[] = $this->map($item, $mapper, $resquest);
				}
			}
		}
		return $ret;
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
	        is_array($query_url_params) && array_key_exists('exposer_type', $query_url_params) &&
	        $Tainacan_Exposers->has_type($query_url_params['exposer_type'])
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
    			if($exposer = $this->request_has_type($request)) {
    				$type_responde = $exposer->rest_request_after_callbacks($response, $handler, $request);
    				if(self::request_has_url_param($request)) {
    				    header(implode('', $response->get_headers()));
    				    echo $response->get_data();
    				    exit();
    				}
    				return $type_responde;
    			}
    		} elseif($request->get_method() == 'POST') {
    		    if($mapper = $this->request_has_mapper($request)) {
    		        return $this->create_mapped_metadata( $response, $handler, $request, $mapper );
    		    }
    		}
	    }
		// default JSON response
		return $response;
	}
	
	/**
	 * Return if type is registered
	 * @param string $type
	 * @return boolean
	 */
	public function has_type($type) {
		return in_array($this->check_class_name($type), $this->types);
	}
	/**
	 * Return Type if request has type, false otherwise
	 * @param \WP_REST_Request $request
	 * @return Types\Type|boolean false
	 */
	public static function request_has_type($request) {
		$body = json_decode( $request->get_body(), true );
		$query_url_params = $request->get_query_params();
		$Tainacan_Exposers = self::get_instance();
		if(
    			is_array($body) && array_key_exists('exposer_type', $body) &&
    			$Tainacan_Exposers->has_type($body['exposer_type'])
		) {
			$type = $Tainacan_Exposers->check_class_name($body['exposer_type'], true);
			return new $type;
		} elseif (
                is_array($query_url_params) && array_key_exists('exposer_type', $query_url_params) &&
                $Tainacan_Exposers->has_type($query_url_params['exposer_type'])
        ){
            $type = $Tainacan_Exposers->check_class_name($query_url_params['exposer_type'], true);
		    return new $type;
		}
		return false;
	}
	
	/**
	 * Return if mapper is registered 
	 * @param string $mapper
	 * @return boolean
	 */
	public function has_mapper($mapper) {
		return in_array($this->check_class_name($mapper, false, self::MAPPER_CLASS_PREFIX), $this->mappers);
	}
	
	/**
	 * Check if there is a mapper
	 * @param \WP_REST_Request $request
	 * @return Mappers\Mapper|boolean false
	 */
	public static function request_has_mapper($request) {
		$body = json_decode( $request->get_body(), true );
		$Tainacan_Exposers = self::get_instance();
		$query_url_params = $request->get_query_params();
		
		$type = self::request_has_type($request);
		if( // There are a defined mapper
			is_array($body) && array_key_exists('exposer_map', $body) &&
			$Tainacan_Exposers->has_mapper($body['exposer_map'])
		) {
			if(
				$type === false || // do not have a exposer type
				$type->get_mappers() === true || // the type accept all mappers
				( is_array($type->get_mappers()) && in_array($body['exposer_map'], $type->get_mappers()) )
			) { // the current mapper is accepted by type
				$mapper = $Tainacan_Exposers->check_class_name($body['exposer_map'], true, self::MAPPER_CLASS_PREFIX);
				return new $mapper;
			}
		} elseif(
		    is_array($query_url_params) && array_key_exists('exposer_map', $query_url_params) &&
		    $Tainacan_Exposers->has_mapper($query_url_params['exposer_map'])
		) {
		    if(
		        $type === false || // do not have a exposer type
		        $type->get_mappers() === true || // the type accept all mappers
		        ( is_array($type->get_mappers()) && in_array($query_url_params['exposer_map'], $type->get_mappers()) )
		        ) { // the current mapper is accepted by type
		            $mapper = $Tainacan_Exposers->check_class_name($query_url_params['exposer_map'], true, self::MAPPER_CLASS_PREFIX);
		            return new $mapper;
		    }
		} elseif( is_object($type) && is_array($type->get_mappers()) && count($type->get_mappers()) > 0 ) { //there are no defined mapper, let use the first one o list if has a list
			$mapper = $Tainacan_Exposers->check_class_name($type->get_mappers()[0], true, self::MAPPER_CLASS_PREFIX);
			return new $mapper;
		}
		return false; // No mapper need, using Tainacan defautls
	}
	
	/**
	 * Add mappers data to translations
	 * @param array $i18n_strings
	 * @return array
	 */
	public function mappers_i18n($i18n_strings) {
		foreach ($this->mappers as $mapper) {
			$obj = new $mapper;
			$i18n_strings[$obj->slug] = $obj->slug; // For url breadcrumb translations
			$i18n_strings[$obj->name] = $obj->name;
		}
		return $i18n_strings;
	}
	
	/**
	 * Return list of registered mappers 
	 * @param string $output output format, ARRAY_N or OBJECT
	 */
	public function get_mappers($output = ARRAY_N) {
		$ret = [];
		switch ($output) {
			case OBJECT:
				foreach ($this->mappers as $mapper) {
					$ret[] = new $mapper;
				}
			break;
			case ARRAY_N:
			default:
				return $this->mappers;
			break;
		}
		return $ret;
	}
	
	/**
	 * Return list of registered types
	 * @param string $output output format, ARRAY_N or OBJECT
	 * @return array of slug or array of \Tainacan\Exposers\Types\Type
	 */
	public function get_types($output = \ARRAY_N) {
	    $ret = [];
	    switch ($output) {
	        case \OBJECT:
	            foreach ($this->types as $type) {
	                $ret[] = new $type;
	            }
	            break;
	        case \ARRAY_N:
	        default:
	            return $this->types;
	            break;
	    }
	    return $ret;
	}
	
	/**
	 * 
	 * @param \WP_REST_Response $response
	 * @param \WP_REST_Server $handler
	 * @param \WP_REST_Request $request
	 * @param Mapper $mapper
	 */
	public function create_mapped_metadata( $response, $handler, $request, $mapper ) {
	    if($response instanceof \WP_REST_Response && $response->get_status() == 201) {
	       $collection_array = $response->get_data();
	       $id = $collection_array['id'];
	       $mapper_metadata = $mapper->metadata;
	       if(is_array($mapper_metadata) ) {
	           $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
	           foreach ($mapper_metadata as $slug => $mapper_metadatum) {
	               if(array_key_exists('core_metadatum', $mapper_metadatum) && $mapper_metadatum['core_metadatum'] != false) continue;
	               
	               $metadatum = new \Tainacan\Entities\Metadatum();
	               if(
	                       array_key_exists('metadata_type', $mapper_metadatum) &&
	                       $mapper_metadatum['metadata_type'] != false &&
	                       class_exists($mapper_metadatum['metadata_type'])
	                   ) {
	                   $metadatum->set_metadata_type($mapper_metadatum['metadata_type']);
	               } else {
	                   $metadatum->set_metadata_type('Tainacan\Metadata_Types\Text');
	               }
	               $metadatum->set_name($mapper_metadatum['label']);
	               $metadatum->set_description($mapper_metadatum['URI']);
	               $metadatum->set_exposer_mapping([
	                   $mapper->slug => $slug
	               ]);
	               $metadatum->set_status('publish');
	               $metadatum->set_collection_id($id);
	               $metadatum->set_slug($slug);
	               if($metadatum->validate()) $Tainacan_Metadata->insert($metadatum);
	           }
	       }
	    }
	    return $response;
	}
	
	/**
	 * 
	 * @param string $base_url url base for exposer parameters append
	 * @return string|string[][]
	 */
	public static function get_exposer_urls($base_url = '') {
	    $Tainacan_Exposers = self::get_instance();
	    $mappers = $Tainacan_Exposers->get_mappers();
	    $types = $Tainacan_Exposers->get_types(\OBJECT);
	    $urls = [];
	    foreach ($types as $type) {
	        $url = $base_url.(strpos($base_url, '?') === false ? '?' : '&').'exposer_type='.$type->slug;
	        $urls[$type->slug] = [$url];
	        if(is_array($type->get_mappers())) {
	            $first = true; // first is default, jump
	            foreach ($type->get_mappers() as $type_mapper) {
	                if($first) {
	                    $first = false;
	                    continue;
	                }
	                $urls[$type->slug][] = $url.'&exposer_map='.$type_mapper;
	            }
	        } else {
	            foreach ($mappers as $type_mapper) {
	                $urls[$type->slug][] = $url.'&exposer_map='.$type_mapper;
	            }
	        }
	    }
	    return $urls;
	}
}