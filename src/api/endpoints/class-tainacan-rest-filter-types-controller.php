<?php

class TAINACAN_REST_Filter_Types_Controller extends TAINACAN_REST_Controller {

	/**
	 * TAINACAN_REST_Filter_Types_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'filter-types';

		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				)
			)
		);
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$name = "\Tainacan\Filter_Types\\$item";
		$filter_type = new $name();

		$filter_arr = $filter_type->__toArray();
		$filter_arr['name'] = $item;

		return $filter_arr;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		global $Tainacan_Filters;

		$filter_types = $Tainacan_Filters->fetch_filter_types('NAME');

		$prepared = [];
		foreach ($filter_types as $filter_type){
			array_push($prepared, $this->prepare_item_for_response($filter_type, $request));
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