<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;

class REST_Metadata_Types_Controller extends REST_Controller {

	/**
	 * REST_Metadata_Types_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'metadata-types';
		parent::__construct();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => [
						'metadata-type' => [
							'description' => __('Returns the structure of the objects.'),
							'items'       => [
								'errors'              => [
									'type' => 'string'
								],
								'component'           => [
									'type' => 'string'
								],
								'primitive_type'      => [
									'type'  => 'array/string',
									'items' => [
										'type' => 'string'
									]
								],
								'related_mapped_prop' => [
									'type' => 'boolean'
								],
								'options'             => [
									'type' => 'array'
								],
								'className'           => [
									'type' => 'string'
								],
								'core'                => [
									'type' => 'boolean'
								],
								'form_component'      => [
									'type' => 'boolean'
								],
								'preview_template'    => [
									'type' => 'string'
								],
							]
						]
					]
				)
			)
		);
	}

	/**
	 * @param $item
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$name = "\Tainacan\Metadata_Types\\$item";
		$metadata_type = new $name();

		$metadatum_arr = $metadata_type->_toArray();

		return $metadatum_arr;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		$metadata_types = $Tainacan_Metadata->fetch_metadata_types('NAME');

		$prepared = [];
		foreach ($metadata_types as $metadata_type){
			array_push($prepared, $this->prepare_item_for_response($metadata_type, $request));
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
}

?>