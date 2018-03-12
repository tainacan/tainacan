<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Fields_Controller extends TAINACAN_REST_Controller {
	private $item_metadata_repository;
	private $item_repository;
	private $collection_repository;
	private $field_repository;

	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'fields';

		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 *
	 * @throws Exception
	 */
	public function init_objects() {
		$this->field_repository = new Repositories\Fields();
		$this->item_metadata_repository = new Repositories\Item_Metadata();
		$this->item_repository = new Repositories\Items();
		$this->collection_repository = new Repositories\Collections();
	}

	/**
	 * If POST on field/collection/<collection_id>, then
	 * a field will be created in matched collection and all your item will receive this field
	 *
	 * If POST on field/item/<item_id>, then a value will be added in a field and field passed
	 * id body of requisition
	 *
	 * Both of GETs return the field of matched objects
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<field_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check')
				),
				// ENDPOINT X. THIS ENDPOINT DO THE SAME THING OF ENDPOINT Z. I hope in a brief future it function changes.
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check')
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check')
				),
			)
		);
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					//'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check')
				),
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check')
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check')
				)
			)
		);
		register_rest_route($this->namespace, '/'. $this->rest_base . '/(?P<field_id>[\d]+)',
			array(
				// ENDPOINT Z.
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check')
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check')
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$collection_id = $request['collection_id'];
		$field_id = $request['field_id'];

		if($request['fetch'] === 'all_field_values'){
			$results = $this->field_repository->fetch_all_field_values($collection_id, $field_id);

			return new WP_REST_Response($results, 200);
		}

		$result = $this->field_repository->fetch($field_id, 'OBJECT');

		return new WP_REST_Response($this->prepare_item_for_response($result, $request), 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function get_item_permissions_check( $request ) {
		$collection = $this->collection_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection) {
			if ($request['context'] === 'edit' && ! $collection->can_read()) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @param null $collection_id
	 *
	 * @return object|void|WP_Error
	 * @throws Exception
	 */
	public function prepare_item_for_database( $request, $collection_id = null ) {
		$field = new Entities\Field();

		$meta = json_decode( $request, true );
		foreach ( $meta as $key => $value ) {
			$set_ = 'set_' . $key;
			$field->$set_( $value );
		}

		if($collection_id) {
			$collection = new Entities\Collection( $collection_id );
			$field->set_collection( $collection );
		} else {
			$field->set_collection_id( 'default' );
		}

		return $field;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 * @throws Exception
	 */
	public function create_item( $request ) {
		if(!empty($request->get_body()) && isset($request['collection_id'])){
			$collection_id = $request['collection_id'];

			try {
				$prepared = $this->prepare_item_for_database( $request->get_body(), $collection_id );
			} catch (\Error $exception){
				return new WP_REST_Response($exception->getMessage(), 400);
			}

			if($prepared->validate()) {
				$field = $this->field_repository->insert( $prepared);

				$items = $this->item_repository->fetch([], $collection_id, 'WP_Query');

				$field_added = '';
				if($items->have_posts()){
					while ($items->have_posts()){
						$items->the_post();

						$item = new Entities\Item($items->post);
						$item_meta = new Entities\Item_Metadata_Entity($item, $field);

						$field_added = $this->item_metadata_repository->insert($item_meta);
					}

					$response = $this->prepare_item_for_response($field_added->get_field(), $request);

					return new WP_REST_Response($response, 201);
				}
				else {
					$response = $this->prepare_item_for_response($prepared, $request);

					return new WP_REST_Response($response, 201);
				}
			} else {
				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared->get_errors(),
					'field'         => $this->prepare_item_for_response($prepared, $request),
				], 400);
			}
		} elseif (!empty($request->get_body())) {
			try {
				$prepared = $this->prepare_item_for_database( $request->get_body() );
			} catch ( \Error $exception ) {
				return new WP_REST_Response( $exception->getMessage(), 400 );
			}

			if ( $prepared->validate() ) {
				$field = $this->field_repository->insert( $prepared );

				$response = $this->prepare_item_for_response($field, $request);

				return new WP_REST_Response($response, 201);
			} else {
				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $this->field->get_errors(),
					'field'         => $this->prepare_item_for_response($this->field, $request),
				], 400);
			}

		}

		return new WP_REST_Response([
			'error_message' => __('Body can not be empty.', 'tainacan'),
			'item'          => $request->get_body()
		], 400);

	}

	/**
	 * @param $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function create_item_permissions_check( $request ) {
		if(isset($request['collection_id'])) {
			return $this->collection_repository->can_edit( new Entities\Collection() );
		}

		return $this->field_repository->can_edit(new Entities\Field());
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)){
			$item_arr = $item->__toArray();

			if($request['context'] === 'edit'){
				$item_arr['current_user_can_edit'] = $item->can_edit();
				ob_start();
				$item->get_field_type_object()->form();
				$form = ob_get_clean();
				$item_arr['edit_form'] = $form;
				$item_arr['field_type_object'] = $item->get_field_type_object();
				$item_arr['disabled'] = $item->get_disabled_for_collection();
			}

			return $item_arr;
		}

		return $item;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		if(isset($request['collection_id'])) {
			$collection_id = $request['collection_id'];

			$args = $this->prepare_filters( $request );
			
			if ($request['context'] === 'edit') {
				$args['include_disabled'] = true;
			}

			$collection = new Entities\Collection( $collection_id );

			$result = $this->field_repository->fetch_by_collection( $collection, $args, 'OBJECT' );
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

			$result = $this->field_repository->fetch( $args, 'OBJECT' );
		}

		$prepared_item = [];
		foreach ( $result as $item ) {
			$prepared_item[] = $this->prepare_item_for_response( $item, $request );
		}

		return new WP_REST_Response($prepared_item, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function get_items_permissions_check( $request ) {
		if ( 'edit' === $request['context'] && ! current_user_can('edit_tainacan-fields') ) {
			return false;
		}

		return true;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
			$field_id = $request['field_id'];

			$field_trashed = $this->field_repository->delete($field_id);

			$prepared = $this->prepare_item_for_response($field_trashed, $request);

			return new WP_REST_Response($prepared, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function delete_item_permissions_check( $request ) {
		if (isset($request['collection_id'])) {
			$collection = $this->collection_repository->fetch($request['collection_id']);

			if ($collection instanceof Entities\Collection) {
				return $collection->can_delete();
			}
		}

		return $this->field_repository->can_edit(new Entities\Field());
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$collection_id = $request['collection_id'];
		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$attributes = [];

			$field_id = $request['field_id'];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$field = $this->field_repository->fetch($field_id);

			$error_message = __('Field with that ID not found', 'tainacan');

			if($field){

				// These conditions are for verify if endpoints are used correctly
				if(!$collection_id && $field->get_collection_id() !== 'default') {
					$error_message = __('That field is not a default field', 'tainacan');

					return new WP_REST_Response( [
						'error_message' => $error_message,
						'field_id'      => $field_id
					] );
				} elseif ($collection_id && $field->get_collection_id() === 'default'){
					$error_message = __('That field is not a collection field', 'tainacan');

					return new WP_REST_Response( [
						'error_message' => $error_message,
						'field_id'      => $field_id
					] );
				}

				$prepared_metadata = $this->prepare_item_for_updating($field, $attributes);

				if($prepared_metadata->validate()){
					$updated_metadata = $this->field_repository->update($prepared_metadata);

					$response = $this->prepare_item_for_response($updated_metadata, $request);

					return new WP_REST_Response($response, 200);
				}

				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_metadata->get_errors(),
					'metadata'      => $this->prepare_item_for_response($prepared_metadata, $request)
				], 400);
			}

			return new WP_REST_Response( [
				'error_message' => $error_message,
				'field_id'      => $field_id
			] );
		}

		return new WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function update_item_permissions_check( $request ) {
		if(isset($request['collection_id'])) {
            $collection = $this->collection_repository->fetch($request['collection_id']);

            if ($collection instanceof Entities\Collection) {
				return $collection->can_edit();
			}

        }

        return $this->field_repository->can_edit(new Entities\Field());
	}
}

?>