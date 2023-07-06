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
				),
				'schema' => [$this, 'get_list_schema']
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
		$metadata_type = new $item();

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

		$metadata_types = $Tainacan_Metadata->fetch_metadata_types( );

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

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => $this->rest_base,
			'type' => 'object',
			'tags' => [ $this->rest_base ],
			'properties' => [
				'name'				=> [
					'type' => 'string',
					'description' => __('The name of the metadata type.', 'tainacan')
				],
				'description' => [
					'type' => 'string',
					'description' => __('The description of the metadata type.', 'tainacan')
				],
				'errors'              => [
					'type' => 'string',
					'description' => __('Validation errors for the metadata type.', 'tainacan')
				],
				'component'           => [
					'type' => 'string',
					'description' => __('The name of the web component used by this metadata type, for example "tainacan-taxonomy".', 'tainacan')
				],
				'primitive_type'      => [
					'type'  => ['array','string'],
					'items' => [
						'type' => 'string'
					],
					'description' => __('The primitive type of the metadata type, how it is saved in the WordPress database. For example "string" or "term".', 'tainacan'),
				],
				'related_mapped_prop' => [
					'type' => ['boolean', 'string'],
					'description' => __('Whether this metadata type is related to a mapped property from the post original data.', 'tainacan')
				],
				'options'             => [
					'type' => ['object', 'array'],
					'description' => __('The options of the metadata type, for example the related taxonomy id in the Taxonomy metadata type.', 'tainacan'),
				],
				'className'           => [
					'type' => 'string',
					'description' => __('The name of the class of the metadata type, for example "Tainacan\Metadata_Types\Taxonomy".', 'tainacan')
				],
				'core'                => [
					'type' => 'boolean',
					'description' => __('Whether this metadata type is a core metadata type.', 'tainacan')
				],
				'form_component'      => [
					'type' => ['boolean', 'string'],
					'description' => __('Whether this metadata type has a form component where it\'s extra options can be set. If it has, the form web component name will be passed, for example "tainacan-form-taxonomy"', 'tainacan')
				],
				'preview_template'    => [
					'type' => 'string',
					'description' => __('An HTML template representing a version of the metadata field that is visible inside a tooltip for demonstrating the metadata type.', 'tainacan')
				],
				'sortable'    => [
					'type' => 'boolean',
					'description' => __('Whether this metadata type will generate metadata that should be available as sorting options in the items list interface.', 'tainacan')
				]
			],
		];

		return $schema;
    }
	
}

?>