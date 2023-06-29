<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Repositories;
use Tainacan\Entities;
use Tainacan\Entities\Collection;

/**
 * Represents the Collections REST Controller
 *
 * @uses Entities\Collection and Repositories\Collections
 * */
class REST_Collections_Controller extends REST_Controller {
	private $collections_repository;
	private $collection;
	private $items_repository;

	/**
	 * REST_Collections_Controller constructor.
	 * Define the namespace, rest base and instantiate your attributes.
	 */
	public function __construct(){
		$this->rest_base = 'collections';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->collections_repository = Repositories\Collections::get_instance();
		$this->items_repository = Repositories\Items::get_instance();
		$this->collection = new Entities\Collection();
	}

	/**
	 * Register the collections route and their endpoints
	 */
	public function register_routes(){
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE),
			),
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'create_item'),
				'permission_callback' => array($this, 'create_item_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
			),
			'schema'                => [$this, 'get_list_schema'],
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<collection_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check'),
				'args'                => [
					'collection_id' => [
						'description' => __( 'Collection ID', 'tainacan' ),
						'required' => true,
					]
				],
			),
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_item'),
				'permission_callback' => array($this, 'update_item_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE),
			),
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'delete_item_permissions_check'),
				'args'                => array(
					'collection_id' => [
						'description' => __( 'Collection ID', 'tainacan' ),
						'required' => true,
					],
					'permanently' => array(
						'description' => __('To delete permanently, you can pass \'permanently\' as 1. By default this will only trash collection'),
						'default'     => '0',
					),
				)
			),
			'schema'                => [$this, 'get_schema'],
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<collection_id>[\d]+)/metadata_section_order', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_metadata_section_order'),
				'permission_callback' => array($this, 'update_metadata_section_order_permissions_check'),
				'args'                => [
					'collection_id' => [
						'description' => __( 'Collection ID', 'tainacan' ),
						'required' => true,
					],
					'metadata_section_order' => $this->get_endpoint_arg_for_schema('metadata_section_order', ['required' => true])
				],
			),
			'schema'                => [$this, 'get_schema'],
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<collection_id>[\d]+)/metadata_section/default_section/metadata_order', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_metadata_order'),
				'permission_callback' => array($this, 'update_metadata_order_permissions_check'),
				'args'                => [
					'metadata_order' => $this->get_endpoint_arg_for_schema('metadata_order', [
						'required' => true,
						'validate_callback' => [$this, 'validate_filters_metadata_order']
					])
				],
			),
			'schema'                => [$this, 'get_schema'],
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<collection_id>[\d]+)/metadata_section/(?P<metadata_section_id>[\d]+)/metadata_order', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_metadata_order'),
				'permission_callback' => array($this, 'update_metadata_order_permissions_check'),
				'args'                => [
					'metadata_order' => $this->get_endpoint_arg_for_schema('metadata_order', [
						'required' => true,
						'validate_callback' => [$this, 'validate_filters_metadata_order']
					])
				],
			),
			'schema'                => [$this, 'get_schema'],
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<collection_id>[\d]+)/filters_order', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_filters_order'),
				'permission_callback' => array($this, 'update_filters_order_permissions_check'),
				'args'                => [
					'filters_order' => $this->get_endpoint_arg_for_schema('filters_order', [
						'required' => true,
						'validate_callback' => [$this, 'validate_filters_metadata_order']
					])
				],
			),
			'schema'                => [$this, 'get_schema'],
		));
	}

	/**
	 * Return a array of Collections objects in JSON
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items($request){
		$args = $this->prepare_filters($request);

		$collections = $this->collections_repository->fetch($args);

		$response = [];
		if($collections->have_posts()){
			while ($collections->have_posts()){
				$collections->the_post();

				$collection = new Entities\Collection($collections->post);

				array_push($response, $this->prepare_item_for_response($collection, $request));
			}

			wp_reset_postdata();
		}

		$total_collections  = $collections->found_posts;
		$max_pages = ceil($total_collections / (int) $collections->query_vars['posts_per_page']);

		$rest_response = new \WP_REST_Response($response, 200);

		$rest_response->header('X-WP-Total', (int) $total_collections);
		$rest_response->header('X-WP-TotalPages', (int) $max_pages);

		$total_collections = wp_count_posts( 'tainacan-collection', 'readable' );

		if (isset($total_collections->publish) ||
			isset($total_collections->private) ||
			isset($total_collections->trash) ||
			isset($total_collections->draft)) {

			$rest_response->header('X-Tainacan-total-collections-trash', $total_collections->trash);
			$rest_response->header('X-Tainacan-total-collections-publish', $total_collections->publish);
			$rest_response->header('X-Tainacan-total-collections-draft', $total_collections->draft);
			$rest_response->header('X-Tainacan-total-collections-private', $total_collections->private);
		}

		return $rest_response;
	}

	/**
	 * Return a Collection object in JSON
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item($request){
		$collection_id = $request['collection_id'];
		$collection = $this->collections_repository->fetch($collection_id);

		$response = $this->prepare_item_for_response($collection, $request );

		return new \WP_REST_Response($response, 200);
	}

	/**
	 * @param \Tainacan\Entities\Collection $collection
	 *
	 * @return array
	 */
	private function get_preview_image_items($collection, $amount) {
		if($amount <= 0 )
			return [];

		$collection_id = $collection->get_id();

		$args = [
			'meta_query' => [
				[
					'key' => 'collection_id',
					'value' => $collection_id
				]
			],
			'posts_per_page' => $amount
		];

		$items = $this->items_repository->fetch($args, [], 'OBJECT');
		$thumbnails = array_map( function($item) {
			$images = array();
			$images['thumbnail'] = $item->get_thumbnail();
			$images['document_mimetype'] = $item->get_document_mimetype();
			return $images;
		 }, $items);

		return $thumbnails;
	}

	/**
	 *
	 * Receive a \WP_Query or a Collection object and return both in JSON
	 *
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|string|void|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response($item, $request){
		if ( !empty($item) ) {

			if( !isset($request['fetch_only']) ) {

				$item_arr = $item->_toArray();

				if ( $request['context'] === 'edit' ) {
					$item_arr['current_user_can_edit'] = $item->can_edit();
					$item_arr['current_user_can_delete'] = $item->can_delete();

					$collection_caps = \tainacan_roles()->get_collection_caps_slugs();
					foreach ($collection_caps as $ccap) {
						if ( strpos($ccap, 'tnc_col_') !== 0 ) {
							continue;
						}
						$cap_key = str_replace( 'tnc_col_%d_', 'current_user_can_', $ccap );
						$cap_check = str_replace( '%d', $item->get_id(), $ccap );
						$item_arr[$cap_key] = current_user_can( $cap_check );
					}

				}

			} else {
				$attributes_to_filter = $request['fetch_only'];

				# Always returns id
				if(is_array($attributes_to_filter)) {
					$attributes_to_filter[] = 'id';
				} elseif(!strstr($attributes_to_filter, ',')){
					$attributes_to_filter = array($attributes_to_filter, 'id');
				} else {
					$attributes_to_filter .= ',id';
				}

				$item_arr = $this->filter_object_by_attributes($item, $attributes_to_filter);

				if ( $request['context'] === 'edit' ) {
					$item_arr['current_user_can_edit'] = $item->can_edit();
					$item_arr['current_user_can_delete'] = $item->can_delete();

					$collection_caps = \tainacan_roles()->get_collection_caps_slugs();
					foreach ($collection_caps as $ccap) {
						if ( strpos($ccap, 'tnc_col_') !== 0 ) {
							continue;
						}
						$cap_key = str_replace( 'tnc_col_%d_', 'current_user_can_', $ccap );
						$cap_check = str_replace( '%d', $item->get_id(), $ccap );
						$item_arr[$cap_key] = current_user_can( $cap_check );
					}
				}

				$item_arr['url'] = get_permalink( $item_arr['id'] );
			}

			if ( isset($request['fetch_preview_image_items']) && $request['fetch_preview_image_items'] != 0 ) {
				$item_arr['preview_image_items'] = $this->get_preview_image_items($item, $request['fetch_preview_image_items']);
			}

			$total_items = wp_count_posts( $item->get_db_identifier(), 'readable' );

			if (isset($total_items->publish) ||
				isset($total_items->private) ||
			  	isset($total_items->trash) ||
			   	isset($total_items->draft)) {

				$item_arr['total_items']['trash'] = $total_items->trash;
				$item_arr['total_items']['publish'] = $total_items->publish;
				$item_arr['total_items']['draft'] = $total_items->draft;
				$item_arr['total_items']['private'] = $total_items->private;
			}

			// Clear private metadata from metadata_order
			if ( isset($item_arr['metadata_order']) && is_array( $item_arr['metadata_order'] ) && ! current_user_can( 'tnc_col_' . $item->get_id() . '_read_private_metadata' ) ) {

				$metadata = $item->get_metadata();
				$meta_ids = array_map(
					function($m) {
						return $m->get_id();
					},
					$metadata
				);
				$item_arr['metadata_order'] = \array_values( \array_filter(
					$item_arr['metadata_order'],
					function($el) use ($meta_ids) {
						return in_array($el['id'], $meta_ids);
					}
				) );

			}

			// Clear private filters from filters_order
			if ( isset($item_arr['filters_order']) && is_array( $item_arr['filters_order'] ) && ! current_user_can( 'tnc_col_' . $item->get_id() . '_read_private_filters' ) ) {

				$filters = $item->get_filters();
				$filters_ids = array_map(
					function($f) {
						return $f->get_id();
					},
					$filters
				);
				$item_arr['filters_order'] = \array_values( \array_filter(
					$item_arr['filters_order'],
					function($el) use ($filters_ids) {
						return in_array($el['id'], $filters_ids);
					}
				) );

			}

			/**
			 * Use this filter to add additional post_meta to the api response
			 * Use the $request object to get the context of the request and other variables
			 * For example, id context is edit, you may want to add your meta or not.
			 *
			 * Also take care to do any permissions verification before exposing the data
			 */
			$extra_metadata = apply_filters('tainacan-api-response-collection-meta', [], $request);

			foreach ($extra_metadata as $extra_meta) {
				$item_arr[$extra_meta] = get_post_meta($item_arr['id'], $extra_meta, true);
			}

			return $item_arr;
		}

		return $item;
	}

	/**
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_items_permissions_check($request){
		return true;
	}

	/**
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function get_item_permissions_check($request){
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if(($collection instanceof Entities\Collection)) {
			return $collection->can_read();
		}

		return false;
	}

	/**
	 * Receive a JSON with the structure of a Collection and return, in case of success insert
	 * a Collection object in JSON
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {
		$body = json_decode($request->get_body(), true);

		if(empty($body)){
			return new \WP_REST_Response([
				'error_message' => __('Body cannot be empty.', 'tainacan'),
				'collection'    => $body
			], 400);
		}

		$this->collection = new Collection();

		try {
			$prepared_post = $this->prepare_item_for_database( $body );
		} catch (\Exception $exception){
			return new \WP_REST_Response($exception->getMessage(), 400);
		}

		if($prepared_post->validate()) {
			$collection = $this->collections_repository->insert( $prepared_post );
			$response = $this->prepare_item_for_response($collection, $request);

			do_action('tainacan-api-collection-created', $response, $request);

			return new \WP_REST_Response($response, 201);
		}

		return new \WP_REST_Response([
			'error_message' => __('One or more values are invalid.', 'tainacan'),
			'errors'        => $prepared_post->get_errors(),
			'collection'    => $this->prepare_item_for_response($prepared_post, $request)
		], 400);
	}

	/**
	 * Verify if current has permission to create a item
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function create_item_permissions_check( $request ) {
		return  current_user_can($this->collections_repository->get_capabilities()->edit_posts);
	}

	/**
	 * Prepare collection for insertion on database
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return object|Entities\Collection|\WP_Error
	 */
	public function prepare_item_for_database( $request ) {

		foreach ($request as $key => $value){
			$set_ = 'set_' . $key;
			if(method_exists($this->collection, $set_)) $this->collection->$set_($value);
		}

		return $this->collection;
	}

	/**
	 * Delete a collection
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {
		$permanently = $request['permanently'];
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if(! $collection instanceof Entities\Collection) {
			return new \WP_REST_Response([
				'error_message' => __('Collection with this ID was not found', 'tainacan' ),
				'collection_id' => $request['collection_id']
			], 400);
		}

		if($permanently == true) {
			$collection = $this->collections_repository->delete($collection);
		} else {
			$collection = $this->collections_repository->trash($collection);
		}

		$prepared_collection = $this->prepare_item_for_response($collection, $request);

		return new \WP_REST_Response($prepared_collection, 200);
	}

	/**
	 * Verify if current user has permission to delete a collection
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function delete_item_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection) {
			return $collection->can_delete();
		}

		return false;
	}

	/**
	 * Update a collection
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
		$collection_id = $request['collection_id'];

		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$attributes = [];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$collection = $this->collections_repository->fetch($collection_id);

			if($collection) {
				$prepared_collection = $this->prepare_item_for_updating( $collection, $attributes );

				if ( $prepared_collection->validate() ) {
					$updated_collection = $this->collections_repository->update( $collection );

					$response = $this->prepare_item_for_response($updated_collection, $request);

					return new \WP_REST_Response( $response, 200 );
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_collection->get_errors(),
					'collection'    => $this->prepare_item_for_response($prepared_collection, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('Collection with this ID was not found', 'tainacan' ),
				'collection_id' => $collection_id
			], 400);
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}


	/**
	 * Verify if current user has permission to update a item
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function update_item_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection) {
			return $collection->can_edit();
		}

		return false;
	}

	public function validate_filters_metadata_order($value, $request, $param) {

		if ( is_array($value) ) {
			foreach ($value as $val) {
				if ( !is_array($val) ) {
					return false;
				}
				if ( !isset($val['id']) || !is_numeric($val['id']) ) {
					return false;
				}
				if ( !isset($val['enabled']) || !is_bool($val['enabled']) ) {
					return false;
				}

			}
			return true;
		}
		return false;

	}

	public function validate_metadata_section_order($value, $request, $param) {
		if ( is_array($value) ) {
			foreach ($value as $val) {
				if ( !is_array($val) ) {
					return false;
				}
				if ( !isset($val['id']) || (!is_numeric($val['id']) && $val['id'] !=  \Tainacan\Entities\Metadata_Section::$default_section_slug ) ) {
					return false;
				}
				if ( !isset($val['enabled']) || !is_bool($val['enabled']) ) {
					return false;
				}
				if ( !isset($val['metadata_order']) || !is_array($val['metadata_order']) ) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Update a collection metadata order
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function update_metadata_order( $request ) {
		$collection_id = $request['collection_id'];
		$metadata_section_id = isset($request['metadata_section_id']) ? $request['metadata_section_id'] : \Tainacan\Entities\Metadata_Section::$default_section_slug;

		$body = json_decode($request->get_body(), true);

		if( !empty($body) && isset($body['metadata_order']) ) {

			$collection = $this->collections_repository->fetch($collection_id);

			if( $collection instanceof Entities\Collection) {
				$metadata_section_order = $collection->get_metadata_section_order();
				if( !isset( $metadata_section_order ) || !is_array($metadata_section_order) ) {
					$metadata_section_order = array();
				}

				$section_order_index = array_search( $metadata_section_id, array_column( $metadata_section_order, 'id' ) );
				if ( $section_order_index !== false ) {
					$metadata_section_order[$section_order_index]['metadata_order'] = $body['metadata_order'];
				} else {
					$metadata_section_order[] = array(
						'id' => $metadata_section_id,
						'metadata_order' => $body['metadata_order'],
						'enabled' => true
					);
				}
				$collection->set_metadata_section_order( $metadata_section_order );

				if ( $collection->validate() ) {
					$updated_collection = $this->collections_repository->update( $collection );
					$response = $this->prepare_item_for_response($updated_collection, $request);
					return new \WP_REST_Response( $response, 200 );
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $collection->get_errors(),
					'collection'    => $this->prepare_item_for_response($collection, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('Collection with this ID was not found', 'tainacan' ),
				'collection_id' => $collection_id
			], 400);
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}

	/**
	 * Update a collection metadata section order
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function update_metadata_section_order( $request ) {
		$collection_id = $request['collection_id'];

		$body = json_decode($request->get_body(), true);

		if( !empty($body) && isset($body['metadata_section_order']) ) {

			$collection = $this->collections_repository->fetch($collection_id);

			if( $collection instanceof Entities\Collection) {
				$collection->set_metadata_section_order( $body['metadata_section_order'] );

				if ( $collection->validate() ) {
					$updated_collection = $this->collections_repository->update( $collection );

					$response = $this->prepare_item_for_response($updated_collection, $request);

					return new \WP_REST_Response( $response, 200 );
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $collection->get_errors(),
					'collection'    => $this->prepare_item_for_response($collection, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('Collection with this ID was not found', 'tainacan' ),
				'collection_id' => $collection_id
			], 400);
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}


	/**
	 * Verify if current user has permission to update metadata order
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function update_metadata_order_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection) {
			return $collection->user_can( 'edit_metadata' );
		}

		return false;
	}

	/**
	 * Verify if current user has permission to update metadata section order
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function update_metadata_section_order_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection) {
			return $collection->user_can( 'edit_metadata' ); // && $collection->user_can( 'edit_metadata_section' );
		}

		return false;
	}
	

	/**
	 * Update a collection metadata order
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return string|\WP_Error|\WP_REST_Response
	 */
	public function update_filters_order( $request ) {
		$collection_id = $request['collection_id'];

		$body = json_decode($request->get_body(), true);

		if( !empty($body) && isset($body['filters_order']) ) {

			$collection = $this->collections_repository->fetch($collection_id);

			if( $collection instanceof Entities\Collection) {
				$collection->set_filters_order( $body['filters_order'] );

				if ( $collection->validate() ) {
					$updated_collection = $this->collections_repository->update( $collection );

					$response = $this->prepare_item_for_response($updated_collection, $request);

					return new \WP_REST_Response( $response, 200 );
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $collection->get_errors(),
					'collection'    => $this->prepare_item_for_response($collection, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('Collection with this ID was not found', 'tainacan' ),
				'collection_id' => $collection_id
			], 400);
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $body
		], 400);
	}


	/**
	 * Verify if current user has permission to update metadata order
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function update_filters_order_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection) {
			return $collection->user_can( 'edit_filters' );
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

		switch ( $method ) {
			case \WP_REST_Server::READABLE:
				$endpoint_args['name'] = array(
					'description' => __('Limits the result set to collections with a specific name'),
					'type'        => 'string',
				);
	
				$endpoint_args = array_merge(
					$endpoint_args,
					parent::get_wp_query_params(),
					parent::get_fetch_only_param(),
					parent::get_meta_queries_params()
				);
			break;
			case \WP_REST_Server::CREATABLE:
			case \WP_REST_Server::EDITABLE:
				$map = $this->collections_repository->get_map();

				foreach ($map as $mapped => $value){
					$set_ = 'set_'. $mapped;

					// Show only args that has a method set
					if( !method_exists($this->collection, "$set_") ){
						unset($map[$mapped]);
					}
				}

				$endpoint_args = $map;
			break;
		}

		return $endpoint_args;
	}

	function get_endpoint_arg_for_schema($name, $properties = [], $repository = null) {
		if ( $repository == null)
			$repository = $this->collections_repository;

		if ( !isset( $repository ) ) {
			return ['title' => $name, 'description' => $name, 'type' => 'string'];
		}

		$map = $repository->get_map();
		if( !isset( $map[$name] ) ) {
			return ['title' => $name, 'description' => $name, 'type' => 'string'];
		}

		$arg = array_merge($map[$name], $properties);
		return $arg;
	}

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'collection',
			'type' => 'object',
			'tags' => [ $this->rest_base ],
		];

		$main_schema = parent::get_repository_schema( $this->collections_repository );
		$permissions_schema = parent::get_permissions_schema();

		$schema['properties'] = array_merge(
			parent::get_base_properties_schema(),
			$main_schema,
			$permissions_schema
		);

		return $schema;
	}

}

?>
