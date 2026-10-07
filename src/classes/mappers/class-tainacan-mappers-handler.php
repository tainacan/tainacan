<?php 

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

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
		
		add_action('init', array(&$this, 'init'));
		
		// @deprecated, we shall use tainacan-i18n now.
		add_filter( 'tainacan-admin-i18n', [$this, 'mappers_i18n']);

		add_filter( 'tainacan-i18n', [$this, 'mappers_i18n']);
		
		add_action('tainacan-api-collection-created', [$this, 'create_mapped_collection'], 10, 2);
		
		add_filter('tainacan-api-items-prepare-for-response', [$this, 'filter_item_api_response'], 10, 3);
		
	}
	
	function init() {
		$this->register_mapper('Tainacan\Mappers\Dublin_Core');
			$this->register_mapper('Tainacan\Mappers\Inbcm_Archive');
			$this->register_mapper('Tainacan\Mappers\Inbcm_Bibliographic');
			$this->register_mapper('Tainacan\Mappers\Inbcm_Museological');
		
		do_action('tainacan-register-mappers', $this);
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
	 * Normalize a metadatum exposer mapping into slug, URI and label.
	 *
	 * Built-in mapper fields are stored as strings (e.g. 'dc:title'). Extra mapper
	 * fields, allowed when the mapper has allow_extra_metadata, are stored as arrays
	 * with 'slug', 'uri' and 'label' keys. Callers that treat the mapping as a string
	 * (array keys, esc_attr, XML element names) must go through this helper.
	 *
	 * @param mixed               $mapping The value stored in exposer_mapping[mapper_slug].
	 * @param Mappers\Mapper|null $mapper  Optional mapper used to resolve URI and label for string mappings.
	 * @return array{slug: string, uri: string, label: string}|false
	 */
	public function normalize_mapping_value( $mapping, $mapper = null ) {
		if ( is_array( $mapping ) ) {
			$slug = isset( $mapping['slug'] ) ? (string) $mapping['slug'] : '';
			if ( '' === $slug ) {
				return false;
			}

			return [
				'slug'  => $slug,
				'uri'   => isset( $mapping['uri'] ) ? (string) $mapping['uri'] : '',
				'label' => isset( $mapping['label'] ) ? (string) $mapping['label'] : '',
			];
		}

		if ( ! is_string( $mapping ) || '' === $mapping ) {
			return false;
		}

		$uri   = '';
		$label = '';
		if ( $mapper instanceof Mappers\Mapper ) {
			$uri = $mapper->get_url( $mapping );
			if ( is_array( $mapper->metadata ) && isset( $mapper->metadata[ $mapping ]['label'] ) ) {
				$label = $mapper->metadata[ $mapping ]['label'];
			}
		}

		return [
			'slug'  => $mapping,
			'uri'   => $uri,
			'label' => $label,
		];
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
		$data_body = $request->get_body() ?? '';
		$body = json_decode( $data_body, true );
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
			$normalized = $this->normalize_mapping_value( $metadatum_mapping[ $mapper->slug ], $mapper );
			if ( ! $normalized ) {
				return [];
			}
			$ret = [ $mapper->prefix . $normalized['slug'] . $mapper->sufix => $item_arr['value'] ]; //TODO Validate option
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
		
		$mapped_meta = [];
		
		foreach ($item_arr['metadata'] as $slug => $meta) {
			
			if ( array_key_exists($mapper->slug, $meta['mapping']) ) {
				$normalized = $this->normalize_mapping_value( $meta['mapping'][ $mapper->slug ], $mapper );
				if ( ! $normalized ) {
					continue;
				}

				$mapped_slug = $normalized['slug'];
				$mapped_meta[ $mapped_slug ] = $meta;
				$mapped_meta[ $mapped_slug ]['semantic_uri'] = $normalized['uri'];
				$mapped_meta[ $mapped_slug ]['name'] = $normalized['label'];
				$mapped_meta[ $mapped_slug ]['slug'] = $mapped_slug;
			}
			
		}
		
		$item_arr['metadata'] = $mapped_meta;
		
		return $item_arr;
		
		
	}
	
	
	
}
