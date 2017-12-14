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

	/**
	 * TAINACAN_REST_Items_Controller constructor.
	 * Define the namespace, rest base and instantiate your attributes.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'items';
		$this->items_repository = new Repositories\Items();
		$this->item = new Entities\Item();
		$this->item_metadata = new Repositories\Item_Metadata();

		add_action('rest_api_init', array($this, 'register_routes'));
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
					//'permission_callback' => array($this, 'get_items_permissions_check'),
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
					array_push($items_as_array, $ite->__toJSON());

				}
				wp_reset_postdata();
			}

			return json_encode($items_as_array);
		} elseif(!empty($item)){
			return $item->__toJSON();
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
		return true;
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

		$collection = new Entities\Collection($request[1]);

		$this->item->set_collection($collection);

		$metadata = get_post_meta($collection->get_id());

		if(!empty($metadata)) {
			foreach ($metadata as $key => $value){
				$new_metadata = new Entities\Metadata();

				try {
					$set_ = 'set_' . $key;
					$new_metadata->$set_( $value );
				} catch (\Error $exception){
					//echo $exception->getMessage();
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

		try {
			$metadata = $this->prepare_item_for_database( [ $item, $collection_id ] );
		} catch (\Error $exception){
			return new WP_REST_Response($exception->getMessage(), 400);
		}

		if($this->item->validate()) {
			$item = $this->items_repository->insert($this->item );

			$item_metadata  = new Entities\Item_Metadata_Entity($item, $metadata );
			$metadata_added = $this->item_metadata->insert( $item_metadata );

			return new WP_REST_Response($metadata_added->get_item()->__toJSON(), 201 );
		}


		return new WP_REST_Response([
			'error_message' => __('One or more values are invalid.', 'tainacan'),
			'errors'        => $this->item->get_errors(),
			'item'          => $this->item->__toJSON()
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		return true;
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
	 */
	public function delete_item_permissions_check( $request ) {
		return true;
	}

}

?>