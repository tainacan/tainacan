<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities\Metadatum;

class REST_Metadatum_Mappers_Controller extends REST_Controller {
	protected function get_schema() {
		return "TODO:get_schema";
	}

	/**
	 * REST_Metadatum_Mappers_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'mappers';
		parent::__construct();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<slug>[a-z0-9-_]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base ,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<slug>[a-z0-9-_]+)' ,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
				),
			)
		);
	}

	/**
	 * @param \Tainacan\Mappers\Mapper $mapper
	 * @param \WP_REST_Request $request
	 *map
	 * @return mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $mapper, $request ) {

		$collection_id = isset($request['collection_id']) && $request['collection_id'] != 'default' ? $request['collection_id'] : null;
		$metadatum_arr = $mapper->_toArray($collection_id);

		return $metadatum_arr;
	}
	
	/**
	 * @param Metadatum $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_metadatum_for_response( $item, $request ) {
		if(!empty($item)){
			$item_arr = ['metadatum_id' => $item->get_id(), 'exposer_mapping' => $item->get('exposer_mapping')];
			return $item_arr;
		}
		return $item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$collection_id = isset($request['collection_id']) ? $request['collection_id'] : 'default';
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);
		$disabled_mappers = isset($collection) ? $collection->get_disabled_mappers() : [];
		$Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();

		$metadatum_mappers = $Tainacan_Mappers->get_mappers( 'OBJECT' );

		$prepared = [];
		foreach ($metadatum_mappers as $metadatum_mapper){
			if($metadatum_mapper->show_ui) {
				$mapper = $this->prepare_item_for_response($metadatum_mapper, $request);
				$mapper['disabled'] = !empty($disabled_mappers) && in_array($metadatum_mapper->slug, $disabled_mappers) ? true : false;
				array_push($prepared, $mapper);
			}
		}
		return new \WP_REST_Response($prepared, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		if( !isset( $request['slug'] ) ) {
			return new \WP_REST_Response([
				'error_message' => __('Slug mapper not informed', 'tainacan'),
			], 422);
		}
		
		$collection_id = isset($request['collection_id']) ? $request['collection_id'] : 'default';
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_id);
		
		$disabled_mappers = isset($collection) ? $collection->get_disabled_mappers() : []; //get_post_meta($collection_id, 'disabled_mappers', false);
		$Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();
		$metadatum_mappers = $Tainacan_Mappers->get_mappers( 'OBJECT' );

		$slug_mapper = $request['slug'];
		$mapper_key = array_search($slug_mapper, array_column($metadatum_mappers, 'slug') );
		if( $mapper_key === false ) {
			return new \WP_REST_Response([
				'error_message' => __('Mapper not found', 'tainacan'),
			], 400);
		}
		$mapper = $metadatum_mappers[$mapper_key];
		$prepared_mapper = $this->prepare_item_for_response($mapper, $request);
		$prepared_mapper['disabled'] = !empty($disabled_mappers) && in_array($slug_mapper, $disabled_mappers) ? true : false;

		return new \WP_REST_Response($prepared_mapper, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}
	
	/**
	 * Verify if current user has permission to update a metadata mappers
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function update_item_permissions_check( $request ) {
		$body = json_decode( $request->get_body(), true );
		if(
			   is_array($body) &&
			   array_key_exists('metadata_mappers', $body) &&
			   is_array($body['metadata_mappers']) &&
			   count($body['metadata_mappers']) > 0
		) {
			$metadatum_mapper = $body['metadata_mappers'][0];
			$metadatum = \Tainacan\Repositories\Repository::get_entity_by_post($metadatum_mapper['metadatum_id']);
			if ($metadatum instanceof \Tainacan\Entities\Metadatum && $metadatum->can_edit()) {
				return true;
			}
		}
		return true;
	}
	
	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
		if( !isset( $request['slug'] ) ) {
			return new \WP_REST_Response([
				'error_message' => __('Slug mapper not informed', 'tainacan'),
			], 422);
		}
		$slug_mapper = $request['slug'];
		$Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
		if ($Tainacan_Mappers->mapper_exists($slug_mapper)) {
			$mapper = $Tainacan_Mappers::get_mapper_by_slug($slug_mapper);
			$body = json_decode( $request->get_body(), true );
			
			if( !isset($body) || empty($body) ) {
				return new \WP_REST_Response([
					'error_message' => __('Body cannot be empty.', 'tainacan'),
					'item'          => $request->get_body()
				], 400);
			}
			
			$response = [
				'mapper' => $mapper->_toArray(isset($request['collection_id']) && $request['collection_id'] != 'default' ? $request['collection_id'] : null),
				'mapping'=>[]
			];
			if (count($body['metadata_mappers']) > 0) {
				foreach ($body['metadata_mappers'] as $metadatum_mapper) {
					$metadatum_mapper['metadatum_id'] = intval($metadatum_mapper['metadatum_id']);
					if (is_array($metadatum_mapper['mapper_metadata'])) {
						foreach ($metadatum_mapper['mapper_metadata'] as $k => $tag) {
							$metadatum_mapper['mapper_metadata'][$k] = esc_attr($tag);
						}
					} else {
						$metadatum_mapper['mapper_metadata'] = sanitize_text_field($metadatum_mapper['mapper_metadata']);
					}
					$isSaved = false;
					$metadatum = $Tainacan_Metadata->fetch($metadatum_mapper['metadatum_id']);
					$exposer_mapping = $metadatum->get('exposer_mapping');
					if ($metadatum_mapper['mapper_metadata'] == '') {
						if(array_key_exists($mapper->slug, $exposer_mapping) ) {
							unset($exposer_mapping[$mapper->slug]);
							$isSaved = true;
						}
					} else {
						if( !array_key_exists($mapper->slug, $exposer_mapping) || $exposer_mapping[$mapper->slug] != $metadatum_mapper['mapper_metadata']) {
							$exposer_mapping[$mapper->slug] = $metadatum_mapper['mapper_metadata'];
							$isSaved = true;
						}
					}
					if ($isSaved) {
						$metadatum->set('exposer_mapping', $exposer_mapping);
						if($metadatum->validate()) {
						   $Tainacan_Metadata->update($metadatum);
						} else {
							return new \WP_REST_Response([
								'error_message' => __('One or more values are invalid.', 'tainacan'),
								'errors'        => $metadatum->get_errors(),
								'mapping'       => $this->prepare_item_for_response($metadatum, $request),
							], 400);
						}
					}
					$response['mapping'][] = $this->prepare_metadatum_for_response($metadatum, $request);
				}
			}
			if (isset($request["collection_id"]) && isset($body["disabled"])) {
				$collection_id = $request["collection_id"];
				$disabled = $body["disabled"];
				$collections_repository = \Tainacan\Repositories\Collections::get_instance();
				$collection = $collections_repository->fetch($collection_id);
				if ($collection) {
					$disabled_mappers = $collection->get_disabled_mappers();
					if ($disabled == true && !in_array($slug_mapper, $disabled_mappers)) {
						$disabled_mappers[] = $slug_mapper;
					} elseif (
						$disabled == false && (($key = array_search($slug_mapper, $disabled_mappers)) !== false)
					) {
						unset($disabled_mappers[$key]);
					}
					$collection->set_disabled_mappers($disabled_mappers);
					if ($collection->validate()) {
						$collections_repository->update($collection);
					}
					$response['disabled'] = $disabled;
				}
			}
		} else {
			return new \WP_REST_Response([
				'error_message' => __('Mapper not found', 'tainacan'),
			], 400);
		}
		return new \WP_REST_Response($response, 200);
	}
	
}

?>