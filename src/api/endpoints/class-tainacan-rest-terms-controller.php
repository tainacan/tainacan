<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Terms_Controller extends REST_Controller {
	private $term;
	private $terms_repository;
	private $taxonomy;
	private $taxonomy_repository;

	/**
	 * REST_Terms_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'terms';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->term = new Entities\Term();
		$this->terms_repository = Repositories\Terms::get_instance();
		$this->taxonomy = new Entities\Taxonomy();
		$this->taxonomy_repository = Repositories\Taxonomies::get_instance();
	}

	public function register_routes() {
		register_rest_route($this->namespace,  '/taxonomy/(?P<taxonomy_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE)
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params()
				)
			)
		);
		register_rest_route($this->namespace,'/taxonomy/(?P<taxonomy_id>[\d]+)/'. $this->rest_base . '/(?P<term_id>[\d]+)' ,
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
					'args'                => [
						'info' => [
							'description' => __('Delete term permanently.')
						]
					]
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE)
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
				)
			)
		);
	}

	/**
	 * @param \WP_REST_Request $to_prepare
	 *
	 * @return object|void|\WP_Error
	 */
	public function prepare_item_for_database( $to_prepare ) {
		$attributes = $to_prepare[0];
		$taxonomy = $to_prepare[1];

		foreach ($attributes as $attribute => $value){
			$this->term->set($attribute, $value);
		}

		$this->term->set_taxonomy($taxonomy);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
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

				return new \WP_REST_Response($this->prepare_item_for_response($term_inserted, $request), 200);
			} else {
				return new \WP_REST_Response([
					'error_message' => 'One or more attributes are invalid.',
					'errors'        => $this->term->get_errors(),
				], 400);
			}
		}

		return new \WP_REST_Response([
			'error_message' => 'The body couldn\'t be empty.',
			'body'          => $body,
		], 400);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function create_item_permissions_check( $request ) {
        $taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

        if ($taxonomy instanceof Entities\Taxonomy) {
            return $taxonomy->can_edit();
        }

        return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {
		$term_id = $request['term_id'];
		$taxonomy_id = $request['taxonomy_id'];
		
		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);

		$term = $this->terms_repository->fetch($term_id, $taxonomy);
		
		if ( ! $term instanceof Entities\Term ) {
			return new \WP_REST_Response([
				'error_message' => __('A term with this ID was not found', 'tainacan' ),
				'taxonomy_id'   => $taxonomy_id,
				'term_id'   => $term_id
			], 400);
		}

		$is_deleted = $this->terms_repository->delete($term);

		return new \WP_REST_Response($is_deleted, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
        $taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

        if ($taxonomy instanceof Entities\Taxonomy) {
            return $taxonomy->can_edit();
        }

        return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
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

					return new \WP_REST_Response($this->prepare_item_for_response($updated_term, $request), 200);
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_term->get_errors(),
					'term'          => $this->prepare_item_for_response($prepared_term, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('Term or Taxonomy with this ID was not found', 'tainacan' ),
				'term_id'       => $term_id,
				'taxonomy_id'   => $taxonomy_id
			], 400);
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
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
	 * @param \WP_REST_Request $request
	 *
	 * @return array|mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)){
			if(!isset($request['fetch_only'])) {
				$item_arr = $item->_toArray();

				if ( $request['context'] === 'edit' ) {
					$item_arr['current_user_can_edit'] = $item->can_edit();
					$item_arr['current_user_can_delete'] = $item->can_delete();
				}

				$children =  get_terms([
					'taxonomy' => $item_arr['taxonomy'],
					'parent' => $item_arr['id'],
					'fields' => 'ids',
					'hide_empty' => false,
				]);

				$item_arr['total_children'] = count($children);
			} else {
				$attributes_to_filter = $request['fetch_only'];

				$item_arr = $this->filter_object_by_attributes($item, $attributes_to_filter);
			}

			/**
			 * Use this filter to add additional term_meta to the api response
			 * Use the $request object to get the context of the request and other variables
			 * For example, id context is edit, you may want to add your meta or not.
			 * 
			 * Also take care to do any permissions verification before exposing the data
			 */
			$extra_metadata = apply_filters('tainacan-api-response-term-meta', [], $request);

			foreach ($extra_metadata as $extra_meta) {
				$item_arr[$extra_meta] = get_term_meta($item_arr['id'], $extra_meta, true);
			}
			
			return $item_arr;
		}

		return $item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {
		$taxonomy_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);

		$args = $this->prepare_filters($request);
		$prepared_args = $args;

		$terms = $this->terms_repository->fetch($args, $taxonomy);

		$response = [];
		foreach ($terms as $term) {
			array_push($response, $this->prepare_item_for_response( $term, $request ));
		}

		$response = new \WP_REST_Response($response, 200);

		if(isset($args['number'], $args['offset'])){
			unset( $args['number'], $args['offset'] );
			$total_terms = wp_count_terms( $taxonomy->get_db_identifier(), $args );

			if ( ! $total_terms ) {
				$total_terms = 0;
			}

			$per_page = (int) $prepared_args['number'];
			$page     = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );
		
			$response->header( 'X-WP-Total', (int) $total_terms );
		
			$max_pages = ceil( $total_terms / $per_page );
		
			$response->header( 'X-WP-TotalPages', (int) $max_pages );
		}
		
		return $response;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

		if(($taxonomy instanceof Entities\Taxonomy)) {
			if('edit' === $request['context'] && !is_user_logged_in()) {
				return false;
			}
			if(!$taxonomy->can_read()) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$term_id = $request['term_id'];
		$tax_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($tax_id);

		$term = $this->terms_repository->fetch($term_id, $taxonomy);
		
		if ( ! $term instanceof Entities\Term ) {
			return new \WP_REST_Response([
				'error_message' => __('A term with this ID was not found', 'tainacan' ),
				'taxonomy_id'   => $tax_id,
				'term_id'   => $term_id
			], 400);
		}

		return new \WP_REST_Response($this->prepare_item_for_response($term, $request), 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

		if(($taxonomy instanceof Entities\Taxonomy)) {
			if('edit' === $request['context'] && !is_user_logged_in()) {
				return false;
			}
			if(!$taxonomy->can_read()) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args_for_item_schema( $method = null ) {
		$endpoint_args = [];
		if($method === \WP_REST_Server::READABLE) {
			$endpoint_args['context'] = array(
				'type'    => 'string',
				'default' => 'view',
				'items'   => array( 'view, edit' )
			);
		} elseif ($method === \WP_REST_Server::CREATABLE || $method === \WP_REST_Server::EDITABLE) {
			$map = $this->terms_repository->get_map();

			foreach ($map as $mapped => $value){
				$set_ = 'set_'. $mapped;

				// Show only args that has a method set
				if( !method_exists($this->term, "$set_") ){
					unset($map[$mapped]);
				}
			}

			$endpoint_args = $map;
		}

		return $endpoint_args;
	}

	/**
	 *
	 * Return the queries supported when getting a collection of objects
	 *
	 * @param null $object_name
	 *
	 * @return array
	 */
	public function get_collection_params($object_name = null) {
		$query_params['context']['default'] = 'view';

		$query_params = array_merge($query_params, parent::get_collection_params('term'));

		$query_params['name'] = array(
			'description' => __('Limits the result set to terms with a specific name'),
			'type'        => 'string',
		);

		$query_params = array_merge($query_params, parent::get_meta_queries_params());

		return $query_params;
	}
}

?>