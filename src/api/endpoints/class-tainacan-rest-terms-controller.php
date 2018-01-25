<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Terms_Controller extends TAINACAN_REST_Controller {
	private $term;
	private $terms_repository;
	private $taxonomy;
	private $taxonomy_repository;

	/**
	 * TAINACAN_REST_Terms_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'terms';

		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->term = new Entities\Term();
		$this->terms_repository = new Repositories\Terms();
		$this->taxonomy = new Entities\Taxonomy();
		$this->taxonomy_repository = new Repositories\Taxonomies();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/taxonomy/(?P<taxonomy_id>[\d]+)',
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
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<term_id>[\d]+)/taxonomy/(?P<taxonomy_id>[\d]+)',
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
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check')
				)
			)
		);
	}

	/**
	 * @param WP_REST_Request $to_prepare
	 *
	 * @return object|void|WP_Error
	 */
	public function prepare_item_for_database( $to_prepare ) {
		$attributes = $to_prepare[0];
		$taxonomy = $to_prepare[1];

		foreach ($attributes as $attribute => $value){
			$set_ = 'set_'. $attribute;

			try {
				$this->term->$set_( $value );
			} catch (\Error $error){
				// Do nothing
			}
		}

		$this->term->set_taxonomy($taxonomy);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$taxonomy_id = $request['taxonomy_id'];
		$body = json_decode($request->get_body(), true);

		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);
		$taxonomy_db_identifier = $taxonomy->get_db_identifier();

		if(!empty($body)){
			$to_prepare = [$body, $taxonomy_db_identifier];
			$this->prepare_item_for_database($to_prepare);

			if($this->term->validate()){
				$term_id = $this->terms_repository->insert($this->term);

				$term_inserted = $this->terms_repository->fetch($term_id, $taxonomy);

				return new WP_REST_Response($term_inserted->__toArray(), 200);
			} else {
				return new WP_REST_Response([
					'error_message' => 'One or more attributes are invalid.',
					'errors'        => $this->term->get_errors(),
				], 400);
			}
		}

		return new WP_REST_Response([
			'error_message' => 'The body couldn\'t be empty.',
			'body'          => $body,
		], 400);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		return $this->terms_repository->can_edit($this->term);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$term_id = $request['term_id'];
		$taxonomy_id = $request['taxonomy_id'];

		$taxonomy_name = $this->taxonomy_repository->fetch( $taxonomy_id )->get_db_identifier();

		if(!$taxonomy_name){
			return new WP_REST_Response([
				'error_message' => 'The ID of taxonomy may be incorrect.'
			]);
		}

		$args = [$term_id, $taxonomy_name];

		$is_deleted = $this->terms_repository->delete($args);

		return new WP_REST_Response($is_deleted, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$term = new Entities\Term($this->terms_repository->fetch($request['term_id']));
		return $this->terms_repository->can_delete($term);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$term_id = $request['term_id'];
		$taxonomy_id = $request['taxonomy_id'];

		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$taxonomy_name = $this->taxonomy_repository->fetch($taxonomy_id)->get_db_identifier();

			$identifiers = [
				'term_id' => $term_id,
				'tax_name'  => $taxonomy_name
			];

			$attributes = [];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$updated_term = $this->terms_repository->update([$attributes, $identifiers]);

			return new WP_REST_Response($updated_term->__toArray(), 200);
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
		$term = new Entities\Term($this->terms_repository->fetch($request['term_id']));
		return $this->terms_repository->can_edit($term);
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return array|mixed|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {

		if(is_array($item)){
			$prepared = [];

			foreach ($item as $term){
				$prepared[] = $term->__toArray();
			}

			return $prepared;
		}

		return $item;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$taxonomy_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);

		$args = json_decode($request->get_body(), true);

		$terms = $this->terms_repository->fetch($args, $taxonomy);

		$prepared_terms = $this->prepare_item_for_response($terms, $request);

		return new WP_REST_Response($prepared_terms, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);
		return $this->taxonomy_repository->can_read($taxonomy);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$term_id = $request['term_id'];
		$tax_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($tax_id);

		$term = $this->terms_repository->fetch($term_id, $taxonomy);

		return new WP_REST_Response($term->__toArray(), 200);
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
}

?>