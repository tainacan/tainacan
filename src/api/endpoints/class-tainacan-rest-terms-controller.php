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
		register_rest_route($this->namespace,  '/taxonomy/(?P<taxonomy_id>[\d]+)/' . $this->rest_base,
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
		register_rest_route($this->namespace,'/taxonomy/(?P<taxonomy_id>[\d]+)/'. $this->rest_base . '/(?P<term_id>[\d]+)' ,
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
				$term_inserted = $this->terms_repository->insert($this->term);

				return new WP_REST_Response($this->prepare_item_for_response($term_inserted, $request), 200);
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
        $taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

        if ($taxonomy instanceof Entities\Taxonomy) {
            return $taxonomy->can_edit();
        }

        return false;
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
        $taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

        if ($taxonomy instanceof Entities\Taxonomy) {
            return $taxonomy->can_edit();
        }

        return false;
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
			$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);
			$tax_name = $taxonomy->get_db_identifier();

			$attributes = [];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$term = $this->terms_repository->fetch($term_id, $taxonomy);

			if($term){
				$prepared_term = $this->prepare_item_for_updating($term, $attributes);

				if($prepared_term->validate()){
					$updated_term = $this->terms_repository->update($prepared_term, $tax_name);

					return new WP_REST_Response($this->prepare_item_for_response($updated_term, $request), 200);
				}

				return new WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_term->get_errors(),
					'term'          => $this->prepare_item_for_response($prepared_term, $request)
				], 400);
			}

			return new WP_REST_Response([
				'error_message' => __('Term or Taxonomy with that IDs not found', 'tainacan' ),
				'term_id'       => $term_id,
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

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return array|mixed|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)){
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
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$taxonomy_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);

		$args = $this->prepare_filters($request);

		$terms = $this->terms_repository->fetch($args, $taxonomy);

		$response = [];
		foreach ($terms as $term) {
			array_push($response, $this->prepare_item_for_response( $term, $request ));
		}

		return new WP_REST_Response($response, 200);
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
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
	public function get_item( $request ) {
		$term_id = $request['term_id'];
		$tax_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($tax_id);

		$term = $this->terms_repository->fetch($term_id, $taxonomy);

		return new WP_REST_Response($this->prepare_item_for_response($term, $request), 200);
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
}

?>