<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Taxonomies_Controller extends WP_REST_Controller {
	private $taxonomy;
	private $taxonomy_repository;

	/**
	 * TAINACAN_REST_Taxonomies_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'taxonomies';

		$this->taxonomy = new Entities\Taxonomy();
		$this->taxonomy_repository = new Repositories\Taxonomies();

		add_action('rest_api_init', array($this, 'register_routes'));
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
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$taxonomies = [];

		if($request['taxonomy_id']){
			return $item->__toArray();
		}

		foreach ( $item as $it ) {
			$taxonomies[] = $it->__toArray();
		}

		return $taxonomies;
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
		return $this->taxonomy_repository->can_read($taxonomy);
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

			return new WP_REST_Response($deleted->__toArray(), 200);
		}

		return new WP_REST_Response([
			'error_message' => __('Taxonomy with this id ('. $taxonomy_id .') not found.', 'tainacan'),
			'taxonomy'       => $taxonomy->__toArray()
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);
		return $this->taxonomy_repository->can_delete($taxonomy);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$taxonomies = $this->taxonomy_repository->fetch([], 'OBJECT');

		$taxonomies_prepared = $this->prepare_item_for_response($taxonomies, $request);

		return new WP_REST_Response($taxonomies_prepared, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return $this->taxonomy_repository->can_read($this->taxonomy);
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

				return new WP_REST_Response($taxonomy->__toArray(), 201);
			} else {
				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $this->taxonomy->get_errors(),
					'item_metadata' => $this->taxonomy->__toArray(),
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

		if(!empty($body)){
			$attributes = ['ID' => $taxonomy_id];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$updated_taxonomy = $this->taxonomy_repository->update($attributes);

			return new WP_REST_Response($updated_taxonomy->__toArray(), 200);
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
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);
		return $this->taxonomy_repository->can_edit($taxonomy);
	}
}

?>