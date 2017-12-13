<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Metadata_Controller extends WP_REST_Controller {
	private $metadata;
	private $metadata_repository;
	private $item_metadata_repository;
	private $item_repository;

	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'metadata';

		$this->metadata = new Entities\Metadata();
		$this->metadata_repository = new Repositories\Metadatas();
		$this->item_metadata_repository = new Repositories\Item_Metadata();
		$this->item_repository = new Repositories\Items();

		add_action('rest_api_init', array($this, 'register_routes'));
	}


	/**
	 * If POST on metadata/collection/<collection_id>, then
	 * a metadata will be created in matched collection and all your item will receive this metadata
	 *
	 * If POST on metadata/item/<item_id>, then a value will be added in a field and metadata passed
	 * id body of requisition
	 *
	 * Both of GETs return the metadata of matched objects
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/collection/(?P<collection_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check')
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_teim_permissions_check')
				)
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base . '/item/(?P<item_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check')
				)
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
			$this->metadata->$set_($value);
		}

		$collection = new Entities\Collection($request[1]);

		$this->metadata->set_collection($collection);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		if(!empty($request['collection_id'])){
			$collection_id = $request['collection_id'];

			try {
				$this->prepare_item_for_database( [ $request, $collection_id ] );
			} catch (\Error $exception){
				return new WP_REST_Response($exception->getMessage(), 400);
			}

			if($this->metadata->validate()) {
				$this->metadata_repository->insert( $this->metadata );

				$items = $this->item_repository->fetch([], $collection_id, 'WP_Query');

				$metadata_added = '';
				if($items->have_posts()){
					while ($items->have_posts()){
						$items->the_post();

						$item = new Entities\Item($items->post);
						$item_meta = new Entities\Item_Metadata_Entity($item, $this->metadata);

						$metadata_added = $this->item_metadata_repository->insert($item_meta);
					}

					return new WP_REST_Response($metadata_added->get_metadata()->__toJSON(), 201);
				}
				else {
					return new WP_REST_Response($this->metadata->__toJSON(), 201);
				}
			} else {
				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $this->metadata->get_errors(),
					'metadata'      => $this->metadata->__toJSON(),
				], 400);
			}
		} elseif (!empty($request['item_id']) && !empty($request->get_body())){
			$body = json_decode($request->get_body(), true);

			$item_id = $request['item_id'];
			$metadata_id = $body['metadata_id'];
			$value = $body['values'];

			$item = $this->item_repository->fetch($item_id);
			$metadata = $this->metadata_repository->fetch($metadata_id);

			$item_metadata = new Entities\Item_Metadata_Entity($item, $metadata);
			$item_metadata->set_value($value);

			if($item_metadata->validate()) {
				$metadata_updated = $this->item_metadata_repository->insert( $item_metadata );

				return new WP_REST_Response( $metadata_updated->__toJSON(), 201 );
			} else {
				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $item_metadata->get_errors(),
					'item_metadata' => $item_metadata->__toJSON(),
				], 400);
			}
		} else {
			return new WP_REST_Response($request->get_body(), 400);
		}
	}

	public function create_item_permissions_check( $request ) {
		return true;
	}

	public function prepare_item_for_response( $item, $request ) {
		$metadata_as_json = [];

		if($request['item_id']) {
			foreach ( $item as $metadata ) {
				$metadata_as_json[] = $metadata->__toJSON();
			}
		} else {
			foreach ( $item as $metadata ) {
				$metadata_as_json[] = $metadata->__toJSON();
			}
		}

		return $metadata_as_json;
	}

	public function get_items( $request ) {
		if(!empty($request['collection_id'])){
			$collection_id = $request['collection_id'];

			$collection = new Entities\Collection($collection_id);

			$collection_metadata = $this->metadata_repository->fetch_by_collection($collection, [], 'OBJECT');

			$prepared_item = $this->prepare_item_for_response($collection_metadata, $request);

			return new WP_REST_Response($prepared_item, 200);
		}

		$item_id = $request['item_id'];

		$item = new Entities\Item($item_id);

		$item_metadata = $this->item_metadata_repository->fetch($item, 'OBJECT');

		$prepared_item = $this->prepare_item_for_response($item_metadata, $request);

		return new WP_REST_Response($prepared_item, 200);
	}

	public function get_item_permissions_check( $request ) {
		return true;
	}

	public function get_items_permissions_check( $request ) {
		return true;
	}

	public function get_collection_params() {
		return parent::get_collection_params(); // TODO: Change the autogenerated stub
	}

	public function delete_item( $request ) {
		if(!empty($request->get_body())){
			$body = json_decode($request->get_body());

			$collection_id = $request['collection_id'];
			$metadata_id = $body['metadata_id'];

			return new WP_REST_Response(['error' => 'Not Implemented.'], 400);
		}
	}

	public function delete_item_permissions_check( $request ) {
		return parent::delete_item_permissions_check( $request ); // TODO: Change the autogenerated stub
	}
}

?>