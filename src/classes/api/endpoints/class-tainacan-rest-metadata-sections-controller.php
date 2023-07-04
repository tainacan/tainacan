<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Metadata_Sections_Controller extends REST_Controller {

	private $metadata_sections_repository;
	private $metadata_repository;
	private $collection_repository;

	public function __construct() {
		parent::__construct();
		$this->rest_base = 'metadata-sections';
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 *
	 * @throws \Exception
	 */
	public function init_objects() {
		$this->metadata_sections_repository = Repositories\Metadata_Sections::get_instance();
		$this->metadata_repository = Repositories\Metadata::get_instance();
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
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<metadata_section_id>[\d|default_section]+)',
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
						'metadata_section_id' => [
							'description' => __( 'Metadata Section ID', 'tainacan' ),
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
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE)
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
					'args' => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::DELETABLE)
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
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
				),
				'schema'                  => [$this, 'get_list_schema']
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<metadata_section_id>[\d|default_section]+)/metadata',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_metadata_list'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'add_metadata'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_metadata'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::DELETABLE),
				),
				'schema'                  => [$this, 'get_metadata_list_schema']
			)
		);
	}

	/**
	 * @param \WP_REST_Request|string $request
	 *
	 * @param $collection_id
	 *
	 * @return object|void|\WP_Error
	 * @throws \Exception
	 */
	public function prepare_item_for_database( $request, $collection_id = null) {
		if($collection_id == null) {
			throw new \InvalidArgumentException('You need provide a collection id');
		}
		$metadata_section = new Entities\Metadata_Section();
		$meta = json_decode( $request, true );
		foreach ( $meta as $key => $value ) {
			$set_ = 'set_' . $key;
			$metadata_section->$set_( $value );
		}
		$collection = new Entities\Collection( $collection_id );
		$metadata_section->set_collection( $collection );
		return $metadata_section;
	}

		/**
	 * @param Entities\Metadata_Section $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		
		if (!empty($item)){
			$item_arr = $item->_toArray();
			if ($request['context'] === 'edit') {
				$item_arr['current_user_can_edit'] = $item->can_edit();
				$item_arr['current_user_can_delete'] = $item->can_delete();
				$item_arr['enabled'] = $item->get_enabled_for_collection();
			}

			$args = [];
			if ($request['include_disabled'] === 'true') {
				$args['include_disabled'] = true;
			}

			$metadata_list = $item->get_metadata_object_list($args);
			$item_arr['metadata_object_list'] = [];
			if($metadata_list != false) {
				foreach($metadata_list as $metadata) {
					$meta_arr = $this->prepare_metadata_for_response($metadata, $request);
					$item_arr['metadata_object_list'][] = $meta_arr;
				}
			}

			/**
			 * Use this filter to add additional post_meta to the api response
			 * Use the $request object to get the context of the request and other variables
			 * For example, id context is edit, you may want to add your meta or not.
			 *
			 * Also take care to do any permissions verification before exposing the data
			 */
			$extra_metadata = apply_filters('tainacan-api-response-metadata-section-meta', [], $request);

			foreach ($extra_metadata as $extra_meta) {
				$item_arr[$extra_meta] = get_post_meta($item_arr['id'], $extra_meta, true);
			}
			return $item_arr;
		}
		return $item;
	}

	/**
	 * @param Entities\Metadata $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_metadata_for_response( $item, $request ) {
		if(!empty($item)){
			$item_arr = $item->_toArray();
			$item_arr['metadata_type_object'] = $item->get_metadata_type_object()->_toArray();

			if ( isset($request['include_options_as_html']) && $request['include_options_as_html'] == 'yes' )
				$item_arr['options_as_html'] = $item->get_metadata_type_object()->get_options_as_html();

			if ( isset($item_arr['metadata_type_options']) && isset($item_arr['metadata_type_options']['taxonomy_id']) ) {
				$taxonomy = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id( $item_arr['metadata_type_options']['taxonomy_id'] );
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
	 */
	public function get_item( $request ) {
		$metadata_section_id = $request['metadata_section_id'];
		if($metadata_section_id == \Tainacan\Entities\Metadata_Section::$default_section_slug) {
			$collection_id = is_numeric($request['collection_id']) ? (int)$request['collection_id'] : null;
			$result = $this->metadata_sections_repository->get_default_section($collection_id);
		} else {
			$result = $this->metadata_sections_repository->fetch($metadata_section_id, 'OBJECT');
		}
		
		if (! $result instanceof Entities\Metadata_Section) {
			return new \WP_REST_Response([
				'error_message' => __('Metadata section with this ID was not found', 'tainacan'),
				'item_id' => $metadata_section_id
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
		if(!isset($request['collection_id']) || !isset($request['metadata_section_id'])) {
			return false;
		}

		$collection = $this->collection_repository->fetch($request['collection_id']);
		if($collection instanceof Entities\Collection && $collection->can_read()) {
			$metadata_section_id = $request['metadata_section_id'];
			if($metadata_section_id == \Tainacan\Entities\Metadata_Section::$default_section_slug) {
				$metadatum_section = $this->metadata_sections_repository->get_default_section($collection);
			} else {
				$metadatum_section = $this->metadata_sections_repository->fetch($metadata_section_id);
			}
			
			return $metadatum_section instanceof Entities\Metadata_Section && $metadatum_section->can_read();
		}

		return false;
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
			} catch (\Exception $exception) {
				return new \WP_REST_Response($exception->getMessage(), 400);
			}

			if($prepared->validate()) {
				$metadata_section = $this->metadata_sections_repository->insert($prepared);
				$response = $this->prepare_item_for_response($metadata_section, $request);
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

	// @TODO: fix the permissions check
	/**
	 * @param $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function create_item_permissions_check( $request ) {
		return true;
		if( isset($request['collection_id']) ) {
			$collection = $this->collection_repository->fetch( $request['collection_id'] );

			if ( $collection instanceof Entities\Collection ) {
				return $collection->user_can( 'edit_metadata_section' );
			}

		} else {

			return current_user_can( 'tnc_rep_edit_metadata_section' );

		}

		return false;

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

			$collection = new Entities\Collection( $collection_id );

			$result = $this->metadata_sections_repository->fetch_by_collection( $collection, $args );
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
		$metadata_section_id = $request['metadata_section_id'];
		$metadatum_section = $this->metadata_sections_repository->fetch($metadata_section_id);
		if (! $metadatum_section instanceof Entities\Metadata_Section) {
			return new \WP_REST_Response([
				'error_message' => __('Metadata section with this ID was not found', 'tainacan'),
				'item_id' => $metadata_section_id
			], 400);
		}

		$metadatum_section_trashed = $this->metadata_sections_repository->delete($metadatum_section);
		$prepared = $this->prepare_item_for_response($metadatum_section_trashed, $request);
		return new \WP_REST_Response($prepared, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function delete_item_permissions_check( $request ) {
		$metadata_section = $this->metadata_sections_repository->fetch($request['metadata_section_id']);

		if ($metadata_section instanceof Entities\Metadata_Section) {
			return $metadata_section->can_delete();
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
			$metadata_section_id = $request['metadata_section_id'];
			if($metadata_section_id == \Tainacan\Entities\Metadata_Section::$default_section_slug) {
				$metadata_section = $this->metadata_sections_repository->get_default_section($collection_id);
			} else {
				$metadata_section = $this->metadata_sections_repository->fetch($metadata_section_id);
				if ( $collection_id != $metadata_section->get_collection_id() ) {
					return new \WP_REST_Response( [
						'error_message' => __('This metadata section not found in collection', 'tainacan'),
						'metadata_section_id'  => $metadata_section_id
					] );
				}
			}

			if ($metadata_section) {
				$attributes = [];
				foreach ($body as $att => $value) {
					$attributes[$att] = $value;
				}

				$prepared = $this->prepare_item_for_updating($metadata_section, $attributes);
				if($prepared->validate()) {
					$updated_metadata_section = $this->metadata_sections_repository->update($prepared);
					$response = $this->prepare_item_for_response($updated_metadata_section, $request);
					return new \WP_REST_Response($response, 200);
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared->get_errors(),
					'metadatum_section' => $this->prepare_item_for_response($prepared, $request)
				], 400);
			}

			return new \WP_REST_Response( [
				'error_message' => __('Metadata with this ID was not found', 'tainacan'),
				'metadata_section_id'  => $metadata_section_id
			] );
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}

	public function add_metadata( $request ) {
		if( !empty($request->get_body()) && isset($request['metadata_section_id']) ){
			$body = json_decode($request->get_body(), true);
			$metadata_section_id = $request['metadata_section_id'];
			$metadata_list = $body['metadata_list'];

			try {
				$metadata_section = $this->metadata_sections_repository->add_metadata($metadata_section_id, $metadata_list);
				if($metadata_section == false) {
					return new \WP_REST_Response([
						'error_message' => __('One or more values are invalid.', 'tainacan'),
						'item'          => $request->get_body()
					], 400);
				}
				$response = $this->prepare_item_for_response($metadata_section, $request);
				return new \WP_REST_Response($response, 201);
			} catch (\Exception $exception) {
				return new \WP_REST_Response($exception->getMessage(), 400);
			}
		}
		return new \WP_REST_Response([
			'error_message' => __('Body cannot be empty.', 'tainacan'),
			'item'          => $request->get_body()
		], 400);
	}

	public function delete_metadata( $request ) {
		if( !empty($request->get_body()) && isset($request['metadata_section_id']) ){
			$body = json_decode($request->get_body(), true);
			$metadata_section_id = $request['metadata_section_id'];
			$metadata_list = $body['metadata_list'];

			try {
				$metadata_section = $this->metadata_sections_repository->delete_metadata($metadata_section_id, $metadata_list);
				if($metadata_section == false) {
					return new \WP_REST_Response([
						'error_message' => __('One or more values are invalid.', 'tainacan'),
						'item'          => $request->get_body()
					], 400);
				}
				$response = $this->prepare_item_for_response($metadata_section, $request);
				return new \WP_REST_Response($response, 201);
			} catch (\Exception $exception) {
				return new \WP_REST_Response($exception->getMessage(), 400);
			}
		}
		return new \WP_REST_Response([
			'error_message' => __('Body cannot be empty.', 'tainacan'),
			'item'          => $request->get_body()
		], 400);
	}

	public function get_metadata_list( $request ) {
		if(isset($request['metadata_section_id']) ){
			$metadata_section_id = $request['metadata_section_id'];

			try {
				$result = $this->metadata_sections_repository->get_metadata_object_list($metadata_section_id);
				$prepared_item = [];
				foreach ( $result as $item ) {
					$prepared_item[] = $item->_toArray();
				}
				return new \WP_REST_Response($prepared_item, 200);
			} catch (\Exception $exception) {
				return new \WP_REST_Response($exception->getMessage(), 400);
			}
		}
		return new \WP_REST_Response([
			'error_message' => __('Metadata section id cannot be empty.', 'tainacan'),
			'item'          => $request->get_body()
		], 400);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function update_item_permissions_check( $request ) {
		return true;
		$metadatum = $this->metadata_sections_repository->fetch($request['metadatum_id']);

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
			'description' => __('Limits the result set to metadata sections with a specific name'),
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
	public function get_endpoint_args_for_item_schema( $method = null ) {
		$endpoint_args = [
			'metadata_section_id' => [
				'description' => __( 'Metadata Section ID', 'tainacan' ),
				'required' => true,
			],
			'collection_id' => [
				'description' => __( 'Collection ID', 'tainacan' ),
				'required' => true,
			]
		];

		switch ( $method ) {
			case \WP_REST_Server::READABLE:
				$endpoint_args = array_merge(
					$endpoint_args,
					parent::get_wp_query_params()
				);
			break;
			case \WP_REST_Server::CREATABLE:
			case \WP_REST_Server::EDITABLE:
				$map = $this->metadata_sections_repository->get_map();

				foreach ($map as $mapped => $value){
					$set_ = 'set_'. $mapped;
	
					// Show only args that has a method set
					if( !method_exists(new Entities\Metadatum(), "$set_") ){
						unset($map[$mapped]);
					}
				}
	
				$endpoint_args = array_merge(
					$endpoint_args,
					$map
				);
	
				if ( $method === \WP_REST_Server::CREATABLE )
					unset($endpoint_args['metadata_section_id']);

			break;
		}

		return $endpoint_args;
	}

	function get_metadata_list_schema() {
		$metadatum_schema = parent::get_repository_schema($this->metadata_repository);
		return [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'metadata',
			'tags' => [$this->rest_base, 'metadata'],
			'type' => 'array',
			'items' => array(
				'type' => 'object',
				'properties' => $metadatum_schema,
			)
		];
	}

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'metadata-section',
			'type' => 'object',
			'tags' => [ $this->rest_base ],
		];

		$main_schema = parent::get_repository_schema( $this->metadata_sections_repository );
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
