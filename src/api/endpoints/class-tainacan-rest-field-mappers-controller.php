<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;

class REST_Field_Mappers_Controller extends REST_Controller {

	/**
	 * REST_Field_Mappers_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'field-mappers';
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

	    $field_arr = $mapper->_toArray();

		return $field_arr;
	}
	
	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_field_for_response( $item, $request ) {
	    if(!empty($item)){
	        $item_arr = $item->_toArray();
	        
	        $item_arr['field_type_object'] = $item->get_field_type_object()->_toArray();
	        
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
		$Tainacan_Exposers = \Tainacan\Exposers\Exposers::get_instance();

		$field_mappers = $Tainacan_Exposers->get_mappers( 'OBJECT' );

		$prepared = [];
		foreach ($field_mappers as $field_mapper){
			array_push($prepared, $this->prepare_item_for_response($field_mapper, $request));
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
	 * Verify if current user has permission to update a fields mappers
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
	           array_key_exists('fields_mappers', $body) &&
	           is_array($body['fields_mappers']) &&
	           count($body['fields_mappers']) > 0 &&
	           array_key_exists('exposer-map', $body)
	    ) {
	        $field_mapper = $body['fields_mappers'][0];
	        $field = \Tainacan\Repositories\Repository::get_entity_by_post($field_mapper['field_id']);
	        if($field instanceof \Tainacan\Entities\Field && $field->can_edit()) {
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
	    $Tainacan_Exposers = \Tainacan\Exposers\Exposers::get_instance();
	    $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
	    $body = json_decode( $request->get_body(), true );
	    if($mapper = $Tainacan_Exposers::request_has_mapper($request)) {
	        if(count($body['fields_mappers']) > 0) {
	            $response = [];
    	        foreach ($body['fields_mappers'] as $field_mapper) {
    	            $field = $Tainacan_Fields->fetch($field_mapper['field_id']);
    	            $exposer_mapping = $field->get('exposer_mapping');
    	            if($field_mapper['mapper_metadata'] == '') {
    	                if(array_key_exists($exposer_mapping, $mapper->slug) ) unset($exposer_mapping[$mapper->slug]);
    	            } else {
    	                $exposer_mapping[$mapper->slug] = $field_mapper['mapper_metadata'];
    	            }
    	            $field->set('exposer_mapping', $exposer_mapping);
    	            if($field->validate()) {
    	               $Tainacan_Fields->update($field);
    	               $response[] = $this->prepare_field_for_response($field, $request);
    	            } else {
    	                return new \WP_REST_Response([
    	                    'error_message' => __('One or more values are invalid.', 'tainacan'),
    	                    'errors'        => $prepared->get_errors(),
    	                    'field'         => $this->prepare_item_for_response($prepared, $request),
    	                ], 400);
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