<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities\Metadatum;

class REST_Metadatum_Mappers_Controller extends REST_Controller {

	/**
	 * REST_Metadatum_Mappers_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'metadatum-mappers';
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
			    array(
			        'methods'             => \WP_REST_Server::EDITABLE,
			        'callback'            => array($this, 'update_item'),
			        'permission_callback' => array($this, 'update_item_permissions_check'),
			    ),
			)
		);
	}

	/**
	 * @param \Tainacan\Exposers\Mappers\Mapper $mapper
	 * @param \WP_REST_Request $request
	 *map
	 * @return mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $mapper, $request ) {

	    $metadatum_arr = $mapper->_toArray();

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
	        $item_arr = ['field_id' => $item->get_id(), 'exposer_mapping' => $item->get('exposer_mapping')];
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
		$Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();

		$metadatum_mappers = $Tainacan_Mappers->get_mappers( 'OBJECT' );

		$prepared = [];
		foreach ($metadatum_mappers as $metadatum_mapper){
		    if($metadatum_mapper->show_ui) array_push($prepared, $this->prepare_item_for_response($metadatum_mapper, $request));
		}

		return new \WP_REST_Response($prepared, 200);
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
	           count($body['metadata_mappers']) > 0 &&
	           \Tainacan\Mappers_Handler::get_mapper_from_request($request)
	    ) {
	        $metadatum_mapper = $body['metadata_mappers'][0];
	        $metadatum = \Tainacan\Repositories\Repository::get_entity_by_post($metadatum_mapper['metadatum_id']);
	        if($metadatum instanceof \Tainacan\Entities\Metadatum && $metadatum->can_edit()) {
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
	    $Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();
	    $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
	    $body = json_decode( $request->get_body(), true );
	    if($mapper = $Tainacan_Mappers::get_mapper_from_request($request)) {
	        if(count($body['metadata_mappers']) > 0) {
	            $response = [];
	            $saved = [];
    	        foreach ($body['metadata_mappers'] as $metadatum_mapper) {
    	            $metadatum_mapper['metadatum_id'] = intval($metadatum_mapper['metadatum_id']);
    	            if(is_array($metadatum_mapper['mapper_metadata'])) {
	                    foreach ($metadatum_mapper['mapper_metadata'] as $k => $tag) {
	                        $metadatum_mapper['mapper_metadata'][$k] = esc_attr($tag);
	                    }
    	            } else {
    	                $metadatum_mapper['mapper_metadata'] = sanitize_text_field($metadatum_mapper['mapper_metadata']);
    	            }
    	            $isSaved = false;
    	            $metadatum = $Tainacan_Metadata->fetch($metadatum_mapper['metadatum_id']);
    	            $exposer_mapping = $metadatum->get('exposer_mapping');
    	            if($metadatum_mapper['mapper_metadata'] == '') {
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
    	            if($isSaved) {
        	            $metadatum->set('exposer_mapping', $exposer_mapping);
        	            if($metadatum->validate()) {
        	               $Tainacan_Metadata->update($metadatum);
        	               $response[] = $this->prepare_metadatum_for_response($metadatum, $request);
        	            } else {
        	                return new \WP_REST_Response([
        	                    'error_message' => __('One or more values are invalid.', 'tainacan'),
        	                    'errors'        => $prepared->get_errors(),
        	                    'metadatum'         => $this->prepare_item_for_response($prepared, $request),
        	                ], 400);
        	            }
    	            }
    	        }
	        
	            return new \WP_REST_Response($response, 200);
	        }
	    }
	    return new \WP_REST_Response([
	        'error_message' => __('Body can not be empty.', 'tainacan'),
	        'item'          => $request->get_body()
	    ], 400);
	}
	
}

?>