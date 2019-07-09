<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Filters_Controller extends REST_Controller {
	private $collection;
	private $collection_repository;

	private $metadatum;
	private $metadatum_repository;

	private $filter_repository;

	/**
	 * REST_Filters_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'filters';
		parent::__construct();
		add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->collection = new Entities\Collection();
		$this->collection_repository = Repositories\Collections::get_instance();
		
		$this->metadatum_repository = Repositories\Metadata::get_instance();
		
		$this->filter_repository = Repositories\Filters::get_instance();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/metadatum/(?P<metadatum_id>[\d]+)/' . $this->rest_base, array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'create_item'),
				'permission_callback' => array($this, 'create_item_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE)
			),
		));
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base, array(
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
		));
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
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
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<filter_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'delete_item_permissions_check'),
				'args'                => array(
	            	'permanently' => array(
		                'description' => __('To delete permanently, you can pass \'permanently\' as true. By default this will only trash collection'),
			            'default'     => 'false'
		            ),
	            )
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
		));
	}


	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return object|void|\WP_Error
	 */
	public function prepare_item_for_database( $request ) {
		$body = json_decode($request->get_body(), true);

		$filter_obj = new Entities\Filter();

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
			$filter_obj->set($attribute, $value);
		}

		if(isset($request['collection_id']) && isset($request['metadatum_id'])) {
			$collection_id = $request['collection_id'];
			$metadatum_id      = $request['metadatum_id'];

			$filter_obj->set_collection_id( $collection_id );
			$filter_obj->set_metadatum_id( $metadatum_id );
		} elseif (isset($request['collection_id'])){
			$collection_id = $request['collection_id'];

			$filter_obj->set_collection_id( $collection_id );

			if(!isset($body['metadatum_id'])){
				throw new \InvalidArgumentException('You need provide a metadatum id');
			}

			$filter_obj->set_metadatum_id($body['metadatum_id']);
		} else {
			$filter_obj->set_collection_id( 'filter_in_repository' );

			if(!isset($body['metadatum_id'])){
				throw new \InvalidArgumentException('You need provide a metadatum id');
			}

			$filter_obj->set_metadatum_id($body['metadatum_id']);
		}

		$filter_obj->set_filter_type($filter_type);

		return $filter_obj;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {

		if(!empty($request->get_body())){
			$filter_obj = $this->prepare_item_for_database($request);

			if ($filter_obj->validate()){
				$filter_inserted = $this->filter_repository->insert($filter_obj);

				return new \WP_REST_Response($this->prepare_item_for_response($filter_inserted, $request), 200);
			}

			return new \WP_REST_Response([
				'error_message' => __('Please verify, invalid attribute(s).', 'tainacan'),
				'error'         => $filter_obj->get_errors()
			], 400);
		}

		return new \WP_REST_Response([
			'error_message' => __('The body could not be empty', 'tainacan'),
			'body'          => $request->get_body()
		], 400);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function create_item_permissions_check( $request ) {
		if(isset($request['collection_id']) && isset($request['metadatum_id'])) {
			$metadata   = $this->metadatum_repository->fetch( $request['metadatum_id'] );
			$collection = $this->collection_repository->fetch( $request['collection_id'] );

			if ( ( $metadata instanceof Entities\Metadatum ) && ( $collection instanceof Entities\Collection ) ) {
				return $this->filter_repository->can_edit( new Entities\Filter() ) && $metadata->can_edit() && $collection->can_edit();
			}

		} elseif (isset($request['collection_id'])){
			$collection = $this->collection_repository->fetch( $request['collection_id'] );

			if ( $collection instanceof Entities\Collection ) {
				return $collection->can_edit();
			}

		}

		return $this->filter_repository->can_edit(new Entities\Metadatum());
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {
		$permanently = $request['permanently'];
		
		$filter = $this->filter_repository->fetch($request['filter_id']);

		if (! $filter instanceof Entities\Filter) {
			return new \WP_REST_Response([
		    	'error_message' => __('A filter with this ID was not found', 'tainacan' ),
			    'filter_id' => $filter_id
		    ], 400);
		}

		if($permanently == true) {
			$filter = $this->filter_repository->delete($filter);
		} else {
			$filter = $this->filter_repository->trash($filter);
		}

		return new \WP_REST_Response($this->prepare_item_for_response($filter, $request), 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
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
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
		$filter_id = $request['filter_id'];

		$body = json_decode($request->get_body(), true);

		if(!empty($body)){
			$attributes = [];

			foreach ($body as $att => $value){
				$attributes[$att] = $value;
			}

			$filter = $this->filter_repository->fetch($filter_id);

			if($filter) {
				$prepared_filter = $this->prepare_item_for_updating($filter, $attributes);

				if($prepared_filter->validate()) {
					$updated_filter = $this->filter_repository->update( $prepared_filter );

					return new \WP_REST_Response($this->prepare_item_for_response($updated_filter, $request), 200);
				}

				return new \WP_REST_Response([
					'error_message' => __('Please verify, invalid value(s).', 'tainacan'),
					'errors'        => $prepared_filter->get_errors(),
					'filters'       => $this->prepare_item_for_response($prepared_filter, $request)
				], 400);
			}

			return new \WP_REST_Response([
				'error_message' => __('A filter with this ID was not found', 'tainacan' ),
				'filter_id'     => $filter_id
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
		$filter = $this->filter_repository->fetch($request['filter_id']);

		if ($filter instanceof Entities\Filter) {
			return $filter->can_edit();
		}

		return false;
	}

	/**
	 * @param $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if(!empty($item)) {
			$item_arr = $item->_toArray();

			if($request['context'] === 'edit'){
				$item_arr['current_user_can_edit'] = $item->can_edit();
				$item_arr['current_user_can_delete'] = $item->can_delete();
				$item_arr['enabled'] = $item->get_enabled_for_collection();
			}

			$item_arr['filter_type_object'] = $item->get_filter_type_object() ? $item->get_filter_type_object()->_toArray() : $item->get_filter_type_object();

			/**
			 * Use this filter to add additional post_meta to the api response
			 * Use the $request object to get the context of the request and other variables
			 * For example, id context is edit, you may want to add your meta or not.
			 * 
			 * Also take care to do any permissions verification before exposing the data
			 */
			$extra_metadata = apply_filters('tainacan-api-response-filter-meta', [], $request);

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
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {
		$args = $this->prepare_filters( $request );

		if ($request['include_disabled'] === 'true') {
			$args['include_disabled'] = true;
		}

		if(!isset($request['collection_id'])) {
			$args['meta_query'][] = [
				'key'     => 'collection_id',
				'value'   => 'filter_in_repository',
				'compare' => '='
			];

			$filters = $this->filter_repository->fetch( $args, 'OBJECT' );
		} else {
			$collection = $this->collection_repository->fetch($request['collection_id']);
			$filters = $this->filter_repository->fetch_by_collection($collection, $args, 'OBJECT');
		}

		$response = [];
		foreach ( $filters as $filter ) {
			array_push( $response, $this->prepare_item_for_response( $filter, $request ) );
		}

		return new \WP_REST_Response($response, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if(!isset($request['collection_id'])) {
			if ( 'edit' === $request['context'] && ! $this->filter_repository->can_read( new Entities\Filter() ) ) {
				return false;
			}

			return true;
		}

		$collection = $this->collection_repository->fetch($request['collection_id']);

		if($collection instanceof Entities\Collection){
			if ( 'edit' === $request['context'] && ! $collection->can_read() ) {
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
		$filter_id = $request['filter_id'];

		$filter = $this->filter_repository->fetch($filter_id);
		
		if(! $filter instanceof Entities\Filter) {
			return new \WP_REST_Response([
		    	'error_message' => __('A filter with this ID was not found', 'tainacan' ),
			    'filter_id' => $filter_id
		    ], 400);
		}
		
		return new \WP_REST_Response($this->prepare_item_for_response($filter, $request), 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$filter = $this->filter_repository->fetch($request['filter_id']);

		if(($filter instanceof Entities\Filter)) {
			if('edit' === $request['context'] && !$filter->can_read()) {
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
			$map = $this->filter_repository->get_map();

			foreach ($map as $mapped => $value){
				$set_ = 'set_'. $mapped;

				// Show only args that has a method set
				if( !method_exists(new Entities\Filter(), "$set_") ){
					unset($map[$mapped]);
				}
			}

			$endpoint_args = $map;
		}

		return $endpoint_args;
	}

	/**
	 * @param null $object_name
	 *
	 * @return array|void
	 */
	public function get_collection_params( $object_name = null ) {
		$query_params['context']['default'] = 'view';

		$query_params = array_merge($query_params, parent::get_collection_params('filter'));

		$query_params['name'] = array(
			'description' => __('Limits the result set to filters with a specific name'),
			'type'        => 'string',
		);

		$query_params = array_merge($query_params, parent::get_meta_queries_params());

		return $query_params;
	}
}
?>