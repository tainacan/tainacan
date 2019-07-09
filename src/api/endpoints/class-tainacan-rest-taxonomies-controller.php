<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Taxonomies_Controller extends REST_Controller {
	private $taxonomy;
	private $taxonomy_repository;

	/**
	 * REST_Taxonomies_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'taxonomies';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->taxonomy = new Entities\Taxonomy();
		$this->taxonomy_repository = Repositories\Taxonomies::get_instance();
		$this->collections_repository = Repositories\Collections::get_instance();
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_collection_params()
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE)
				)
			)
		);
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<taxonomy_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array($this, 'delete_item'),
					'permission_callback' => array($this, 'delete_item_permissions_check'),
					'args'                => array(
						'permanently' => array(
							'description' => __('To delete permanently, you can pass \'permanently\' as true. By default this will only trash collection'),
							'default'     => 'false',
						),
					)
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE)
				)
			)
		);
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<taxonomy_id>[\d]+)/collection/(?P<collection_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array($this, 'update_item'),
					'permission_callback' => array($this, 'update_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE)
				)
			)
		);
	}

	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)) {
			if(!isset($request['fetch_only'])) {
				$item_arr = $item->_toArray();

				if ( $request['context'] === 'edit' ) {
					$item_arr['current_user_can_edit'] = $item->can_edit();
					$item_arr['current_user_can_delete'] = $item->can_delete();
					// $item_arr['collections'] = [];
					// if ( is_array($tax_collections = $item->get_collections()) ) {
					// 	foreach ($tax_collections as $tax_collection) {
					// 		if ( $tax_collection instanceof \Tainacan\Entities\Collection ) {
					// 			$item_arr['collections'][] = $tax_collection->_toArray();
					// 		}
					// 	}
					// 
					// }
				}
				
				/**
				* 
				* 
				* See issue #229
				* 
				*/
				$item_arr['collections'] = [];
				$item_arr['collections_ids'] = [];
				
				$taxonomy = get_taxonomy( $item->get_db_identifier() );
				if (is_object($taxonomy) && isset($taxonomy->object_type) && is_array($taxonomy->object_type)) {
					foreach ($taxonomy->object_type as $slug) {
						
						$tax_collection = Repositories\Collections::get_instance()->fetch_by_db_identifier($slug);
						if ( $tax_collection instanceof \Tainacan\Entities\Collection ) {
							$item_arr['collections'][] = $tax_collection->_toArray();
							$item_arr['collections_ids'][] = $tax_collection->get_id();
						}
						
					}
				}
				
				
				
			} else {
				$attributes_to_filter = $request['fetch_only'];

				$item_arr = $this->filter_object_by_attributes($item, $attributes_to_filter);
			}

			/**
			 * Use this filter to add additional post_meta to the api response
			 * Use the $request object to get the context of the request and other variables
			 * For example, id context is edit, you may want to add your meta or not.
			 * 
			 * Also take care to do any permissions verification before exposing the data
			 */
			$extra_metadata = apply_filters('tainacan-api-response-taxonomy-meta', [], $request);

			foreach ($extra_metadata as $extra_meta) {
				$item_arr[$extra_meta] = get_post_meta($item_arr['id'], $extra_meta, true);
			}
			
			return $item_arr;
		}

		return $item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return object|void|\WP_Error
	 */
	public function prepare_item_for_database( $request ) {
		foreach ($request as $key => $value){
			$set_ = 'set_' . $key;
			$this->taxonomy->$set_($value);
		}
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$taxonomy_id = $request['taxonomy_id'];

		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);
		
		if (! $taxonomy instanceof Entities\Taxonomy) {
			return new \WP_REST_Response([
				'error_message' => __('A taxonomy with this ID was not found', 'tainacan' ),
				'taxonomy_id'   => $taxonomy_id
			], 400);
		}
		
		$taxonomy_prepared = $this->prepare_item_for_response($taxonomy, $request);

		return new \WP_REST_Response($taxonomy_prepared, 200);
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
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {
		$taxonomy_id = $request['taxonomy_id'];
		$permanently = $request['permanently'];
		
		$taxonomy = $this->taxonomy_repository->fetch($taxonomy_id);
		
		if (! $taxonomy instanceof Entities\Taxonomy) {
			return new \WP_REST_Response([
				'error_message' => __('A taxonomy with this ID was not found', 'tainacan' ),
				'taxonomy_id'   => $taxonomy_id
			], 400);
		}
		
		if($permanently == true){
			$deleted = $this->taxonomy_repository->delete($taxonomy);
		} else {
			$deleted = $this->taxonomy_repository->trash($taxonomy);
		}

		if ( $deleted instanceof \WP_Error ) {
			return new \WP_REST_Response( $deleted->get_error_message(), 400 );
		} elseif ( ! $deleted ) {
			return new \WP_REST_Response( [
				'error_message' => __( 'Failed to delete', 'tainacan' ),
				'deleted'       => $deleted
			], 400 );
		} elseif ( ! $deleted ) {
			return new \WP_REST_Response( $deleted, 400 );
		}

		return new \WP_REST_Response( $this->prepare_item_for_response( $deleted, $request ), 200 );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$taxonomy = $this->taxonomy_repository->fetch($request['taxonomy_id']);

		if ($taxonomy instanceof Entities\Taxonomy) {
			return $taxonomy->can_delete();
		}

		return false;

	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {
		$args = $this->prepare_filters($request);

		$taxonomies = $this->taxonomy_repository->fetch($args);

		$response = [];
		if($taxonomies->have_posts()){
			while ($taxonomies->have_posts()){
				$taxonomies->the_post();

				$taxonomy = new Entities\Taxonomy($taxonomies->post);

				array_push($response, $this->prepare_item_for_response($taxonomy, $request));
			}

			wp_reset_postdata();
		}

		$total_taxonomies  = (int) $taxonomies->found_posts;
		$max_pages = ceil($total_taxonomies / (int) $taxonomies->query_vars['posts_per_page']);

		$rest_response = new \WP_REST_Response($response, 200);

		$rest_response->header('X-WP-Total', $total_taxonomies);
		$rest_response->header('X-WP-TotalPages', (int) $max_pages);

		$total_taxonomies = wp_count_posts( 'tainacan-taxonomy', 'readable' );

		if (isset($total_taxonomies->publish) ||
		    isset($total_taxonomies->private) ||
		    isset($total_taxonomies->trash) ||
		    isset($total_taxonomies->draft)) {

			$rest_response->header('X-Tainacan-total-taxonomies-trash', $total_taxonomies->trash);
			$rest_response->header('X-Tainacan-total-taxonomies-publish', $total_taxonomies->publish);
			$rest_response->header('X-Tainacan-total-taxonomies-draft', $total_taxonomies->draft);
			$rest_response->header('X-Tainacan-total-taxonomies-private', $total_taxonomies->private);
		}

		return $rest_response;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if('edit' === $request['context'] && !is_user_logged_in()) {
			return false;
		}
		// if(!$this->taxonomy_repository->can_read($this->taxonomy)) {
		// 	return false;
		// }

		return true;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {
		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$this->prepare_item_for_database($body);

			if($this->taxonomy->validate()){
				$taxonomy = $this->taxonomy_repository->insert($this->taxonomy);

				return new \WP_REST_Response($this->prepare_item_for_response($taxonomy, $request), 201);
			} else {
				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $this->taxonomy->get_errors(),
					'item_metadata' => $this->prepare_item_for_response($this->taxonomy, $request),
				], 400);
			}
		} else {
			return new \WP_REST_Response([
				'error_message' => __('Body can not be empty.', 'tainacan'),
				'body'          => $body
			], 400);
		}
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		return $this->taxonomy_repository->can_edit($this->taxonomy);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
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

					return new \WP_REST_Response($this->prepare_item_for_response($updated_taxonomy, $request), 200);
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_taxonomy->get_errors(),
					'taxonomy'      => $this->prepare_item_for_response($prepared_taxonomy, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('A taxonomy with this ID was not found', 'tainacan' ),
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
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args_for_item_schema( $method = null ) {
		$endpoint_args = [];
		if($method === \WP_REST_Server::READABLE) {
			$endpoint_args['fetch_only'] = array(
				'type'        => 'string/array',
				'description' => __( 'Fetch only specific attribute. The specifics attributes are the same in schema.' ),
			);

			$endpoint_args['context'] = array(
				'type'    => 'string',
				'default' => 'view',
				'items'   => array( 'view, edit' )
			);
		} elseif ($method === \WP_REST_Server::CREATABLE || $method === \WP_REST_Server::EDITABLE) {
			$map = $this->taxonomy_repository->get_map();

			foreach ($map as $mapped => $value){
				$set_ = 'set_'. $mapped;

				// Show only args that has a method set
				if( !method_exists($this->taxonomy, "$set_") ){
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

		$query_params = array_merge($query_params, parent::get_collection_params('tax'));

		$query_params['name'] = array(
			'description' => __('Limits the result set to a taxonomy with a specific name.'),
			'type'        => 'string',
		);

		$query_params = array_merge($query_params, parent::get_meta_queries_params());

		return $query_params;
	}
}

?>