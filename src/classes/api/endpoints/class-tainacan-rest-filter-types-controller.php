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
					'permission_callback' => array($this, 'get_items_permissions_check')
				),
				'schema' => [$this, 'get_list_schema']
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
		$filter_type = new $item();

		$filter_arr = $filter_type->_toArray();

		return $filter_arr;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

		$filter_types = $Tainacan_Filters->fetch_filter_types( );

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
	
	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => $this->rest_base,
			'type' => 'object',
			'tags' => [ $this->rest_base ],
			'properties' =>  [
				'className'       => [
					'type' => 'string',
					'description' => __( 'The name of the class of the filter type, for example "Tainacan\Filter_Types\Checkbox".', 'tainacan' ),
				],
				'name'       => [
					'type' => 'string',
					'description' => __( 'The name for the filter type.', 'tainacan' ),
				],
				'component'       => [
					'type' => 'string',
					'description' => __('The name of the web component used by this filter type, for example "tainacan-filter-checkbox".', 'tainacan')
				],
				'supported_types' => [
					'type'  => 'array',
					'items' => [
						'type' => 'string'
					],
					'description' => __('The list of primitive types supported by this filter type, for example, string, date, term, item.', 'tainacan')
				],
				'options'             => [
					'type' => ['object', 'array'],
					'description' => __('The options of the filter type, for example the "step" in the numeric input field.', 'tainacan'),
				],
				'form_component'      => [
					'type' => ['boolean', 'string'],
					'description' => __('Whether this filter type has a form component where it\'s extra options can be set. If it has, the form web component name will be passed, for example "tainacan-filter-form-numeric-interval"', 'tainacan')
				],
				'use_max_options' => [
					'type' => 'boolean',
					'description' => __('Whether this filter type has a maximum number of options that can be selected.', 'tainacan')
				]
			]
		];

		return $schema;
	}

}

?>