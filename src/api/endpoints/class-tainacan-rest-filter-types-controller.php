<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;

class REST_Filter_Types_Controller extends REST_Controller {

	/**
	 * REST_Filter_Types_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'filter-types';
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
						'filter-type' => [
							'description' => __('Returns the structure of the objects.'),
							'items'       => [
								'className'       => [
									'type' => 'string'
								],
								'component'       => [
									'type' => 'string'
								],
								'supported_types' => [
									'type'  => 'array',
									'items' => [
										'type' => 'string'
									]
								]
							]
						]
					]
				)
			)
		);
	}

	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$name = "\Tainacan\Filter_Types\\$item";
		$filter_type = new $name();

		$filter_arr = $filter_type->_toArray();
		$filter_arr['name'] = $item;

		return $filter_arr;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

		$filter_types = $Tainacan_Filters->fetch_filter_types('NAME');

		$prepared = [];
		foreach ($filter_types as $filter_type){
			array_push($prepared, $this->prepare_item_for_response($filter_type, $request));
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