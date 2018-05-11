<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Export_Controller extends REST_Controller {
	private $item_metadata_repository;
	private $items_repository;
	private $collection_repository;
	private $field_repository;

	public function __construct() {
		$this->rest_base = 'export';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 *
	 * @throws \Exception
	 */
	public function init_objects() {
		$this->field_repository = Repositories\Fields::get_instance();
		$this->item_metadata_repository = Repositories\Item_Metadata::get_instance();
		$this->items_repository = Repositories\Items::get_instance();
		$this->collection_repository = Repositories\Collections::get_instance();
	}

	/**
	 * If POST on field/collection/<collection_id>, then
	 * a field will be created in matched collection and all your item will receive this field
	 *
	 * If POST on field/item/<item_id>, then a value will be added in a field and field passed
	 * id body of requisition
	 *
	 * Both of GETs return the field of matched objects
	 *
	 * @throws \Exception
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base. '/collection/(?P<collection_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE),
				),
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base. '/item/(?P<item_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE),
				),
			)
		);
		register_rest_route($this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params(),
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
		/*$collection_id = $request['collection_id'];
		$field_id = $request['field_id'];

		if($request['fetch'] === 'all_field_values'){
			$results = $this->field_repository->fetch_all_field_values($collection_id, $field_id);

			return new \WP_REST_Response($results, 200);
		}

		$result = $this->field_repository->fetch($field_id, 'OBJECT');

		$prepared_item = $this->prepare_item_for_response($result, $request);
		return new \WP_REST_Response(apply_filters('tainacan-rest-response', $prepared_item, $request), 200);*/
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_item_permissions_check( $request ) {
		if(isset($request['collection_id'])) {
			$collection = $this->collection_repository->fetch($request['collection_id']);
			if($collection instanceof Entities\Collection) {
				if (! $collection->can_read()) {
					return false;
				}
				return true;
			}
		} elseif(isset($request['item_id'])) {
			$item = $this->items_repository->fetch($request['item_id']);
			if($item instanceof Entities\Item) {
				if (! $item->can_read()) {
					return false;
				}
				return true;
			}
		} else { // Exporting all
			$dummy = new Entities\Collection();
			return current_user_can($dummy->get_capabilities()->read); // Need to check Colletion by collection
		}
		return false;
	}

	/**
	 * @param \Tainacan\Entities\Entity $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$items_metadata = $item->get_fields();
		
		$prepared_item = [];
		
		foreach ($items_metadata as $item_metadata){
			$prepared_item[] =  $item_metadata->__toArray();
		}

		return $prepared_item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$args = $this->prepare_filters($request);
		$rest_response = new \WP_REST_Response([], 200); // TODO error, empty response
		
		if(isset($request['collection_id'])) {
			$collection_id = $request['collection_id'];
			$items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
			
			$response = [];
			if ($items->have_posts()) {
				while ( $items->have_posts() ) {
					$items->the_post();
					
					$item = new Entities\Item($items->post);
					
					$prepared_item = $this->prepare_item_for_response($item, $request);
					
					array_push($response, $prepared_item);
				}
				
				wp_reset_postdata();
			}
			
			$total_items  = $items->found_posts;
			$max_pages = ceil($total_items / (int) $items->query_vars['posts_per_page']);
			
			$rest_response = new \WP_REST_Response(apply_filters('tainacan-rest-response', $response, $request), 200);
			
			$rest_response->header('X-WP-Total', (int) $total_items);
			$rest_response->header('X-WP-TotalPages', (int) $max_pages);
		}
		return $rest_response;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check($request);
	}

}

?>