<?php

use Tainacan\Repositories;
use Tainacan\Entities;

/**
 * Represents the Items REST Controller
 * @uses Tainacan\Repositories\
 * @uses Tainacan\Entities\
*/
class TAINACAN_REST_Items_Controller extends WP_REST_Controller {
	private $items_repository;
	private $item;
	private $item_metadata;
	private $collections_repository;

	/**
	 * TAINACAN_REST_Items_Controller constructor.
	 * Define the namespace, rest base and instantiate your attributes.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'items';
		
		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->items_repository = new Repositories\Items();
		$this->item = new Entities\Item();
		$this->item_metadata = new Repositories\Item_Metadata();
		$this->collections_repository = new Repositories\Collections();
	}

	/**
	 * Register items routes, and their endpoints
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/collection/(?P<collection_id>[\d]+)',
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
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
				),
				'schema' => array($this, 'get_public_item_schema'),
		));
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<item_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
				),
		));
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|string|void|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if (!empty($item) && $item instanceof WP_Query){
			$items_as_array = [];

			if ($item->have_posts()) {
				while ( $item->have_posts() ) {
					$item->the_post();
					$ite = new Entities\Item($item->post);
					array_push($items_as_array, $ite->__toArray());

				}
				wp_reset_postdata();
			}

			return $items_as_array;
		} elseif(!empty($item)){
			return $item->__toArray();
		}

		return $item;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$item_id = $request['item_id'];

		$item = $this->items_repository->fetch($item_id);

		$response = $this->prepare_item_for_response($item, $request);

		return new WP_REST_Response($response, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$collection_id = $request['collection_id'];
		$items = $this->items_repository->fetch([], $collection_id, 'WP_Query');

		$response = $this->prepare_item_for_response($items, $request);

		return new WP_REST_Response($response, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$item = $this->items_repository->fetch($request['item_id']);
		return $this->items_repository->can_read($item);
	}

	public function get_items_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);
		return $this->collections_repository->can_read($collection);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return object|Entities\Item|WP_Error
	 */
	public function prepare_item_for_database( $request ) {

		$item_as_array = $request[0];

		foreach ($item_as_array as $key => $value){
			$set_ = 'set_' . $key;
			$this->item->$set_($value);
		}

		$collection = $this->collections_repository->fetch($request[1]);

		$this->item->set_collection($collection);

		$metadata = get_post_meta($collection->get_id());

		if(!empty($metadata)) {
			foreach ($metadata as $key => $value){
				$new_metadata = new Entities\Metadata();

				try {
					$set_ = 'set_' . $key;
					$new_metadata->$set_( $value );
				} catch (\Error $exception){
					// Do nothing
				}
			}

		}

		return $new_metadata;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$collection_id = $request['collection_id'];
		$item          = json_decode($request->get_body(), true);

		if(empty($item)){
			return new WP_REST_Response([
				'error_message' => __('Body can not be empty.', 'tainacan'),
				'item'          => $item
			], 400);
		}

		try {
			$metadata = $this->prepare_item_for_database( [ $item, $collection_id ] );
		} catch (\Error $exception){
			return new WP_REST_Response($exception->getMessage(), 400);
		}

		if($this->item->validate()) {
			$item = $this->items_repository->insert($this->item );

			$item_metadata  = new Entities\Item_Metadata_Entity($item, $metadata );
			$metadata_added = $this->item_metadata->insert( $item_metadata );

			return new WP_REST_Response($metadata_added->get_item()->__toArray(), 201 );
		}


		return new WP_REST_Response([
			'error_message' => __('One or more values are invalid.', 'tainacan'),
			'errors'        => $this->item->get_errors(),
			'item'          => $this->item->__toArray()
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function create_item_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);
        if ($collection instanceof Entities\Collection) {
            return $collection->get_items_capabilities()->edit_posts;
        }
        return false;
        
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$item_id        = $request['item_id'];
		$is_permanently = json_decode($request->get_body(), true);

		$args = [$item_id, $is_permanently];

		$item = $this->items_repository->delete($args);

		$prepared_item = $this->prepare_item_for_response($item, $request);

		return new WP_REST_Response($prepared_item, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function delete_item_permissions_check( $request ) {
		$item = $this->items_repository->fetch($request['item_id']);
		return $this->items_repository->can_delete($item);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$item_id = $request['item_id'];

		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$attributes = ['ID' => $item_id];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$updated_item = $this->items_repository->update($attributes);

			return new WP_REST_Response($updated_item->__toArray(), 200);
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
		$item = $this->items_repository->fetch($request['item_id']);
		return $this->items_repository->can_edit($item);
	}
}

?>