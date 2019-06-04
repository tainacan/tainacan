<?php 

namespace Tainacan;

class Mappers_Handler {
	
	private static $instance = null;
	
	protected $mappers = [];
	
	const MAPPER_CLASS_PREFIX = 'Tainacan\Mappers\\';
	const MAPPER_PARAM = 'mapper';
	
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function __construct() {
	    self::$instance = $this;

		$this->register_mapper('Tainacan\Mappers\Dublin_Core');
		
		do_action('tainacan-register-mappers', $this);
		
		add_filter( 'tainacan-admin-i18n', [$this, 'mappers_i18n']);
		
		add_action('tainacan-api-collection-created', [$this, 'create_mapped_collection'], 10, 2);
		
		add_filter('tainacan-api-items-prepare-for-response', [$this, 'filter_item_api_response'], 10, 3);
		
	}
	
	/**
	 * register mappers class 
	 *
	 * @param $class_name string | object The class name or the object instance
	 */
	public function register_mapper( $class_name ){
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
	 * unregister mappers class 
	 *
	 * @param $class_name string | object The class name or the object instance
	 */
	public function unregister_mapper( $class_name ){
	    $obj = $class_name;
		if( is_object( $class_name ) ){
			$class_name = get_class( $class_name );
		} else {
		    $obj = new $class_name;
		}
		
		if ( array_key_exists($obj->slug, $this->mappers) ) {
			unset($this->mappers[$obj->slug]);
		}
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
	 * Get a mapper object by its slug
	 * @param false|\Tainacan\Mappers\Mapper False or Object 
	 */
	public function get_mapper($slug) {
		
		if ( $this->mapper_exists($slug) ) {

			$mappers = $this->get_mappers();

			$className = $mappers[$slug];

			return new $className();

		}

		return false;
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
	 * Check if there is a mapper
	 * @param \WP_REST_Request $request
	 * @return Mappers\Mapper|boolean false
	 */
	public static function get_mapper_from_request($request) {
		$body = json_decode( $request->get_body(), true );
		$Tainacan_Mappers = self::get_instance();
		$query_url_params = $request->get_query_params();
		
		$return_mapper = false;

		if( // There is a defined mapper
			is_array($body) && array_key_exists(self::MAPPER_PARAM, $body) &&
			$Tainacan_Mappers->mapper_exists($body[self::MAPPER_PARAM])
		) {
			$mapper = $Tainacan_Mappers->check_class_name($body[self::MAPPER_PARAM], true, self::MAPPER_CLASS_PREFIX);
			$return_mapper = new $mapper;
		} elseif(
		    is_array($query_url_params) && array_key_exists(self::MAPPER_PARAM, $query_url_params) &&
		    $Tainacan_Mappers->mapper_exists($query_url_params[self::MAPPER_PARAM])
		) {
			$mapper = $Tainacan_Mappers->check_class_name($query_url_params[self::MAPPER_PARAM], true, self::MAPPER_CLASS_PREFIX);
			$return_mapper = new $mapper;
		} 
		return apply_filters('tainacan-get-mapper-from-request', $return_mapper, $request); 
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
	 * Return if mapper is registered 
	 * @param string $mapper
	 * @return boolean
	 */
	public function mapper_exists($mapper) {
		return in_array($this->check_class_name($mapper, false, self::MAPPER_CLASS_PREFIX), $this->mappers);
	}
	
	/**
	 * Return namespaced class name 
	 * @param string $class_name
	 * @param boolean $root
	 * @param string $prefix
	 * @return string
	 */
	public function check_class_name($class_name, $root = false, $prefix = 'Tainacan\Mapper\\') {
	    if(is_string($class_name)) {
    	    if( array_key_exists($class_name, $this->mappers)) {
    	        $class_name = $this->mappers[$class_name];
    	        $prefix = '';
    	    }
	    }
		$class = $prefix.sanitize_text_field($class_name);
		$class = str_replace(['-', ' '], ['_', '_'], $class);
		
		return ($root ? '\\' : '').$class;
	}
	
	
	/**
	 * 
	 * @param array $collection collection passed by the collections API endpoint
	 * @param \WP_REST_Request $request
	 */
	public function create_mapped_collection( $collection, $request ) {
	    
		
		if ($mapper = $this->get_mapper_from_request($request)) {
			
			$mapper_metadata = $mapper->metadata;
			if(is_array($mapper_metadata) ) {
				
				$id = $collection['id'];
				$collection_object = \Tainacan\Repositories\Collections::get_instance()->fetch($id);
				
				$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
				foreach ($mapper_metadata as $slug => $mapper_metadatum) {
					if( array_key_exists('core_metadatum', $mapper_metadatum) ) {
						$method = 'get_core_' . $mapper_metadatum['core_metadatum'] . '_metadatum';
						if (method_exists($collection_object, $method)) {
							$core_meta = $collection_object->$method();
							if ( ! $core_meta ) {
								$Tainacan_Metadata->register_core_metadata( $collection_object, true );
								$core_meta = $collection_object->$method();
							}
							$_meta_mapping = $core_meta->get_exposer_mapping();
							$_meta_mapping[$mapper->slug] = $slug;
							$core_meta->set_exposer_mapping($_meta_mapping);
							if ($core_meta->validate()) {
								$Tainacan_Metadata->insert($core_meta);
							}
						}
						continue;
					}
					
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
					$metadatum->set_semantic_uri($mapper->get_url($slug));
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

	}
	
	function filter_item_api_response($item_arr, $item, $request) {
		
		$mapper = $this->get_mapper_from_request($request);
		
		if (!$mapper) {
			return $item_arr;
		}
		
		$mapper_meta = $mapper->metadata;
		$mapped_meta = [];
		
		foreach ($item_arr['metadata'] as $slug => $meta) {
			
			if ( array_key_exists($mapper->slug, $meta['mapping']) ) {
				$mapped_slug = $meta['mapping'][$mapper->slug]; 
				
				// Extra metadata
				if ( is_array($mapped_slug) ) {
					$url = $mapped_slug['uri'];
					$label = $mapped_slug['label'];
					$mapped_slug = $mapped_slug['slug'];
				} else {
					$url = $mapper->get_url( $mapped_slug );
					$label = $mapper->metadata[$mapped_slug]['label'];
				}
				
				 
				$mapped_meta[ $mapped_slug ] = $meta;
				$mapped_meta[ $mapped_slug ]['semantic_uri'] = $url;
				$mapped_meta[ $mapped_slug ]['name'] = $label;
				$mapped_meta[ $mapped_slug ]['slug'] = $mapped_slug;
			}
			
		}
		
		$item_arr['metadata'] = $mapped_meta;
		
		return $item_arr;
		
		
	}
	
	
	
}