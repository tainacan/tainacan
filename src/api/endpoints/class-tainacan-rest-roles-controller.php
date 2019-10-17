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
		
	}

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
				'args'                => array(
					'name' => array(
						'description' => __('New role name', 'tainacan'),
						'type' => 'string',
						'required' => false
					),
					'add_cap' => array(
						'description' => __('Slug of the capability to be added to the role', 'tainacan'),
						'type' => 'string',
						'required' => false
					),
					'remove_cap' => array(
						'description' => __('Slug of the capability to be removed from the role', 'tainacan'),
						'type' => 'string',
						'required' => false
					),
				)
			),
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check'),
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
			return new \WP_REST_Response($this->_prepare_item_for_response($role_slug, $name, $new_role->capabilities, $request), 201);
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
		
		$role_slug = $request['role'];
		
		// avoid confusion ...
		if ( in_array($role_slug, $this->core_roles) ) {
			return new \WP_REST_Response([
				'error_message' => __('This role name is protected.', 'tainacan'),
				'error'         => $name
			], 400);
		}
		
		// ... even though it could work 
		$role_slug = 0 === \strpos($role_slug, 'tainacan-') ? $role_slug : 'tainacan-' . $role_slug;
		
		// check if role exists
		$role = get_role($role_slug);
		if ( ! $role ) {
			return new \WP_REST_Response([
				'error_message' => __('Role not found.', 'tainacan'),
				'error'         => $role_slug
			], 400);
		}
		
		\remove_role($role_slug);
		
		return new \WP_REST_Response($role_slug, 200);
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
		
		$role_slug = $request['role'];
		
		// avoid confusion ...
		if ( in_array($role_slug, $this->core_roles) ) {
			return new \WP_REST_Response([
				'error_message' => __('This role name is protected.', 'tainacan'),
				'error'         => $name
			], 400);
		}
		
		// ... even though it could work 
		$role_slug = 0 === \strpos($role_slug, 'tainacan-') ? $role_slug : 'tainacan-' . $role_slug;
		
		// check if role exists
		// get the role from roles array that contains the display_name
		$roles = \wp_roles()->roles;
		if ( ! isset($roles[$role_slug]) ) {
			return new \WP_REST_Response([
				'error_message' => __('Role not found.', 'tainacan'),
				'error'         => $role_slug
			], 400);
		}
		
		$role = $roles[$role_slug];
		
		if ( isset($request['name']) ) {
			$name = esc_html( esc_sql( $request['name'] ) );
			// the slug remains the same
			\wp_roles()->roles[$role_slug]['name'] = $name;
			update_option( \wp_roles()->role_key, \wp_roles()->roles );
			\wp_roles()->role_names[$role_slug] = $name;
			
		}
		
		if ( isset($request['add_cap']) ) {
			// validate that we only deal with tainacan capabilities 
			\wp_roles()->add_cap($role_slug, $request['add_cap']);
		}
		
		if ( isset($request['remove_cap']) ) {
			// validate that we only deal with tainacan capabilities 
			\wp_roles()->remove_cap($role_slug, $request['remove_cap']);
		}
		
		return new \WP_REST_Response($this->_prepare_item_for_response($role_slug, \wp_roles()->roles[$role_slug]['name'], \wp_roles()->roles[$role_slug]['capabilities'], $request), 200);

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
	public function _prepare_item_for_response( $slug, $name, $caps, $request ) {
		$return = [
			'slug' => $slug,
			'name' => translate_user_role($name),
			'capabilities' => $caps
		];
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
			$response[$slug] = $this->_prepare_item_for_response( $slug, $role['name'], $role['capabilities'], $request );
		}

		return new \WP_REST_Response($response, 200);
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
		
		$role_slug = $request['role'];
		
		// check if role exists
		// get the role from roles array that contains the display_name
		$roles = \wp_roles()->roles;
		if ( ! isset($roles[$role_slug]) ) {
			return new \WP_REST_Response([
				'error_message' => __('Role not found.', 'tainacan'),
				'error'         => $role_slug
			], 400);
		}
		
		return new \WP_REST_Response($this->_prepare_item_for_response($role_slug, $roles[$role_slug]['name'], $roles[$role_slug]['capabilities'], $request), 200);

	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		return current_user_can('edit_tainacan_users');
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