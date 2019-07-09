<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Metadata_Controller extends REST_Controller {
	private $item_metadata_repository;
	private $item_repository;
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
		$this->item_metadata_repository = Repositories\Item_Metadata::get_instance();
		$this->item_repository = Repositories\Items::get_instance();
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
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE)
				),
				// ENDPOINT X. THIS ENDPOINT DO THE SAME THING OF ENDPOINT Z. I hope in a brief future it function changes.
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE),
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
				),
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params(),
				)
			)
		);
		register_rest_route($this->namespace, '/'. $this->rest_base . '/(?P<metadatum_id>[\d]+)',
			array(
				// ENDPOINT Z.
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check')
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE)
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission'          => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
				)
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$collection_id = isset($request['collection_id']) ? $request['collection_id'] : false;
		$metadatum_id = $request['metadatum_id'];

		$offset = '';
		$number = '';
		if($request['offset'] >= 0 && $request['number'] >= 1){
			$offset = $request['offset'];
			$number = $request['number'];
		}

		$result = $this->metadatum_repository->fetch($metadatum_id, 'OBJECT');
		
		if (! $result instanceof Entities\Metadatum) {
			return new \WP_REST_Response([
				'error_message' => __('Metadata with this ID was not found', 'tainacan'),
				'item_id' => $item_id
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
		$collection_id = isset($request['collection_id']) ? $request['collection_id'] : false;

		if($collection_id) {
			$collection = $this->collection_repository->fetch( $collection_id );

			if ( $collection instanceof Entities\Collection ) {
				if ( $request['context'] === 'edit' && ! $collection->can_read() ) {
					return false;
				}

				return true;
			}
		} elseif($request['metadatum_id']) {
			$metadatum = $this->metadatum_repository->fetch($request['metadatum_id']);

			if ( $metadatum instanceof Entities\Metadatum ) {
				if ( $request['context'] === 'edit' && ! $metadatum->can_read() ) {
					return false;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * @param \WP_REST_Request $request
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
			'error_message' => __('Body can not be empty.', 'tainacan'),
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
		if(isset($request['collection_id'])) {
			return $this->collection_repository->can_edit( new Entities\Collection() );
		}

		return $this->metadatum_repository->can_edit(new Entities\Metadatum());
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

			if(isset($item_arr['metadata_type_options']) && isset($item_arr['metadata_type_options']['taxonomy_id'])){
				$taxonomy = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id( $item_arr['metadata_type_options']['taxonomy_id'] );
				//$taxonomy = new Entities\Taxonomy($item_arr['metadata_type_options']['taxonomy_id']);
				//$item_arr['metadata_type_options']['taxonomy'] = $taxonomy->get_db_identifier();
				$item_arr['metadata_type_options']['taxonomy'] = $taxonomy;
			}
			
			if($request['context'] === 'edit'){
				$item_arr['current_user_can_edit'] = $item->can_edit();
				$item_arr['current_user_can_delete'] = $item->can_delete();
				ob_start();
				$item->get_metadata_type_object()->form();
				$form = ob_get_clean();
				$item_arr['edit_form'] = $form;
				$item_arr['enabled'] = $item->get_enabled_for_collection();
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

			$collection = new Entities\Collection( $collection_id );

			$result = $this->metadatum_repository->fetch_by_collection( $collection, $args, 'OBJECT' );
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
		if ( 'edit' === $request['context'] && ! $this->metadatum_repository->can_edit(new Entities\Metadatum()) ) {
			return false;
		}

		return true;
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
				'item_id' => $item_id
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
		if (isset($request['collection_id'])) {
			$collection = $this->collection_repository->fetch($request['collection_id']);

			if ($collection instanceof Entities\Collection) {
				return $collection->can_delete();
			}
		}

		return $this->metadatum_repository->can_edit(new Entities\Metadatum());
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

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$metadatum = $this->metadatum_repository->fetch($metadatum_id);

			$error_message = __('Metadata with this ID was not found', 'tainacan');

			if($metadatum){

				// These conditions are for verify if endpoints are used correctly
				if(!$collection_id && $metadatum->get_collection_id() !== 'default') {
					$error_message = __('This metadata is not a default metadata', 'tainacan');

					return new \WP_REST_Response( [
						'error_message' => $error_message,
						'metadatum_id'      => $metadatum_id
					] );
				} elseif ($collection_id && $metadatum->get_collection_id() === 'default'){
					$error_message = __('This metadata is not a collection metadata', 'tainacan');

					return new \WP_REST_Response( [
						'error_message' => $error_message,
						'metadatum_id'      => $metadatum_id
					] );
				}

				$prepared_metadata = $this->prepare_item_for_updating($metadatum, $attributes);

				if($prepared_metadata->validate()){
					$updated_metadata = $this->metadatum_repository->update($prepared_metadata);

					$response = $this->prepare_item_for_response($updated_metadata, $request);

					return new \WP_REST_Response($response, 200);
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_metadata->get_errors(),
					'metadatum'      => $this->prepare_item_for_response($prepared_metadata, $request)
				], 400);
			}

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
		if(isset($request['collection_id'])) {
            $collection = $this->collection_repository->fetch($request['collection_id']);

            if ($collection instanceof Entities\Collection) {
				return $collection->can_edit();
			}

        }

        return $this->metadatum_repository->can_edit(new Entities\Metadatum());
	}

	/**
	 * @param null $object_name
	 *
	 * @return array|void
	 */
	public function get_collection_params( $object_name = null ) {
		$query_params['context']['default'] = 'view';

		$query_params = array_merge($query_params, parent::get_collection_params('metadatum'));

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
	public function get_endpoint_args_for_item_schema( $method = null ) {
		$endpoint_args = [];
		if($method === \WP_REST_Server::READABLE) {
			$endpoint_args['context'] = array(
				'type'    => 'string',
				'default' => 'view',
				'items'   => array( 'view, edit' )
			);
		} elseif ($method === \WP_REST_Server::CREATABLE || $method === \WP_REST_Server::EDITABLE) {
			$map = $this->metadatum_repository->get_map();

			foreach ($map as $mapped => $value){
				$set_ = 'set_'. $mapped;

				// Show only args that has a method set
				if( !method_exists(new Entities\Metadatum(), "$set_") ){
					unset($map[$mapped]);
				}
			}

			$endpoint_args = $map;
		}

		return $endpoint_args;
	}
}

?>