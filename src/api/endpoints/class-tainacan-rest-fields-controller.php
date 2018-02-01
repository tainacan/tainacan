<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Fields_Controller extends TAINACAN_REST_Controller {
	private $field;
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
	 */
	public function init_objects() {
		$this->field = new Entities\Field();
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
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check')
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check')
				)
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
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return object|void|WP_Error
	 */
	public function prepare_item_for_database( $request ) {
		$meta = json_decode($request[0]->get_body(), true);

		foreach ($meta as $key => $value){
			$set_ = 'set_' . $key;
			$this->field->$set_($value);
		}

		$collection = new Entities\Collection($request[1]);

		$this->field->set_collection($collection);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		if(!empty($request->get_body())){
			$collection_id = $request['collection_id'];

			try {
				$this->prepare_item_for_database( [ $request, $collection_id ] );
			} catch (\Error $exception){
				return new WP_REST_Response($exception->getMessage(), 400);
			}

			if($this->field->validate()) {
				$this->field_repository->insert( $this->field );

				$items = $this->item_repository->fetch([], $collection_id, 'WP_Query');

				$field_added = '';
				if($items->have_posts()){
					while ($items->have_posts()){
						$items->the_post();

						$item = new Entities\Item($items->post);
						$item_meta = new Entities\Item_Metadata_Entity($item, $this->field);

						$field_added = $this->item_metadata_repository->insert($item_meta);
					}

					return new WP_REST_Response($field_added->get_field()->__toArray(), 201);
				}
				else {
					return new WP_REST_Response($this->field->__toArray(), 201);
				}
			} else {
				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $this->field->get_errors(),
					'field'      => $this->field->__toArray(),
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
		return $this->collection_repository->can_edit(new Entities\Collection());
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$field_as = [];

		foreach ( $item as $field ) {
			$field_as[] = $field->__toArray();
		}

		return $field_as;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$collection_id = $request['collection_id'];

		$args = [];

		$map = $this->field_repository->get_map();

		foreach ($map as $key => $value){
			if(isset($request[$key], $map[$key])){
				$args[$value['map']] = $request[$key];
			}
		}

		//$args = $this->unmap_filters($args, $map);

		$collection = new Entities\Collection($collection_id);

		$collection_metadata = $this->field_repository->fetch_by_collection($collection, $args, 'OBJECT');

		$prepared_item = $this->prepare_item_for_response($collection_metadata, $request);

		return new WP_REST_Response($prepared_item, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function get_items_permissions_check( $request ) {
		if (isset($request['collection_id'])) {
			$collection = $this->collection_repository->fetch($request['collection_id']);

			if ($collection instanceof Entities\Collection) {
				return $collection->can_read();
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function get_collection_params() {
		return parent::get_collection_params(); // TODO: Change the autogenerated stub
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		if(!empty($request->get_body())){
			$body = json_decode($request->get_body());

			$collection_id = $request['collection_id'];
			$field_id = $body['field_id'];

			return new WP_REST_Response(['error' => 'Not Implemented.'], 400);
		}
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

		return false;
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

			foreach ($body['values'] as $att => $value){
				$attributes[$att] = $value;
			}

			$field = $this->field_repository->fetch($field_id);

			$updated_metadata = $this->field_repository->update($field, $attributes);

			if(!($updated_metadata instanceof Entities\Field)){
				return new WP_REST_Response($updated_metadata, 400);
			}

			$items = $this->item_repository->fetch([], $collection_id, 'WP_Query');

			$up_metadata = '';
			if($items->have_posts()){
				while ($items->have_posts()){
					$items->the_post();

					$item = new Entities\Item($items->post);
					$item_meta = new Entities\Item_Metadata_Entity($item, $updated_metadata);

					$up_metadata = $this->item_metadata_repository->update($item_meta);
				}

				return new WP_REST_Response($up_metadata->get_field()->__toArray(), 201);
			}

			return new WP_REST_Response($updated_metadata->__toArray(), 201);
		}

		return new WP_REST_Response([
			'error_message' => 'The body could not be empty',
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

        return false;
	}
}

?>