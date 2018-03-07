<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Taxonomies_Controller extends TAINACAN_REST_Controller {
	private $taxonomy;
	private $taxonomy_repository;

	/**
	 * TAINACAN_REST_Taxonomies_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'taxonomies';

		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->taxonomy = new Entities\Taxonomy();
		$this->taxonomy_repository = new Repositories\Taxonomies();
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
				)
			)
		);
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<taxonomy_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check')
				)
			)
		);
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<taxonomy_id>[\d]+)/collection/(?P<collection_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check')
				)
			)
		);
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)) {
			if(!isset($request['fetch_only'])) {
				$item_arr = $item->__toArray();

				if ( $request['context'] === 'edit' ) {
					$item_arr['current_user_can_edit'] = $item->can_edit();
				}
			} else {
				$attributes_to_filter = $request['fetch_only'];

				$item_arr = $this->filter_object_by_attributes($item, $attributes_to_filter);
			}

			return $item_arr;
		}

		return $item;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return object|void|WP_Error
	 */
	public function prepare_item_for_database( $request ) {
		foreach ($request as $key => $value){
			$set_ = 'set_' . $key;
			$this->taxonomy->$set_($value);
		}
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$taxonomy_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);

		$taxonomy_prepared = $this->prepare_item_for_response($taxonomy, $request);

		return new WP_REST_Response($taxonomy_prepared, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

		if(($taxonomy instanceof Entities\Taxonomy)) {
			if('edit' === $request['context'] && !$taxonomy->can_read()) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$taxonomy_id = $request['taxonomy_id'];

		if(empty($request->get_body())){
			return new WP_REST_Response([
				'error_message' => __('Body can not be empty.', 'tainacan'),
				'body'          => $request->get_body()
			], 400);
		}

		$is_permanently = json_decode($request->get_body(), true);

		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);

		if(!empty($taxonomy)) {
			$taxonomy_name = $taxonomy->get_db_identifier();

			$args = [ $taxonomy_id, $taxonomy_name, $is_permanently ];

			$deleted = $this->taxonomy_repository->delete( $args );

			if($deleted instanceof WP_Error) {
				return new WP_REST_Response( $deleted->get_error_message(), 400 );
			} elseif(!$deleted){
				return new WP_REST_Response( [
					'error_message' => __('Failure on deleted.', 'tainacan'),
					'deleted'       => $deleted
				], 400 );
			} elseif (!$deleted){
				return new WP_REST_Response($deleted, 400);
			}

			return new WP_REST_Response($this->prepare_item_for_response($deleted, $request), 200);
		}

		return new WP_REST_Response([
			'error_message' => __('Taxonomy with this id ('. $taxonomy_id .') not found.', 'tainacan'),
			'taxonomy'      => $this->prepare_item_for_response($taxonomy, $request)
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

		if ($taxonomy instanceof Entities\Taxonomy) {
			return $taxonomy->can_delete();
		}

		return false;

	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$args = $this->prepare_filters($request);

		$taxonomies = $this->taxonomy_repository->fetch($args, 'OBJECT');

		$response = [];
		foreach ($taxonomies as $taxonomy) {
			array_push($response, $this->prepare_item_for_response( $taxonomy, $request ));
		}

		return new WP_REST_Response($response, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if('edit' === $request['context'] && !$this->taxonomy_repository->can_read($this->taxonomy)) {
			return false;
		}

		return true;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$this->prepare_item_for_database($body);

			if($this->taxonomy->validate()){
				$taxonomy = $this->taxonomy_repository->insert($this->taxonomy);

				return new WP_REST_Response($this->prepare_item_for_response($taxonomy, $request), 201);
			} else {
				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $this->taxonomy->get_errors(),
					'item_metadata' => $this->prepare_item_for_response($this->taxonomy, $request),
				], 400);
			}
		} else {
			return new WP_REST_Response([
				'error_message' => __('Body can not be empty.', 'tainacan'),
				'body'          => $body
			], 400);
		}
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		return $this->taxonomy_repository->can_edit($this->taxonomy);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$taxonomy_id = $request['taxonomy_id'];

		$body = json_decode($request->get_body(), true);

		if(!empty($body) || isset($request['collection_id'])){
			$attributes = [];

			if(isset($request['collection_id'])) {
				$collection_id = $request['collection_id'];

				$attributes = [ 'collection' => $collection_id ];
			} else {
				foreach ( $body as $att => $value ) {
					$attributes[ $att ] = $value;
				}
			}

			$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);

			if($taxonomy){
				$prepared_taxonomy = $this->prepare_item_for_updating($taxonomy, $attributes);

				if($prepared_taxonomy->validate()){
					$updated_taxonomy = $this->taxonomy_repository->update($prepared_taxonomy);

					return new WP_REST_Response($this->prepare_item_for_response($updated_taxonomy, $request), 200);
				}

				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_taxonomy->get_errors(),
					'taxonomy'      => $this->prepare_item_for_response($prepared_taxonomy, $request)
				], 400);
			}

			return new WP_REST_Response([
				'error_message' => __('Taxonomy with that ID not found', 'tainacan' ),
				'taxonomy_id'   => $taxonomy_id
			], 400);
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
	 */
	public function update_item_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

		if ($taxonomy instanceof Entities\Taxonomy) {
			return $taxonomy->can_edit();
		}

		return false;
	}
}

?>