<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Repositories;
use Tainacan\Entities;

/**
 * Represents the Items REST Controller
 * @uses Tainacan\Repositories\
 * @uses Tainacan\Entities\
*/
class REST_Items_Controller extends REST_Controller {
	private $items_repository;
	private $item;
	private $item_metadata;
	private $collections_repository;
	private $metadatum_repository;
	private $terms_repository;
	private $filters_repository;
	private $taxonomies_repository;

	/**
	 * REST_Items_Controller constructor.
	 * Define the namespace, rest base and instantiate your attributes.
	 */
	public function __construct() {
		$this->rest_base = 'items';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->items_repository = Repositories\Items::get_instance();
		$this->item = new Entities\Item();
		$this->item_metadata = Repositories\Item_Metadata::get_instance();
		$this->collections_repository = Repositories\Collections::get_instance();
		$this->metadatum_repository = Repositories\Metadata::get_instance();
		$this->terms_repository = \Tainacan\Repositories\Terms::get_instance();
		$this->filters_repository = \Tainacan\Repositories\Filters::get_instance();
		$this->taxonomies_repository = \Tainacan\Repositories\Taxonomies::get_instance();
	}

	/**
	 * Register items routes, and their endpoints
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => array_merge([
							'collection_id' => [
								'description' => __( 'Collection ID', 'tainacan' ),
								'required' => true,
							],
						],
						$this->get_wp_query_params()
					),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
				),
				'schema'                => [$this, 'get_list_schema'],
			)
		);
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<item_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'get_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE),
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
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::DELETABLE),
				),
				'schema'                => [$this, 'get_schema'],
			)
		);
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<item_id>[\d]+)/attachments',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_item_attachments'),
					'permission_callback' => array($this, 'get_item_attachments_permissions_check'),
					'args'                => array_merge([
							'item_id' => [
								'description' => __( 'Item ID', 'tainacan' ),
								'required' => true,
							],
						],
						$this->get_wp_query_params()
					),
				),
				'schema' => [$this, 'get_attachments_schema'],
			)
		);
		register_rest_route(
			$this->namespace, '/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_items'),
					'permission_callback' => array($this, 'get_items_permissions_check'),
					'args'                => $this->get_wp_query_params(),
				),
				'schema'                => [$this, 'get_list_schema'],
			)
		);
		register_rest_route(
			$this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<item_id>[\d]+)/duplicate',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'duplicate_item'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
					'args'                => array(
						'collection_id' => [
							'description' => __( 'Collection ID', 'tainacan' ),
							'type' => 'string',
						],
						'item_id' => [
							'description' => __( 'Item ID', 'tainacan' ),
							'type' => 'string',
						],
						'copies' => array(
							'description' => __('Number of copies to be created', 'tainacan'),
							'default'     => 1,
							'type'        => 'integer'
						),
						'status' => array(
							'description' => __('Try to assign the specified status to the duplicates if they validate. By default it will save them as drafts.', 'tainacan'),
							'type'        => 'string',
							'enum'        => array('draft', 'publish', 'private', 'trash'),
							'default'     => 'draft'
						),
					)
				),
				'schema'                => [$this, 'get_list_schema']
			)
		);
		register_rest_route(
			$this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/submission',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'submission_item'),
					'permission_callback' => array($this, 'submission_item_permissions_check'),
					'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
				),
				'schema'                => [$this, 'get_schema']
			)
		);
		register_rest_route(
			$this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/submission/(?P<submission_id>[a-z0-9]+)/finish',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'submission_item_finish'),
					'permission_callback' => array($this, 'submission_item_permissions_check'),
					'args'                => array(
						'collection_id' => [
							'description' => __( 'Collection ID', 'tainacan' ),
							'type' => 'string',
						],
						'submission_id' => [
							'description' => __( 'Submission ID', 'tainacan' ),
							'type' => 'string',
						],
					)
				),
				'schema'                => [$this, 'get_schema']
			)
		);
	}

	/**
	 * @param $item_object
	 * @param $item_array
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	private function add_metadata_to_item($item_object, $item_array, $args = []){
		$item_metadata = $item_object->get_metadata($args);

		foreach($item_metadata as $index => $me){
			$metadatum               = $me->get_metadatum();
			$slug                = $metadatum->get_slug();
			$item_metadata_array = $me->_toArray();

			$item_array['metadata'][ $slug ]['name']            = $metadatum->get_name();
			$item_array['metadata'][ $slug ]['id']            = $metadatum->get_id();
			if($metadatum->get_metadata_type_object()->get_primitive_type() === 'date') {
				$item_array['metadata'][ $slug ]['date_i18n'] = $item_metadata_array['date_i18n'];
			}
			$item_array['metadata'][ $slug ]['value']           = $item_metadata_array['value'];
			$item_array['metadata'][ $slug ]['value_as_html']   = $item_metadata_array['value_as_html'];
			$item_array['metadata'][ $slug ]['value_as_string'] = $item_metadata_array['value_as_string'];
			$item_array['metadata'][ $slug ]['semantic_uri'] = $metadatum->get_semantic_uri();

			$item_array['metadata'][ $slug ]['multiple']        = $metadatum->get_multiple();
			$item_array['metadata'][ $slug ]['mapping']        = $metadatum->get_exposer_mapping();
		}

		return $item_array;
	}

	/**
	 * @param \Tainacan\Entities\Item $item|int
	 *
	 * @return array
	 */
	public function get_context_edit($item) {
		if(is_numeric($item))
			$item = new Entities\Item($item);

		return array(
			'current_user_can_edit' => $item->can_edit(),
			'current_user_can_delete' => $item->can_delete(),
			'nonces' => array(
				'update-post_' . $item->get_id() => wp_create_nonce('update-post_' . $item->get_id())
			)
		);
	}

	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|string|void|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)){

			/**
			 * Use this filter to add additional post_meta to the api response
			 * Use the $request object to get the context of the request and other variables
			 * For example, id context is edit, you may want to add your meta or not.
			 *
			 * Also take care to do any permissions verification before exposing the data
			 */
			$extra_metadata = apply_filters('tainacan-api-response-item-meta', [], $request);
			$extra_metadata_values = [];

			foreach ($extra_metadata as $extra_meta) {
				$extra_metadata_values[$extra_meta] = get_post_meta($item->get_id(), $extra_meta, true);
			}

			if(!isset($request['fetch_only'])) {
				$item_arr = $item->_toArray();

				$item_arr = array_merge($extra_metadata_values, $item_arr);

				if ( $request['context'] === 'edit' ) {
					$item_arr['current_user_can_edit'] = $item->can_edit();
					$item_arr['current_user_can_delete'] = $item->can_delete();
				}

				$img_size = 'large';

				if ( $request['doc_img_size'] ) {
					$img_size = $request['doc_img_size'];
				}

				$item_arr['document_as_html'] = $item->get_document_as_html($img_size);
				$item_arr['exposer_urls'] = \Tainacan\Exposers_Handler::get_exposer_urls(rest_url("{$this->namespace}/{$this->rest_base}/{$item->get_id()}/"));
				$item_arr = $this->add_metadata_to_item( $item, $item_arr );

				if ( $request->get_method() != 'GET') {
					$item_arr['thumbnail'] = $item->get_thumbnail();
					$item_arr['thumbnail_alt'] = get_post_meta( $item->get__thumbnail_id(), '_wp_attachment_image_alt', true );
					$item_arr['thumbnail_id'] = $item->get__thumbnail_id();
					$item_arr['nonces'] = array(
						'update-post_' . $item->get_id() => wp_create_nonce('update-post_' . $item->get_id())
					);
				}

			} else {

				$attributes_to_filter = $request['fetch_only'];
				$meta_to_filter = $request['fetch_only_meta'];

				# Always returns id and collection id
				if(is_array($attributes_to_filter)) {
					$attributes_to_filter[] = 'id';
					$attributes_to_filter[] = 'collection_id';
				} elseif (!strstr($attributes_to_filter, ',')){
					$attributes_to_filter = array($attributes_to_filter, 'id', 'collection_id');
				} else {
					$attributes_to_filter .= ',id,collection_id';
				}

				if ( $request['context'] === 'edit' ) {
					add_filter( 'tainacan_add_related_item', function( $related_item ) {
						return array_merge($related_item, $this->get_context_edit($related_item['id']));
					}, 10, 2 );
				}

				$item_arr = $this->filter_object_by_attributes($item, $attributes_to_filter);

				$item_arr = array_merge($extra_metadata_values, $item_arr);

				if(is_array($attributes_to_filter) && array_key_exists('meta', $attributes_to_filter)){

					$args = array('post__in' => $attributes_to_filter['meta']);

					$item_arr = $this->add_metadata_to_item($item, $item_arr, $args);
				} elseif ($meta_to_filter){
					$meta_to_filter = explode(',', $meta_to_filter);

					$args = array('post__in' => $meta_to_filter);

					$item_arr = $this->add_metadata_to_item($item, $item_arr, $args);
				}

				if ( $request['context'] === 'edit' ) {
					$item_arr = array_merge($item_arr, $this->get_context_edit($item));
				}
				if( isset($item_arr['thumbnail']) ) {
					$item_arr['thumbnail_alt'] = get_post_meta( $item->get__thumbnail_id(), '_wp_attachment_image_alt', true );
					$item_arr['thumbnail_id'] = $item->get__thumbnail_id();
					$item_arr['document_mimetype'] = $item->get_document_mimetype(); // In case the thumbnail is requested, we need the document mime type to generate proper placeholders
				}

				$item_arr['url'] = get_permalink( $item_arr['id'] );
				$item_arr['exposer_urls'] = \Tainacan\Exposers_Handler::get_exposer_urls(get_rest_url(null, "{$this->namespace}/{$this->rest_base}/{$item->get_id()}/"));

			}

			$item_arr = apply_filters('tainacan-api-items-prepare-for-response', $item_arr, $item, $request);

			return $item_arr;
		}

		return $item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$item_id = $request['item_id'];

		$item = $this->items_repository->fetch($item_id);

		if (! $item instanceof Entities\Item) {
			return new \WP_REST_Response([
				'error_message' => __('An item with this ID was not found', 'tainacan' ),
				'item_id' => $item_id
			], 400);
		}

		$response = $this->prepare_item_for_response($item, $request);

		return new \WP_REST_Response(apply_filters('tainacan-rest-response', $response, $request), 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item_attachments( $request ) {
		$item_id = $request['item_id'];

		$item = $this->items_repository->fetch($item_id);

		if (! $item instanceof Entities\Item) {
			return new \WP_REST_Response([
				'error_message' => __('An item with this ID was not found', 'tainacan' ),
				'item_id' => $item_id
			], 400);
		}

		$args = $this->prepare_filters($request);

		// fixed args
		$args['post_parent'] = $item_id;
		$args['post_type'] = 'attachment';
		$args['post_status'] = 'any';

		unset($args['perm']);

		$posts_query  = new \WP_Query();
		$query_result = $posts_query->query( $args );

		$attachments = array();
		global $post; // makes the apply_filters('the_content') below work as expected
		foreach ( $query_result as $post ) {

			$sizes = get_intermediate_image_sizes();
			$thumbs = [];

			foreach ( $sizes as $size ) {
				$thumbs[$size] = wp_get_attachment_image_src( $post->ID, $size );
			}

			$attachments[] = [
				'id' => $post->ID,
				'title' => get_the_title( $post ),
				'description' => apply_filters( 'the_content', $post->post_content ),
				'mime_type' => $post->post_mime_type,
				'date' => $post->post_date,
				'date_gmt' => $post->post_date_gmt,
				'author' => $post->post_author,
				'url' => wp_get_attachment_url( $post->ID ),
				'media_type' => wp_attachment_is_image( $post->ID ) ? 'image' : 'file',
				'alt_text' => get_post_meta( $post->ID, '_wp_attachment_image_alt', true ),
				'thumbnails' => $thumbs
			];
		}

		$total_items  = $posts_query->found_posts;
		$max_pages = ceil($total_items / (int) $posts_query->query_vars['posts_per_page']);

		$rest_response = new \WP_REST_Response(apply_filters('tainacan-rest-response', $attachments, $request), 200);

		$rest_response->header('X-WP-Total', (int) $total_items);
		$rest_response->header('X-WP-TotalPages', (int) $max_pages);
		$rest_response->header('X-WP-ItemsPerPage', (int) $posts_query->query_vars['posts_per_page']);

		return $rest_response;
	}

	/**
	 * @param array $args — array of query arguments.
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function prepare_filters_arguments ( $args, $collection_id = false, $ignore_filter_arguments = [] ) {
		$filters_arguments = array();
		
		if(!empty($collection_id)) {
			$collection = $this->collections_repository->fetch($collection_id);
			$order = $collection->get_filters_order();
			$order = ( is_array( $order ) ) ? $order : unserialize( $order );
		}
		
		
		$meta_query = isset($args['meta_query']) ? $args['meta_query'] : [];
		if(isset($meta_query['value'])) $meta_query =  [$meta_query];
		$tax_query = isset($args['tax_query']) ? $args['tax_query'] : [];

		foreach($tax_query as $tax) {

			if ( !isset($tax['taxonomy']) || !isset($tax['terms']) )
				continue;

			$taxonomy = $tax['taxonomy'];
			$taxonomy_id = $this->taxonomies_repository->get_id_by_db_identifier($taxonomy);
			$terms_id = is_array($tax['terms']) ? $tax['terms']: [$tax['terms']];
			$terms_name = array_map(function($term_id) { 
				$t = is_numeric($term_id) ? get_term($term_id) : get_term_by('slug', $term_id);
				return $t != false ? $t->name : '--';
			}, $terms_id);

			$metas = $this->metadatum_repository->fetch(array(
				'meta_query' => [
					[
						'key'     => 'collection_id',
						'value'   => empty($collection_id) ? 'default' : $collection_id,
						'compare' => '='
					],
					[
						'key'     => '_option_taxonomy_id',
						'value'   => $taxonomy_id,
						'compate' => '='
					]
				]
			), 'OBJECT' );

			if( !empty($metas) ) {
				$meta_query[] = array(
					'key' => $metas[0]->get_id(),
					'value' => $terms_id,
					'label' => $terms_name,
				);
			}
		}

		foreach($meta_query as $meta) {
			if ( !isset($meta['key']) || !isset($meta['value']) || ( in_array($meta['key'], $ignore_filter_arguments)  ))
				continue;

			$meta_id = $meta['key'];
			$meta_value = is_array($meta['value']) ? $meta['value'] : [$meta['value']];
			$meta_label = isset($meta['label']) ? $meta['label'] : $meta_value;
			$meta_type = 'CHAR';
			$filter_type_component = false;
			$arg_type = 'meta';
			$f = false;
			$m = false;

			if ($meta_id === 'collection_id') {

				$arg_type = 'collection';
				$meta_label = array_map(function($collection_id) {
					$collection = $this->collections_repository->fetch($collection_id);
					return $collection->get_name();
				}, $meta_value);

			} else {

				$filter = $this->filters_repository->fetch([
					'meta_query' => array(
						[
							'key'     => 'metadatum_id',
							'value'   => $meta_id,
							'compare' => '='
						]
					)
					], 'OBJECT'
				);

				$filter_id = empty($filter) ? false : $filter[0]->get_id();
				$filter_order_index = $order ? array_search( $filter_id, array_column( $order, 'id' ) ) :  false;
				if ( !empty($filter_order_index) && $order[$filter_order_index]['enabled'] == true) {
					$f = $filter[0]->_toArray();
					$filter_type_component = $filter[0]->get_filter_type_object()->get_component();
					$m = $f['metadatum'];
				} else {
					$metadatum = $this->metadatum_repository->fetch($meta_id, 'OBJECT');
					if(!empty($metadatum)) {
						$m = $metadatum->_toArray();
						$meta_object = $metadatum->get_metadata_type_object();
						if (is_object($meta_object)) {
							$m['metadata_type_object'] = $meta_object->_toArray();
						}
					}
				}
	
				if ($m !== false) {
					switch ($m['metadata_type_object']['primitive_type']) {
						case 'date':
							$meta_label = array_map(function($date) {
								$date_format = get_option( 'date_format' ) != false ? get_option( 'date_format' ) : 'Y-m-d';
								return empty($date) == false ? mysql2date($date_format, $date) : "";
							}, $meta_label);
							$meta_type = 'DATE';
							break;
						case 'item':
							$meta_label = array_map(function($item_id) {
								$_post = get_post($item_id);
								if ( ! $_post instanceof \WP_Post) {
									return;
								}
								return $_post->post_title;
							}, $meta_label);
							break;
						case 'control':
							$control_metadatum = $m['metadata_type_object']['options']['control_metadatum'];
							$metadata_type_object = new \Tainacan\Metadata_Types\Control();
							$meta_label = array_map(function($control_value) use ($metadata_type_object, $control_metadatum) {
								return $metadata_type_object->get_control_metadatum_value($control_value, $control_metadatum, 'string' );
							}, $meta_label);
							break;
						case 'user':
							$meta_label = array_map(function($user_id) {
								$name = get_the_author_meta( 'display_name', $user_id );
								return apply_filters("tainacan-item-get-author-name", $name);
							}, $meta_label);
							break;
						case 'int':
						case 'float':
						case 'numeric':
							$meta_type = 'NUMERIC';
							break;
					}
				}
			}

			$filter_name = is_string($filter_type_component) 
				? "tainacan-api-items-$filter_type_component-filter-arguments"
				: 'tainacan-api-items-filter-arguments';

			$filters_arguments[] = apply_filters($filter_name, array(
				'filter' => $f,
				'metadatum' => $m,
				'arg_type' => $arg_type,
				'value' => $meta_value,
				'label' => $meta_label,
				'compare' => isset($meta['compare']) ? $meta['compare'] : '=',
				'type' => $meta_type,
			));
		}

		if (isset($args['post__in'])) {
			$filters_arguments[] = array(
				'filter' => false,
				'metadatum' => false,
				'arg_type' => 'postin',
				'value' => $args['post__in'],
				'label' => count($args['post__in']),
				'compare' => 'IN'
			);
		}

		return $filters_arguments;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {

		global $TAINACAN_API_MAX_ITEMS_PER_PAGE;

		// Free php session early so simultaneous requests dont get queued
		session_write_close();
		$args = $this->prepare_filters($request);

		/**
		 * allow plugins to hijack the process.
		 *
		 * If it returns a \WP_REST_Response, the method will return it and ignore the rest of the script
		 */
		$alternate_response = apply_filters('tainacan-api-get-items-alternate', false, $request);
		if ( $alternate_response instanceof \WP_REST_Response ) {
			return $alternate_response;
		}

		$collection_id = [];
		if($request['collection_id']) {
			$collection_id = $request['collection_id'];
		}
		$metaqueries = isset($request['metaquery']) ? $request['metaquery'] : [];
		$ignore_filter_arguments = array_map(
			function($metaquery) { return $metaquery['key']; },
			array_filter(
				$metaqueries,
				function($metaquery) { return isset($metaquery['key']) && isset($metaquery['secondary']) && $metaquery['secondary'] == 'true'; }
			)
		);
		$filters_args = $this->prepare_filters_arguments($args, $collection_id, $ignore_filter_arguments);
		if(isset($args['meta_query']) && !empty($args['meta_query']) && is_array($filters_args) && !empty($filters_args)) {
			foreach($filters_args as $filters_arg) {
				if($filters_arg['filter'] !== false) {
					for($idx = 0; $idx < count($args['meta_query']); $idx++) {
						if( isset($args['meta_query'][$idx]['key']) && $args['meta_query'][$idx]['key'] == $filters_arg['metadatum']['metadatum_id']) {
							$args['meta_query'][$idx]['type'] = $filters_arg['type'];
						}
					}
				}
			}
		}

		$max_items_per_page = $TAINACAN_API_MAX_ITEMS_PER_PAGE;
		if ( $max_items_per_page > -1 ) {
			if ( isset($args['posts_per_page']) && (int) $args['posts_per_page'] > $max_items_per_page ) {
				$args['posts_per_page'] = $max_items_per_page;
			}
		}

		$response = [];
		$response['items'] = [];
		$response['template'] = '';

		$query_start = microtime(true);

		if(isset($request['geoquery'])) {
			$args['geoquery'] = $request['geoquery'];
		}

		$items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');

		// Filter right after the ->fetch() method. Elastic Search integration relies on this on its 'last_aggregations' hook
		$response['filters'] = apply_filters('tainacan-api-items-filters-response', [], $request);
		$response['filters_arguments'] = apply_filters('tainacan-api-items-filters-arguments-response', $filters_args, $args);

		$query_end = microtime(true);

		$return_template = false;

		if ( isset($request['view_mode']) ) {

			// TODO: Check if requested view mode is really enabled for current collection
			$view_mode = \Tainacan\Theme_Helper::get_instance();
			$view_mode = $view_mode->get_view_mode($request['view_mode']);

			if ($view_mode && $view_mode['type'] == 'template' && isset($view_mode['template']) && file_exists($view_mode['template'])) {
				$return_template = true;
			}

		}

		if ( $return_template ) {

			ob_start();

			global $wp_query, $view_mode_displayed_metadata;
			$wp_query = $items;

			$meta = [];
			$view_mode_displayed_metadata = [];

			if(is_string($request['fetch_only'])){
				$view_mode_displayed_metadata = explode(',', $request['fetch_only']);
			}

			if($request['fetch_only_meta']){
				$meta = explode(',', $request['fetch_only_meta']);
			} elseif(is_array($request['fetch_only'])){
				$view_mode_displayed_metadata = $request['fetch_only'];
				$meta = array_key_exists( 'meta', $request['fetch_only'] ) ? $request['fetch_only']['meta'] : array();
			}

			$view_mode_displayed_metadata['meta'] = array_map( function ( $el ) {
				return (int) $el;
			}, $meta);

			include $view_mode['template'];

			$response['template'] = ob_get_clean();

		} else {

			if ($items->have_posts()) {
				while ( $items->have_posts() ) {
					$items->the_post();

					$item = new Entities\Item($items->post);

					$prepared_item = $this->prepare_item_for_response($item, $request);

					array_push($response['items'], $prepared_item);
				}

				wp_reset_postdata();
			}

		}

		$response = apply_filters('tainacan-api-items-response', $response, $request);

		$total_items  = $items->found_posts;
		$max_pages = ceil($total_items / (int) $items->query_vars['posts_per_page']);

		$rest_response = new \WP_REST_Response($response, 200);

		$rest_response->header('X-WP-Total', (int) $total_items);
		$rest_response->header('X-WP-TotalPages', (int) $max_pages);
		$rest_response->header('X-WP-ItemsPerPage', (int) $items->query_vars['posts_per_page']);
		$rest_response->header('X-Tainacan-Query-Time', $query_end - $query_start);
		$rest_response->header('X-Tainacan-Elapsed-Time', microtime(true) - $query_start);

		return $rest_response;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$item = $this->items_repository->fetch($request['item_id']);

		if(($item instanceof Entities\Item)) {
			return $item->can_read();
		}

		return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_item_attachments_permissions_check( $request ) {
		$item = $this->items_repository->fetch($request['item_id']);

		if(($item instanceof Entities\Item)) {
			return $item->can_read();
		}

		return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if ( isset($request['taxquery']) && !$this->get_items_permissions_check_for_taxonomy($request['taxquery']) ) {
			return false;
		}

		if( $collection instanceof Entities\Collection ) {
			if(!$collection->can_read()) {
				return false;
			}
			return true;
		} else {
			return true;
		}
	}

	private function get_items_permissions_check_for_taxonomy($taxonomies) {

		foreach ($taxonomies as $tax) {
			if ( isset($tax['taxonomy']) ) {
				$taxonomy = \tainacan_taxonomies()->fetch_by_db_identifier( $tax['taxonomy'] );

				if ( $taxonomy instanceof Entities\Taxonomy ) {
					if (!$taxonomy->can_read())
						return false;
				}
			}
		}
		return true;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return object|Entities\Item|\WP_Error
	 * @throws \Exception
	 */
	public function prepare_item_for_database( $request ) {

		$item = new Entities\Item();

		$item_as_array = $request[0];

		foreach ($item_as_array as $key => $value){
			$item->set($key, $value);
		}

		$collection = $this->collections_repository->fetch($request[1]);

		$item->set_collection($collection);

		return $item;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function create_item( $request ) {
		$collection_id = $request['collection_id'];
		$item          = json_decode($request->get_body(), true);

		if(empty($item)){
			return new \WP_REST_Response([
				'error_message' => __('Body cannot be empty.', 'tainacan'),
				'item'          => $item
			], 400);
		}

		try {
			$item_obj = $this->prepare_item_for_database( [ $item, $collection_id ] );
		} catch (\Exception $exception){
			return new \WP_REST_Response($exception->getMessage(), 400);
		}

		if($this->item->validate()) {
			$item = $this->items_repository->insert( $item_obj );

			return new \WP_REST_Response($this->prepare_item_for_response($item, $request), 201 );
		}


		return new \WP_REST_Response([
			'error_message' => __('One or more values are invalid.', 'tainacan'),
			'errors'        => $this->item->get_errors(),
			'item'          => $this->prepare_item_for_response($this->item, $request)
		], 400);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function create_item_permissions_check( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);

		if ($collection instanceof Entities\Collection) {
			return current_user_can($collection->get_items_capabilities()->edit_posts);
		}

		return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {
		$item_id     = $request['item_id'];
		$permanently = $request['permanently'];

		$item = $this->items_repository->fetch($request['item_id']);

		if (! $item instanceof Entities\Item) {
			return new \WP_REST_Response([
				'error_message' => __('An item with this ID was not found', 'tainacan' ),
				'item_id' => $item_id
			], 400);
		}

		if($permanently == true) {
			$item = $this->items_repository->delete($item);
		} else {
			$item = $this->items_repository->trash($item);
		}

		$item_deleted = array(
			'id' => $item_id
		);

		return new \WP_REST_Response($item_deleted, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function delete_item_permissions_check( $request ) {
		$item = $this->items_repository->fetch($request['item_id']);

		if ($item instanceof Entities\Item) {
			return $item->can_delete();
		}

		return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
		$item_id = $request['item_id'];
		$body = json_decode($request->get_body(), true);

		if (!empty($body)) {
			$attributes = [];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$item = $this->items_repository->fetch($item_id);

			if($item){
				$prepared_item = $this->prepare_item_for_updating($item, $attributes);

				if($prepared_item->validate()){
					$updated_item = $this->items_repository->update($prepared_item);

					do_action('tainacan-api-item-updated', $updated_item, $attributes);

					return new \WP_REST_Response($this->prepare_item_for_response($updated_item, $request), 200);
				}

				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $prepared_item->get_errors(),
					'item'          => $this->prepare_item_for_response($prepared_item, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('An item with this ID was not found', 'tainacan' ),
				'item_id'       => $item_id
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
	 * @throws \Exception
	 */
	public function update_item_permissions_check( $request ) {
		$item = $this->items_repository->fetch($request['item_id']);

		if ($item instanceof Entities\Item) {
			return $item->can_edit();
		}

		return false;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function duplicate_item( $request ) {
		$item_id = $request['item_id'];

		$item = $this->items_repository->fetch($item_id);

		$defaults = [
			'copies' => 1,
			'status' => 'draft'
		];

		$data_body = $request->get_body() ?? '';
		$body = json_decode($data_body, true);

		if (!is_array($body)) {
			$body = [];
		}

		$args = array_merge($defaults, $body);

		if ($item) {

			$response = [
				'items' => []
			];

			for ($i=1; $i<=$args['copies']; $i++) {

				$new_item = new Entities\Item();
				$items_repo = Repositories\Items::get_instance();

				$new_item->set_status( 'draft' );

				$new_item->set_title( $item->get_title() );
				$new_item->set_description( $item->get_description() );
				$new_item->set_collection_id( $item->get_collection_id() );

				if ( $new_item->validate() ) {

					$new_item = $items_repo->insert($new_item);

					$metadata = $item->get_metadata();

					$errors = [];

					foreach ($metadata as $item_metadatum) {
						if ( $item_metadatum->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Compound' ) {
							$multiple_values = $item_metadatum->is_multiple() ? $item_metadatum->get_value() : [$item_metadatum->get_value()] ;
							foreach ($multiple_values as $value) {
								$parent_meta_id = null;
								foreach ( $value as $meta_id => $meta ) {
									if ( $meta instanceof Entities\Item_Metadata_Entity) {
										$item_metadata = new Entities\Item_Metadata_Entity($new_item, $meta->get_metadatum(), null, $parent_meta_id);
										$item_metadata->set_value( $meta->get_value() );
										if ( $item_metadata->validate() ) {
											$item_metadata = Repositories\Item_Metadata::get_instance()->insert( $item_metadata );
											$parent_meta_id = $item_metadata->get_parent_meta_id();
										} else {
											$errors[] = $item_metadata->get_errors();
										}
									}
								}
							}
						} else {
							$new_item_metadatum = new Entities\Item_Metadata_Entity( $new_item, $item_metadatum->get_metadatum() );
							$new_item_metadatum->set_value( $item_metadatum->get_value() );
							if ( $new_item_metadatum->validate() ) {
								Repositories\Item_Metadata::get_instance()->insert( $new_item_metadatum );
							} else {
								$errors[] = $new_item_metadatum->get_errors();
							}
						}
					}

					if ($args['status'] != 'draft') {

						$new_item->set_status( $args['status'] );

						if ( $new_item->validate() ) {
							$new_item = $items_repo->insert($new_item);
						} else {
							$new_item->set_status( 'draft' );
						}

					}

					$response['items'][] = $this->prepare_item_for_response($new_item, $request);

					do_action('tainacan-api-item-duplicated', $item, $new_item);

				} else {
					return new \WP_REST_Response([
						'error_message' => __('Error duplicating item', 'tainacan'),
						'errors'        => $new_item->get_errors(),
						'item'          => $this->prepare_item_for_response($item, $request)
					], 400);
				}

			}

			return new \WP_REST_Response($response, 201);

		}

		return new \WP_REST_Response([
			'error_message' => __('An item with this ID was not found', 'tainacan' ),
			'item_id'       => $item_id
		], 400);

	}

	private function submission_item_metadata ( \Tainacan\Entities\Item_Metadata_Entity &$item_metadata, $request) {
		$collection_id = $item_metadata->get_item()->get_collection_id();
		$item = $item_metadata->get_item();
		$collection = $this->collections_repository->fetch($collection_id);
		if ( $item_metadata->validate() ) {
			if($item->can_edit() || $collection->get_submission_anonymous_user() == 'yes') {
				return $this->item_metadata->update( $item_metadata );
			}
			elseif($item_metadata->get_metadatum()->get_accept_suggestion()) {
				return $this->item_metadata->suggest( $item_metadata );
			}
			else {
				$this->submission_rollback_new_terms();
				return new \WP_REST_Response( [
					'error_message' => __( 'The metadatum does not accept suggestions', 'tainacan' ),
				], 400 );
			}
		} else {
			$this->submission_rollback_new_terms();
			return new \WP_REST_Response( [
				'error_message' => __( 'Please verify, invalid value(s)', 'tainacan' ),
				'errors'        => $item_metadata->get_errors(),
				'item_metadata' => $this->prepare_item_for_response($item_metadata->get_item(), $request),
			], 400 );
		}
	}

	private $new_terms_ids = [];
	private function submission_process_terms ($value, $taxonomy) {
		if (is_numeric($value)) return $value;
		$split_value = explode(">>", $value);
		if(count($split_value) == 1 ) {
			$exist = $this->terms_repository->term_exists($split_value[0], $taxonomy, null, true);
			if ($exist)
				return $split_value[0];
			$new_term  = new Entities\Term();
			$new_term->set_taxonomy( $taxonomy->get_db_identifier() );
			$new_term->set('name', $split_value[0]);
			if ( $new_term->validate() ) {
				$new_term = $this->terms_repository->insert( $new_term );
				$this->new_terms_ids[] = ['term_id' => $new_term->get_term_id(), 'taxonomy' => $new_term->get_taxonomy()];
			}
			return $new_term;
		} else if (count($split_value) == 2 ) {
			$new_term  = new Entities\Term();
			$new_term->set_taxonomy( $taxonomy->get_db_identifier() );
			$new_term->set('name', $split_value[1]);
			$new_term->set('parent', $split_value[0]);
			if ( $new_term->validate() ) {
				$new_term = $this->terms_repository->insert( $new_term );
				$this->new_terms_ids[] = ['term_id' => $new_term->get_term_id(), 'taxonomy' => $new_term->get_taxonomy()];
			}
			return $new_term;
		}
		return count($split_value) > 1 ? $value : htmlspecialchars($value);
	}

	private function submission_rollback_new_terms () {
		foreach($this->new_terms_ids as $term) {
			$remove_term = new Entities\Term($term['term_id'], $term['taxonomy']);
			$this->terms_repository->delete( $remove_term, true );
		}
	}

	public function submission_item ($request) {
		$collection_id = $request['collection_id'];
		$item          = json_decode($request->get_body(), true);
		$metadata = $item['metadata'];
		foreach ($item as $key => $value) {
			$item[$key] = ( is_string($value) && !is_numeric($value) ? htmlspecialchars($value) : $value );
		}

		$response_recaptcha = $this->submission_item_check_recaptcha($request);
		if ($response_recaptcha !== true) {
			return $response_recaptcha;
		}

		if(empty($item) || empty($metadata)) {
			return new \WP_REST_Response([
				'error_message' => __('Body cannot be empty.', 'tainacan'),
				'item'          => $item
			], 400);
		}

		try {
			$item['status'] = 'auto-draft';
			$item = $this->prepare_item_for_database( [ $item, $collection_id ] );
			$data = apply_filters('tainacan-submission-item-data', $item, $metadata);
			if ( \is_null($data) === false ) {
				$item = $data;
			}

			if ( $item->validate() ) {
				$item = $this->items_repository->insert( $item );

				foreach ( $metadata as $m ) {
					if ( !isset($m['value']) || $m['value'] == null ) continue;
					$value = $m['value'];
					$metadatum_id = $m['metadatum_id'];
					$metadatum = $this->metadatum_repository->fetch( $metadatum_id );
					$item_metadata = new Entities\Item_Metadata_Entity($item, $metadatum);

					if($metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Compound') {
						if ($item_metadata->is_multiple()) {
							foreach($value as $row) {
								$parent_meta_id = null;
								foreach($row as $child) {
									$child_value = $child['value'];
									if (is_array($child_value) == true) {
										$child_value = implode(' ', $child_value);
									}
									if (is_numeric($value) != true) {
										$child_value = htmlspecialchars($child_value);
									}
									$metadatum_child = $this->metadatum_repository->fetch( $child['metadatum_id'] );
									$item_metadata_child = new Entities\Item_Metadata_Entity($item, $metadatum_child, null, $parent_meta_id);
									$item_metadata_child->set_value($child_value);
									$item_metadata_child = $this->submission_item_metadata($item_metadata_child, $request);
									if ($item_metadata_child instanceof \WP_REST_Response) {
										return $item_metadata_child;
									}
									$parent_meta_id = $item_metadata_child->get_parent_meta_id();
								}
							}
						} else {
							$parent_meta_id = null;
							if( is_array($value) && count($value) == 1 )
								$value = $value[0];
							foreach($value as $child) {
								$child_value = $child['value'];
								if (is_array($child_value) == true) {
									$child_value = implode(' ', $child_value);
								}
								if (is_numeric($value) != true) {
									$child_value = htmlspecialchars($child_value);
								}
								$metadatum_child = $this->metadatum_repository->fetch( $child['metadatum_id'] );
								$item_metadata_child = new Entities\Item_Metadata_Entity($item, $metadatum_child, null, $parent_meta_id);
								$item_metadata_child->set_value($child_value);
								$item_metadata_child = $this->submission_item_metadata($item_metadata_child, $request);
								if ($item_metadata_child instanceof \WP_REST_Response) {
									return $item_metadata_child;
								}
								$parent_meta_id = $item_metadata_child->get_parent_meta_id();
							}
						}
					} else if ($metadatum->get_metadata_type_object()->get_primitive_type() == 'term') {
						$taxonomy_id  = $metadatum->get_metadata_type_object()->get_option( 'taxonomy_id' );
						$taxonomy = new Entities\Taxonomy( $taxonomy_id );
						if (is_array($value) == true) {
							$value = array_map( function($v) use ($taxonomy) {
								return $this->submission_process_terms($v, $taxonomy);
							}, $value);
						} else {
							$value = $this->submission_process_terms($value, $taxonomy);
						}
						if ($item_metadata->is_multiple()) {
							$item_metadata->set_value( is_array($value) ? $value : [$value] );
						} else {
							$item_metadata->set_value( is_array($value) ? implode(' ', $value) : $value);
						}
						$item_metadata = $this->submission_item_metadata($item_metadata, $request);
						if ($item_metadata instanceof \WP_REST_Response) {
							return $item_metadata;
						}
					} else {
						if (is_array($value) == true) {
							$value = array_map( function($v) { return is_numeric($v) ? $v : htmlspecialchars($v); }, $value);
						} else if (is_numeric($value) != true) {
							$value = htmlspecialchars($value);
						}
						if ($item_metadata->is_multiple()) {
							$item_metadata->set_value( is_array($value) ? $value : [$value] );
						} else {
							$item_metadata->set_value( is_array($value) ? implode(' ', $value) : $value);
						}
						$item_metadata = $this->submission_item_metadata($item_metadata, $request);
						if ($item_metadata instanceof \WP_REST_Response) {
							return $item_metadata;
						}
					}
				}

				if ($item->validate()) {
					$item = $this->items_repository->insert( $item );
					$fake_id = md5(uniqid(mt_rand(), true));
					$id = $item->get_id();
					if (set_transient('tnc_transient_submission_' . $fake_id, $id, 300) == true) {
						set_transient('tnc_transient_submission_' . $fake_id . '_new_terms_ids', $this->new_terms_ids, 300);
						$response_item = $this->prepare_item_for_response($item, $request);
						$response_item['id'] = $fake_id;
						return new \WP_REST_Response($response_item, 201 );
					} else {
						$this->submission_rollback_new_terms();
						return new \WP_REST_Response([
							'error_message' => __('unable to create submission ID.', 'tainacan'),
						], 400);
					}

				} else {
					$this->submission_rollback_new_terms();
					return new \WP_REST_Response([
						'error_message' => __('One or more values are invalid.', 'tainacan'),
						'errors'        => $item->get_errors(),
						'item'          => $this->prepare_item_for_response($this->item, $request)
					], 400);
				}
			} else {
				$this->submission_rollback_new_terms();
				return new \WP_REST_Response([
					'error_message' => __('One or more values are invalid.', 'tainacan'),
					'errors'        => $item->get_errors(),
					'item'          => $this->prepare_item_for_response($this->item, $request)
				], 400);
			}
		} catch (\Exception $exception){
			$this->submission_rollback_new_terms();
			return new \WP_REST_Response($exception->getMessage(), 400);
		}
	}

	public function submission_item_finish ( $request ) {
		define( 'WP_ADMIN', true );
		$submission_id = $request['submission_id'];
		$collection_id = $request['collection_id'];
		$item_id = get_transient('tnc_transient_submission_' . $submission_id);
		if($item_id === false) {
			return new \WP_REST_Response([
				'error_message' => __('submission ID does not exist.', 'tainacan'),
			], 400);
		}
		$this->new_terms_ids = get_transient('tnc_transient_submission_' . $submission_id . '_new_terms_ids');

		$item = $this->items_repository->fetch($item_id);
		$collection = $this->collections_repository->fetch($collection_id);
		$default_status = $collection->get_submission_default_status();
		$item->set_status($default_status);

		$TainacanMedia = \Tainacan\Media::get_instance();
		$files = $request->get_file_params();

		$insert_attachments = [];
		$entities_erros = [];
		if( isset($files['document']) && !is_array($files['document']['tmp_name']) == 1 && $files['document']['size'] > 0 ) {
			$tmp_file_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . \hexdec(\uniqid()) . '_' . $files['document']['name'];
			move_uploaded_file($files['document']['tmp_name'], $tmp_file_name);
			$document_id = $TainacanMedia->insert_attachment_from_file($tmp_file_name, $item_id);
			if($document_id === false) {
				$entities_erros[] = ["document" => __('Error while creating document', 'tainacan')];
				wp_delete_attachment($document_id, true);
			} else {
				$item->set_document_type('attachment');
				$item->set_document($document_id);
				$insert_attachments[] = $document_id;
			}
			unlink($tmp_file_name);
		}

		if( isset($files['thumbnail']) && !is_array($files['thumbnail']['tmp_name']) == 1 && $files['thumbnail']['size'] > 0 ) {
			$tmp_file_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . \hexdec(\uniqid()) . '_' . $files['thumbnail']['name'];
			move_uploaded_file($files['thumbnail']['tmp_name'], $tmp_file_name);
			$thumbnail_id = $TainacanMedia->insert_attachment_from_file($tmp_file_name);
			if($thumbnail_id === false) {
				$entities_erros[] = ["thumbnail" => __('Error while creating thumbnail', 'tainacan')];
				wp_delete_attachment($thumbnail_id, true);
			} else {
				$item->set__thumbnail_id($thumbnail_id);
				$insert_attachments[] = $thumbnail_id;
			}
			unlink($tmp_file_name);
		} else {
			$thumbnail_id = $this->items_repository->get_thumbnail_id_from_document($item);
			if (!is_null($thumbnail_id)) {
				set_post_thumbnail( $item_id, (int) $thumbnail_id );
				$insert_attachments[] = $thumbnail_id;
			}
		}

		if( isset($files['attachments']) ) {
			$attachments = is_array($files['attachments']['tmp_name']) ? $files['attachments']['tmp_name'] : [$files['attachments']['tmp_name']];
			$attachments_name = is_array($files['attachments']['name']) ? $files['attachments']['name'] : [$files['attachments']['name']];
			for ($i = 0; $i < count($attachments); $i++) {
				$tmp_file_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . \hexdec(\uniqid()) . '_' . $attachments_name[$i];
				move_uploaded_file($attachments[$i], $tmp_file_name);
				$attachment_id = $TainacanMedia->insert_attachment_from_file($tmp_file_name, $item_id);
				unlink($tmp_file_name);
				if($attachment_id === false) {
					$entities_erros[] = ['attachments' => __('Error while creating attachment ', 'tainacan') . "($attachments_name[$i])" ];
					break;
				}
				$insert_attachments[] = $attachment_id;
			}
		}

		if( !empty($entities_erros) ) {
			foreach($insert_attachments as $remove_id) {
				wp_delete_attachment($remove_id, true);
			}
		}

		if (empty($entities_erros) & $item->validate()) {
			$item = $this->items_repository->insert( $item );
			delete_transient('tnc_transient_submission_' . $submission_id);
			do_action('tainacan-submission-item-finish', $item, $request);
			return new \WP_REST_Response($this->prepare_item_for_response($item, $request), 201 );
		} else {
			$this->submission_rollback_new_terms();
			return new \WP_REST_Response([
				'error_message' => __('One or more values are invalid.', 'tainacan'),
				'errors'        => array_merge($item->get_errors(), $entities_erros),
				'item'          => $this->prepare_item_for_response($this->item, $request)
			], 400);
		}
	}

	public function submission_item_permissions_check ( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);
		if ($collection instanceof Entities\Collection && $collection->get_allows_submission() == 'yes') {
			if ($collection->get_submission_anonymous_user() == 'yes') {
				return true;
			}
			return current_user_can($collection->get_items_capabilities()->edit_posts);
		}
		return false;
	}

	private function submission_item_check_recaptcha( $request ) {
		$collection = $this->collections_repository->fetch($request['collection_id']);
		$body = json_decode($request->get_body(), true);
		if ($collection instanceof Entities\Collection && $collection->get_submission_use_recaptcha() == 'yes') {
			$captcha_data = $body['g-recaptcha-response'];
			if (!$captcha_data) {
				return new \WP_REST_Response([
					'error_message' => __('reCAPTCHA data it is need.', 'tainacan'),
					'errors'        => []
				], 400);
			}
			$secret_key = get_option("tnc_option_recaptch_secret_key");
			$api_url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=".$captcha_data."&remoteip=".$_SERVER['REMOTE_ADDR'];

			$response = wp_remote_get( $api_url );
			$body = wp_remote_retrieve_body( $response );
			$response = json_decode($body);

			if ($response->success) {
				return true;
			} else {
				return new \WP_REST_Response([
					'error_message' => __('reCAPTCHA not valid.', 'tainacan'),
					'errors'        => []
				], 400);
			}
		}
		return true;
	}

	/**
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args_for_item_schema( $method = null ){
		$endpoint_args = [
			'item_id' => [
				'description' => __( 'Item ID', 'tainacan' ),
				'required' => true,
			]
		];

		switch ( $method ) {
			case \WP_REST_Server::READABLE:
				$endpoint_args['context'] = array(
					'type'    	  => 'string',
					'default' 	  => 'view',
					'description' => __( 'Scope under which the request is made; determines fields present in response.', 'tainacan' ),
					'enum'    	  => array(
						'view',
						'edit'
					),
				);
				$endpoint_args = array_merge(
					$endpoint_args,
					parent::get_fetch_only_param()
				);
			break;
			case \WP_REST_Server::CREATABLE:
			case \WP_REST_Server::EDITABLE:
				$map = $this->items_repository->get_map();

				foreach ($map as $mapped => $value){
					$set_ = 'set_'. $mapped;
	
					// Show only args that has a method set
					if( !method_exists($this->item, "$set_") ){
						unset($map[$mapped]);
					}
				}
	
				$endpoint_args = array_merge(
					$endpoint_args,
					$map
				);
	
				if ( $method === \WP_REST_Server::CREATABLE )
					unset($endpoint_args['item_id']);
			break;
			case \WP_REST_Server::DELETABLE:
				$endpoint_args['permanently'] = array(
					'description' => __('To delete permanently, you can pass \'permanently\' as 1. By default this will only trash collection', 'tainacan'),
					'default'     => '0'
				);
			break;
		}

		return $endpoint_args;
	}

	/**
	 * @param null $object_name
	 *
	 * @return array|void
	 */
	public function get_wp_query_params() {
		$query_params['title'] = array(
			'description' => __('Limits the result set to items with a specific title'),
			'type'        => 'string',
		);

		$query_params = array_merge(
			$query_params,
			parent::get_wp_query_params(),
			parent::get_meta_queries_params()
		);

		return $query_params;
	}

	function process_request_filters($args) {
		return $this->prepare_filters($args);
	}

	function get_attachments_schema() {

		$properties = [
			'title' => [
				'description' => esc_html__('The attachment title', 'tainacan'),
				'type' => 'string'
			],
			'description' => [
				'description' => esc_html__('The attachment description', 'tainacan'),
				'type' => 'string'
			],
			'mime_type' => [
				'description' => esc_html__('The attachment MIME type', 'tainacan'),
				'type' => 'string'
			],
			'date' => [
				'description' => esc_html__('The attachment creation date', 'tainacan'),
				'type' => 'string'
			],
			'date_gmt' => [
				'description' => esc_html__('The attachment creation date in GMT', 'tainacan'),
				'type' => 'string'
			],
			'author' => [
				'description' => esc_html__('The ID of the user who uploaded the attachment', 'tainacan'),
				'type' => 'string'
			],
			'url' => [
				'description' => esc_html__('The URL to the attachment file', 'tainacan'),
				'type' => 'string'
			],
			'media_type' => [
				'description' => esc_html__('The attachment Media type', 'tainacan'),
				'type' => 'string',
				'enum' => [ 'image', 'file' ]
			],
			'alt_text' => [
				'description' => esc_html__('The attachment Alt text', 'tainacan'),
				'type' => 'string'
			],
			'thumbnails' => [
				'description' => esc_html__('The attachment thumbnails', 'tainacan'),
				'type' => 'array'
			],
		];

		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'attachments',
			'type' => 'array',
			'tags' => ['attachments', $this->rest_base],
			'items' => array(
				'type' => 'object',
				'properties' => array_merge(
					parent::get_base_properties_schema(),
					$properties
				)
			)
		];

		return $schema;
	}

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'item',
			'type' => 'object',
			'tags' => [ $this->rest_base ],
		];

		$main_schema = parent::get_repository_schema( $this->items_repository );
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
