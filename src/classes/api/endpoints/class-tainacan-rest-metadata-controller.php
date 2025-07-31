<?php

namespace Tainacan\API\EndPoints;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Metadata_Controller extends REST_Controller {
	private $collection_repository;
	private $metadatum_repository;

	public function __construct() {
		$this->rest_base = 'metadata';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 *
	 * @throws \Exception
	 */
	public function init_objects() {
		$this->metadatum_repository = Repositories\Metadata::get_instance();
		$this->collection_repository = Repositories\Collections::get_instance();
	}

	/**
	 * If POST on metadatum/collection/<collection_id>, then
	 * a metadatum will be created in matched collection and all your item will receive this metadatum
	 *
	 * If POST on metadatum/item/<item_id>, then a value will be added in a metadatum and metadatum passed
	 * id body of requisition
	 *
	 * Both of GETs return the metadatum of matched objects
	 *
	 * @throws \Exception
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<metadatum_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => [
						'collection_id' => [
							'description' => __( 'Collection ID', 'tainacan' ),
							'required' => true,
						],
						'metadatum_id' => [
							'description' => __( 'Metadatum ID', 'tainacan' ),
							'required' => true,
						],
						'context' => array(
							'type'    	  => 'string',
							'default' 	  => 'view',
							'description' => 'The context in which the request is made.',
							'enum'    	  => array(
								'view',
								'edit'
							)
						),
					],
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE, true)
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::DELETABLE, true)
				),
				'schema'                  => [$this, 'get_schema']
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => array_merge(
						array(
							'collection_id' => [
								'description' => __( 'Collection ID', 'tainacan' ),
								'required' => true,
							],
						),
						$this->get_wp_query_params()
					),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE, true),
				),
				'schema'                  => [$this, 'get_list_schema']
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_wp_query_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
				),
				'schema'                  => [$this, 'get_list_schema'],
			)
		);
		register_rest_route($this->namespace, '/'. $this->rest_base . '/(?P<metadatum_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => [
						'metadatum_id' => [
							'description' => __( 'Metadatum ID', 'tainacan' ),
							'required' => true,
						],
					],
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE)
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::DELETABLE)
				),
				'schema'                  => [$this, 'get_schema'],
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$metadatum_id = $request['metadatum_id'];

		$result = $this->metadatum_repository->fetch($metadatum_id, 'OBJECT');

		if (! $result instanceof Entities\Metadatum) {
			return new \WP_REST_Response([
				'error_message' => __('Metadata with this ID was not found', 'tainacan'),
				'item_id' => $metadatum_id
			], 400);
		}

		return new \WP_REST_Response($this->prepare_item_for_response($result, $request), 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_item_permissions_check( $request ) {
		$metadatum = $this->metadatum_repository->fetch($request['metadatum_id']);

		if ( $metadatum instanceof Entities\Metadatum ) {
			return $metadatum->can_read();
		}

		return false;
	}

	/**
	 * @param String $request
	 *
	 * @param null $collection_id
	 *
	 * @return object|void|\WP_Error
	 * @throws \Exception
	 */
	public function prepare_item_for_database( $request, $collection_id = null ) {
		$metadatum = new Entities\Metadatum();

		$meta = json_decode( $request, true );
		foreach ( $meta as $key => $value ) {
			$set_ = 'set_' . $key;
			$metadatum->$set_( $value );
		}

		if($collection_id) {
			$collection = new Entities\Collection( $collection_id );
			$metadatum->set_collection( $collection );
		} else {
			$metadatum->set_collection_id( 'default' );
		}

		return $metadatum;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function create_item( $request ) {
		if(!empty($request->get_body()) && isset($request['collection_id'])){
			$collection_id = $request['collection_id'];

			try {
				$prepared = $this->prepare_item_for_database( $request->get_body(), $collection_id );
			} catch (\Exception $exception){
				return new \WP_REST_Response($exception->getMessage(), 400);
			}

			if($prepared->validate()) {
				$metadatum = $this->metadatum_repository->insert( $prepared);

				$response = $this->prepare_item_for_response($metadatum, $request);

				return new \WP_REST_Response($response, 201);
			} else {
				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared->get_errors(),
					'metadatum'         => $this->prepare_item_for_response($prepared, $request),
				], 400);
			}
		} elseif (!empty($request->get_body())) {
			try {
				$prepared = $this->prepare_item_for_database( $request->get_body() );
			} catch ( \Exception $exception ) {
				return new \WP_REST_Response( $exception->getMessage(), 400 );
			}

			if ( $prepared->validate() ) {
				$metadatum = $this->metadatum_repository->insert( $prepared );

				$response = $this->prepare_item_for_response($metadatum, $request);

				return new \WP_REST_Response($response, 201);
			} else {
				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared->get_errors(),
					'metadatum'         => $this->prepare_item_for_response($prepared, $request),
				], 400);
			}

		}

		return new \WP_REST_Response([
			'error_message' => __('Body cannot be empty.', 'tainacan'),
			'item'          => $request->get_body()
		], 400);

	}

	/**
	 * @param $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function create_item_permissions_check( $request ) {

		if( isset($request['collection_id']) ) {
			$collection = $this->collection_repository->fetch( $request['collection_id'] );

			if ( $collection instanceof Entities\Collection ) {
				return $collection->user_can( 'edit_metadata' );
			}

		} else {

			return current_user_can( 'tnc_rep_edit_metadata' );

		}

		return false;

	}

	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)){
			$item_arr = $item->_toArray();
			$item_arr['metadata_type_object'] = $item->get_metadata_type_object()->_toArray();

			if ( isset($request['include_options_as_html']) && $request['include_options_as_html'] == 'yes' )
				$item_arr['options_as_html'] = $item->get_metadata_type_object()->get_options_as_html();

			if ( isset($item_arr['metadata_type_options']) && isset($item_arr['metadata_type_options']['taxonomy_id']) ) {
				$taxonomy = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id( $item_arr['metadata_type_options']['taxonomy_id'] );
				//$taxonomy = new Entities\Taxonomy($item_arr['metadata_type_options']['taxonomy_id']);
				//$item_arr['metadata_type_options']['taxonomy'] = $taxonomy->get_db_identifier();
				$item_arr['metadata_type_options']['taxonomy'] = $taxonomy;
			}

			if ($request['context'] === 'edit') {
				$item_arr['current_user_can_edit'] = $item->can_edit();
				$item_arr['current_user_can_delete'] = $item->can_delete();
				ob_start();
				$item->get_metadata_type_object()->form();
				$form = ob_get_clean();
				$item_arr['edit_form'] = $form;
				$item_arr['enabled'] = $item->get_enabled_for_collection();

				if(isset($item_arr['metadata_type_options']) && isset($item_arr['metadata_type_options']['children_objects'])) {
					foreach ($item_arr['metadata_type_options']['children_objects'] as $index => $children) {
						$item_arr['metadata_type_options']['children_objects'][$index]['current_user_can_edit'] = $item->can_edit();
						$item_arr['metadata_type_options']['children_objects'][$index]['current_user_can_delete'] = $item->can_delete();
					}
				}
			}

			/**
			 * Use this filter to add additional post_meta to the api response
			 * Use the $request object to get the context of the request and other variables
			 * For example, id context is edit, you may want to add your meta or not.
			 *
			 * Also take care to do any permissions verification before exposing the data
			 */
			$extra_metadata = apply_filters('tainacan-api-response-metadatum-meta', [], $request);

			foreach ($extra_metadata as $extra_meta) {
				$item_arr[$extra_meta] = get_post_meta($item_arr['id'], $extra_meta, true);
			}
			$item_arr['inherited'] = $item_arr['collection_id'] != $request['collection_id'];

			return $item_arr;
		}

		return $item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {
		if(isset($request['collection_id'])) {
			$collection_id = $request['collection_id'];

			$args = $this->prepare_filters( $request );

			if ($request['include_disabled'] === 'true') {
				$args['include_disabled'] = true;
			}

			if ($request['include_control_metadata_types'] === 'true') {
				$args['include_control_metadata_types'] = true;
			}

			$collection = new Entities\Collection( $collection_id );

			$result = $this->metadatum_repository->fetch_by_collection( $collection, $args );
		} else {
			$args = [
				'meta_query' => [
					[
						'key'     => 'collection_id',
						'value'   => 'default',
						'compare' => '='
					]
				]
			];

			if ($request['include_control_metadata_types'] === 'true') {
				$args['include_control_metadata_types'] = true;
			}

			$result = $this->metadatum_repository->fetch( $args, 'OBJECT' );
		}

		$prepared_item = [];
		foreach ( $result as $item ) {
			$prepared_item[] = $this->prepare_item_for_response( $item, $request );
		}

		return new \WP_REST_Response($prepared_item, 200);
	} 

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_items_permissions_check( $request ) {


		if(!isset($request['collection_id'])) {
			return true;
		}

		$collection = $this->collection_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection){
			if ( ! $collection->can_read() ) {
				return false;
			}

			return true;
		}

		return false;

	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {
		$metadatum_id = $request['metadatum_id'];

		$metadatum = $this->metadatum_repository->fetch($metadatum_id);

		if (! $metadatum instanceof Entities\Metadatum) {
			return new \WP_REST_Response([
				'error_message' => __('Metadata with this ID was not found', 'tainacan'),
				'item_id' => $metadatum_id
			], 400);
		}

		$metadatum_trashed = $this->metadatum_repository->trash($metadatum);

		$prepared = $this->prepare_item_for_response($metadatum_trashed, $request);

		return new \WP_REST_Response($prepared, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function delete_item_permissions_check( $request ) {
		$metadatum = $this->metadatum_repository->fetch($request['metadatum_id']);

		if ($metadatum instanceof Entities\Metadatum) {
			return $metadatum->can_delete();
		}

		return false;

	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
		$collection_id = is_numeric($request['collection_id']) ? $request['collection_id'] : null;
		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$attributes = [];

			$metadatum_id = $request['metadatum_id'];
			$confirm_repository = false;
			foreach ($body as $att => $value){
				if ($att === "repository_level" && $value === "yes") {
					$confirm_repository = true;
					continue;
				}
				$attributes[$att] = $value;
			}

			$metadatum = $this->metadatum_repository->fetch($metadatum_id);

			if ($metadatum) {
				// These conditions are for verify if endpoints are used correctly
				if(!$collection_id && $metadatum->get_collection_id() !== 'default') {
					$error_message = __('This metadata is not a default metadata', 'tainacan');

					return new \WP_REST_Response( [
						'error_message' => $error_message,
						'metadatum_id'  => $metadatum_id
					], 400 );
				} elseif ($collection_id && $metadatum->get_collection_id() === 'default'){
					$error_message = __('This metadata is not a collection metadata', 'tainacan');

					return new \WP_REST_Response( [
						'error_message' => $error_message,
						'metadatum_id'  => $metadatum_id
					], 400 );
				}

				if (isset($request['repository_level']) && $confirm_repository) {
					$attributes['collection_id'] = "default";
				}

				$prepared_metadata = $this->prepare_item_for_updating($metadatum, $attributes);

				if($prepared_metadata->validate()){
					$updated_metadata = $this->metadatum_repository->update($prepared_metadata, $attributes);

					$response = $this->prepare_item_for_response($updated_metadata, $request);

					return new \WP_REST_Response($response, 200);
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_metadata->get_errors(),
					'metadatum'     => $this->prepare_item_for_response($prepared_metadata, $request)
				], 400);
			}

			$error_message = __('Metadata with this ID was not found', 'tainacan');
			return new \WP_REST_Response( [
				'error_message' => $error_message,
				'metadatum_id'  => $metadatum_id
			] );
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function update_item_permissions_check( $request ) {
		$metadatum = $this->metadatum_repository->fetch($request['metadatum_id']);

		if ($metadatum instanceof Entities\Metadatum) {
			return $metadatum->can_edit();
		}

		return false;
	}

	/**
	 * @param null $object_name
	 *
	 * @return array|void
	 */
	public function get_wp_query_params() {
		$query_params['context']['default'] = 'view';

		$query_params = array_merge($query_params, parent::get_wp_query_params());

		$query_params['name'] = array(
			'description' => __('Limits the result set to metadata with a specific name'),
			'type'        => 'string',
		);

		$query_params = array_merge($query_params, parent::get_meta_queries_params());

		return $query_params;
	}

	/**
	 * @param null $method
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function get_endpoint_args_for_item_schema( $method = null, $is_collection_level = false ) {
		$endpoint_args = [
			'metadatum_id' => [
				'description' => __( 'Metadatum ID', 'tainacan' ),
				'required' => true,
			]
		];

		if ( $is_collection_level )
			$endpoint_args['collection_id'] = [
				'description' => __( 'Collection ID', 'tainacan' ),
				'required' => true,
			];
		
		switch ( $method ) {
			case \WP_REST_Server::READABLE:
				$endpoint_args['context'] = array(
					'description' => __( 'Scope under which the request is made; determines fields present in response.', 'tainacan' ),
					'type'        => 'string',
					'default'     => 'view',
					'enum'        => array(
						'view',
						'edit',
					),
				);
				$endpoint_args = array_merge(
					$endpoint_args,
					parent::get_wp_query_params()
				);
			break;
			case \WP_REST_Server::CREATABLE:
			case \WP_REST_Server::EDITABLE:
				$map = $this->metadatum_repository->get_map();

				foreach ($map as $mapped => $value){
					$set_ = 'set_'. $mapped;
	
					// Show only args that has a method set
					if ( !method_exists(new Entities\Metadatum(), "$set_") ){
						unset($map[$mapped]);
					}
				}
	
				$endpoint_args = array_merge(
					$endpoint_args,
					$map
				);
	
				if ( $method === \WP_REST_Server::CREATABLE )
					unset($endpoint_args['metadatum_id']);
			break;
		}

		return $endpoint_args;
	}

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'metadatum',
			'type' => 'object',
			'tags' => [ $this->rest_base ],
		];

		$main_schema = parent::get_repository_schema( $this->metadatum_repository );
		$permissions_schema = parent::get_permissions_schema();

		$schema['properties'] = array_merge(
			parent::get_base_properties_schema(),
			$main_schema,
			$permissions_schema
		);

		return $schema;
	}
}

?>
