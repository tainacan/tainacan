<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;
use Tainacan\Entities;
use Tainacan\Repositories;

class REST_Roles_Controller extends REST_Controller {


	/**
	 * REST_Roles_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'roles';
		parent::__construct();
		
		$this->core_roles = [
			'administrator',
			'editor',
			'author',
			'contributor',
			'subscriber'
		];
		
		//add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	// public function init_objects() {
	// 	$this->collection = new Entities\Collection();
	// 	$this->collection_repository = Repositories\Collections::get_instance();
	// 	
	// 	$this->metadatum_repository = Repositories\Metadata::get_instance();
	// 	
	// 	$this->filter_repository = Repositories\Filters::get_instance();
	// }

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				//'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
			),
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'create_item'),
				'permission_callback' => array($this, 'create_item_permissions_check'),
				'args'                => array(
					'name' => array(
						'description' => __('New role name', 'tainacan'),
						'type' => 'string',
						'required' => true
					),
				)
			),
			'schema'                  => [$this, 'get_schema']
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<role>[a-z-]+)', array(
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'delete_item_permissions_check'),
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
			),
			'schema'                  => [$this, 'get_schema']
		));
	}


	

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {
		
		//global $wpdb;
		//$name = $wpdb->prepare( $request['name'], '' );
		$name = esc_html( esc_sql( $request['name'] ) );
		
		$role_slug = sanitize_title($name);
		
		// avoid confusion ...
		if ( in_array($role_slug, $this->core_roles) ) {
			return new \WP_REST_Response([
				'error_message' => __('This role name is protected.', 'tainacan'),
				'error'         => $name
			], 400);
		}
		
		// ... even though it could work 
		$role_slug = 'tainacan-' . $role_slug;
		
		// check if role exists
		$role = get_role($role_slug);
		if ( $role ) {
			return new \WP_REST_Response([
				'error_message' => __('Role already exists.', 'tainacan'),
				'error'         => $name
			], 400);
		}
		
		$new_role = add_role($role_slug, $name);
		
		if ($new_role instanceof \WP_Role) {
			return new \WP_REST_Response($this->_prepare_item_for_response($role_slug, $new_role, $request), 201);
		}

		return new \WP_REST_Response([
			'error_message' => __('Error creating role', 'tainacan'),
			'error'         => $name
		], 400);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can('edit_tainacan_users');
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
		return current_user_can('edit_tainacan_users');
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
		return current_user_can('edit_tainacan_users');
	}

	/**
	 * @param $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|mixed|\WP_Error|\WP_REST_Response
	 */
	public function _prepare_item_for_response( $slug, $role, $request ) {
		$return = [];
		$return[$slug] = $role;
		return $return;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {
		
		$roles = get_editable_roles();

		$response = [];
		foreach ( $roles as $slug => $role ) {
			array_push( $response, $this->prepare_item_for_response( $slug, $role, $request ) );
		}

		return new \WP_REST_Response($roles, 200);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can('edit_tainacan_users');
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
		return current_user_can('edit_tainacan_users');
	}

	/**
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args_for_item_schema( $method = null ) {
		return [];
	}

	

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => 'filter',
			'type' => 'object'
		];
		
		return $schema;
		
	}
}
?>