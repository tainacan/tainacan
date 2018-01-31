<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Filters_Controller extends TAINACAN_REST_Controller {
	private $collection;
	private $collection_repository;

	private $metadata;
	private $metadata_repository;

	private $filter;
	private $filter_repository;

	/**
	 * TAINACAN_REST_Filters_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = '/tainacan/v2';
		$this->rest_base = 'filters';

		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->collection = new Entities\Collection();
		$this->collection_repository = new Repositories\Collections();
		
		$this->metadata = new Entities\Metadata();
		$this->metadata_repository = new Repositories\Metadatas();
		
		$this->filter = new Entities\Filter();
		$this->filter_repository = new Repositories\Filters();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
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
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<filter_id>[\d]+)', array(
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
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check')
			)
		));
	}


	/**
	 * @param WP_REST_Request $request
	 *
	 * @return object|void|WP_Error
	 */
	public function prepare_item_for_database( $request ) {
		$body = json_decode($request->get_body(), true);

		$collection_id = $body['collection_id'];
		$metadata_id   = $body['metadata_id'];
		$filter = $body['filter'];

		$received_type = $body['filter_type'];

		if(empty($received_type)){
			throw new \InvalidArgumentException('The type can\'t be empty');
		} elseif(!strrchr($received_type, '_')){
			$received_type = ucfirst(strtolower($received_type));
		} else {
			$received_type = ucwords(strtolower($received_type), '_');
		}

		$type = "Tainacan\Filter_Types\\$received_type";

		$filter_type = new $type();

		foreach ($filter as $attribute => $value){
			try {
				$set_ = 'set_'. $attribute;
				$this->filter->$set_($value);
			} catch (\Error $error){
				//
			}
		}

		$this->filter->set_collection_id($collection_id);
		$this->filter->set_metadata($metadata_id);
		$this->filter->set_filter_type($filter_type);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		if(!empty($request->get_body())){
			$this->prepare_item_for_database($request);

			if ($this->filter->validate()){
				$filter_inserted = $this->filter_repository->insert($this->filter);

				return new WP_REST_Response($filter_inserted->__toArray(), 200);
			}

			return new WP_REST_Response([
				'error_message' => 'One or more attributes are invalid',
				'error'         => $this->filter->get_errors()
			], 400);
		}

		return new WP_REST_Response([
			'error_message' => 'The body could not be empty.',
			'body'          => $request->get_body()
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		$body = json_decode($request->get_body(), true);

		$metadata = $this->metadata_repository->fetch($body['metadata_id']);
		$collection = $this->collection_repository->fetch($body['collection_id']);

		if(($metadata instanceof Entities\Metadata) && ($collection instanceof Entities\Collection)) {
			return ( (($this->filter_repository->can_edit($this->filter)) === true) === ($metadata->can_edit()) ) === ($collection->can_edit());
		}

		return false;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$filter_id = $request['filter_id'];

		$is_permanently = json_decode($request->get_body(), true);

		if(!empty($is_permanently)){
			$args = [$filter_id, $is_permanently];

			$filter = $this->filter_repository->delete($args);

			return new WP_REST_Response($filter->__toArray(), 200);
		}

		return new WP_REST_Response([
			'error_message' => 'The body could not be empty',
			'body'          => $request->get_body()
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$filter = $this->filter_repository->fetch($request['filter_id']);

		if ($filter instanceof Entities\Filter) {
			return $filter->can_delete();
		}

		return false;
	}

	/**
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$filter_id = $request['filter_id'];

		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$attributes = ['ID' => $filter_id];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$updated_filter = $this->filter_repository->update($attributes);

			return new WP_REST_Response($updated_filter->__toArray(), 200);
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
	 */
	public function update_item_permissions_check( $request ) {
		$filter = $this->filter_repository->fetch($request['filter_id']);

		if ($filter instanceof Entities\Filter) {
			return $filter->can_edit();
		}

		return false;
	}

	/**
	 * @param mixed $object
	 * @param WP_REST_Request $request
	 *
	 * @return array|mixed|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $object, $request ) {

		if(is_array($object)){
			$filters = [];

			foreach ($object as $item){
				$filters[] = $item->__toArray();
			}

			return $filters;
		}

		return $object;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$filters = $this->filter_repository->fetch([], 'OBJECT');

		$response = $this->prepare_item_for_response($filters, $request);

		return new WP_REST_Response($response, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return $this->filter_repository->can_read($this->filter);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$filter_id = $request['filter_id'];

		$filter = $this->filter_repository->fetch($filter_id);

		return new WP_REST_Response($filter->__toArray(), 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$filter = $this->filter_repository->fetch($request['filter_id']);

		if ($filter instanceof Entities\Filter) {
			return $filter->can_read();
		}

		return false;
	}
}
?>