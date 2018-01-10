<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Filters_Controller extends WP_REST_Controller {
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

		$this->collection = new Entities\Collection();
		$this->collection_repository = new Repositories\Collections();

		$this->metadata = new Entities\Metadata();
		$this->metadata_repository = new Repositories\Metadatas();

		$this->filter = new Entities\Filter();
		$this->filter_repository = new Repositories\Filters();

		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'create_item'),
				'permission_callback' => array($this, 'create_item_permissions_check')
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
		return $this->filter_repository->can_edit($this->filter);
	}
}

?>