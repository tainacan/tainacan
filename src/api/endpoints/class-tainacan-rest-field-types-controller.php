<?php

class TAINACAN_REST_Field_Types_Controller extends TAINACAN_REST_Controller {

	/**
	 * TAINACAN_REST_Field_Types_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'field-types';

		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => [
						'field-type' => [
							'description' => __('The structure of objects returned.'),
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
								]
							]
						]
					]
				)
			)
		);
	}

	/**
	 * @param $item
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$name = "\Tainacan\Field_Types\\$item";
		$field_type = new $name();

		$field_arr = $field_type->__toArray();
		$field_arr['name'] = $item;

		return $field_arr;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		global $Tainacan_Fields;

		$field_types = $Tainacan_Fields->fetch_field_types('NAME');

		$prepared = [];
		foreach ($field_types as $field_type){
			array_push($prepared, $this->prepare_item_for_response($field_type, $request));
		}

		return new WP_REST_Response($prepared, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}
}

?>