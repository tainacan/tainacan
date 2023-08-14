<?php

namespace Tainacan\API\EndPoints;

use \Tainacan\API\REST_Controller;

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
				'args'                => $this->get_endpoint_args(\WP_REST_Server::READABLE)
			),
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'create_item'),
				'permission_callback' => array($this, 'create_item_permissions_check'),
				'args'                => $this->get_endpoint_args(\WP_REST_Server::CREATABLE)
			),
			'schema'                  => [$this, 'get_list_schema']
		));
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<role>[a-z0-9-_]+)', array(
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'delete_item_permissions_check'),
				'args'                => $this->get_endpoint_args(\WP_REST_Server::DELETABLE)
			),
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_item'),
				'permission_callback' => array($this, 'update_item_permissions_check'),
				'args'                => $this->get_endpoint_args(\WP_REST_Server::EDITABLE)
			),
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check'),
				'args'                => $this->get_endpoint_args(\WP_REST_Server::READABLE)
			),
			'schema'                  => [$this, 'get_schema']
		));
		register_rest_route(
			$this->namespace, '/collection/(?P<collection_id>[\d]+)/capabilities',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_capabilities'),
					'permission_callback' => array($this, 'get_capabilities_permissions_check'),
					'args'				  => [
						'collection_id' => [
							'description' => __( 'Collection ID', 'tainacan' ),
							'type' => 'string',
							'required' => true,
						]
					]
				),
				'schema'                  => [$this, 'get_capabilities_schema']
		));
		register_rest_route(
			$this->namespace, '/capabilities',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_capabilities'),
					'permission_callback' => array($this, 'get_capabilities_permissions_check'),
				),
				'schema'                  => [$this, 'get_capabilities_schema']
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

		// allow restricted name format ...
		if( preg_match('/^[a-zA-Z0-9-_ ]*$/', $name) == false ) {
			return new \WP_REST_Response([
				'error_message' => __('This role name is not allowed. Use only letters, numbers, underscore and hyphen', 'tainacan'),
				'error'         => $name
			], 400);
		}

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

		if ( isset($request['capabilities']) && is_array($request['capabilities']) ) {
			$this->handle_capabilities_for_role($role_slug, $request['capabilities']);
			$this->handle_capabilites_default_for_role($role_slug);
		}

		if ($new_role instanceof \WP_Role) {

			// every role should at least be able to read
			$new_role->add_cap( 'read' );

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
		return current_user_can('tnc_rep_edit_users');
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
		return current_user_can('tnc_rep_edit_users');
	}

	/**
	 * @param $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {

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

		if ( isset($request['name']) ) {

			$name = esc_html( esc_sql( $request['name'] ) );
			// allow restricted name format ...
			if( preg_match('/^[a-zA-Z0-9-_ ]*$/', $name) == false ) {
				return new \WP_REST_Response([
					'error_message' => __('This role name is not allowed. Use only letters, numbers, underscore and hyphen', 'tainacan'),
					'error'         => $name
				], 400);
			}

			// the slug remains the same
			\wp_roles()->roles[$role_slug]['name'] = $name;
			update_option( \wp_roles()->role_key, \wp_roles()->roles );
			\wp_roles()->role_names[$role_slug] = $name;

		}

		if ( is_array($request['capabilities']) ) {

			$this->handle_capabilities_for_role($role_slug, $request['capabilities']);
			$this->handle_capabilites_default_for_role($role_slug);

		} elseif ( isset($request['add_cap']) ) {
			// validate that we only deal with tainacan capabilities
			if ( ! in_array( \tainacan_roles()->get_cap_generic_name($request['add_cap']) , \tainacan_roles()->get_all_caps_slugs() ) ) {
				return new \WP_REST_Response([
					'error_message' => __('Not allowed to edit non Tainacan capabilities.', 'tainacan'),
					'error'         => $request['add_cap']
				], 400);
			}

			\wp_roles()->add_cap($role_slug, $request['add_cap']);
			\tainacan_roles()->add_dependencies($role_slug, $request['add_cap']);
		} elseif ( isset($request['remove_cap']) ) {
			// validate that we only deal with tainacan capabilities
			if ( ! in_array( \tainacan_roles()->get_cap_generic_name($request['remove_cap']) , \tainacan_roles()->get_all_caps_slugs() ) ) {
				return new \WP_REST_Response([
					'error_message' => __('Not allowed to edit non Tainacan capabilities.', 'tainacan'),
					'error'         => $request['remove_cap']
				], 400);
			}
			\wp_roles()->remove_cap($role_slug, $request['remove_cap']);
		}

		return new \WP_REST_Response($this->_prepare_item_for_response($role_slug, \wp_roles()->roles[$role_slug]['name'], \wp_roles()->roles[$role_slug]['capabilities'], $request), 200);

	}

	private function handle_capabilities_for_role($role_slug, $newcaps) {

		if ( !isset( \wp_roles()->roles[$role_slug] ) ) {
			return false;
		}

		$role = \wp_roles()->roles[$role_slug];

		foreach ($role['capabilities'] as $cap => $val) {
			if ( ! in_array( \tainacan_roles()->get_cap_generic_name($cap) , \tainacan_roles()->get_all_caps_slugs() ) ) {
				continue;
			}

			if ( !array_key_exists($cap, $newcaps) ) {
				\wp_roles()->remove_cap($role_slug, $cap);
			}

		}

		foreach ( $newcaps as $cap => $val ) {
			\wp_roles()->add_cap($role_slug, $cap, $val);
			\tainacan_roles()->add_dependencies($role_slug, $cap);
		}



	}

	private function handle_capabilites_default_for_role($role_slug) {
		if ( !isset( \wp_roles()->roles[$role_slug] ) ) {
			return false;
		}
		\wp_roles()->add_cap($role_slug, 'read', true);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		if ( current_user_can('tnc_rep_edit_users') ) {
			return true;
		}

		if ( !isset($request['name']) ) {

			$return = true;
			$cap = '';
			if ( isset($request['add_cap']) ) {
				$return = in_array( \tainacan_roles()->get_cap_generic_name($request['add_cap']), \tainacan_roles()->get_collection_caps_slugs());
				$cap = $request['add_cap'];
			} elseif ( isset($request['remove_cap']) ) {
				$return = in_array( \tainacan_roles()->get_cap_generic_name($request['remove_cap']), \tainacan_roles()->get_collection_caps_slugs());
				$cap = $request['remove_cap'];
			}
			if ($return) {
				$collection_id = preg_replace('/[a-z_]/', '', $cap);
				if ( is_numeric($collection_id) ) {
					return current_user_can('tnc_col_' . $collection_id . '_edit_users');
				}

			}

		}

		return false;
	}

	public function validate_roles_capabilities_arg($value, $request, $param) {
		if ( is_array($value) ) {
			foreach ($value as $cap => $val) {
				if ( ! in_array( \tainacan_roles()->get_cap_generic_name($cap), \tainacan_roles()->get_all_caps_slugs() ) ) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * @param $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|mixed|\WP_Error|\WP_REST_Response
	 */
	public function _prepare_item_for_response( $slug, $name, $caps, $request ) {
		$caps = array_filter($caps, function($el) {
			return in_array( \tainacan_roles()->get_cap_generic_name($el), \tainacan_roles()->get_all_caps_slugs() );
		}, ARRAY_FILTER_USE_KEY);

		$return = [
			'slug' => $slug,
			'name' => translate_user_role($name),
			'capabilities' => $caps
		];
		$return = apply_filters('tainacan-api-role-prepare-for-response', $return, $request);
		return $return;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function get_items( $request ) {

		if (!function_exists('get_editable_roles')) {
			require_once(ABSPATH . '/wp-admin/includes/user.php');
		}

		$roles = \get_editable_roles();

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
		return current_user_can('read');
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
		return current_user_can('tnc_rep_edit_users');
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_capabilities_permissions_check( $request ) {
		if ( current_user_can('tnc_rep_edit_users') ) {
			return true;
		}
		if ( isset($request['collection_id']) ) {
			return current_user_can( 'tnc_col_' . $request['collection_id'] . '_edit_users' );
		}
		return false;
	}

	/**
	 * @param string $method
	 *
	 * @return array|mixed
	 */
	public function get_endpoint_args( $method = null ) {
		$endpoint_args = [];
		
		switch ( $method ) {
			case \WP_REST_Server::EDITABLE:
				$endpoint_args['role'] = array(
					'description' => __( 'Role slug', 'tainacan' ),
					'type' => 'string',
					'required' => true
				);
				$endpoint_args['add_cap'] = array(
					'description' => __( 'the capability slug to be add', 'tainacan' ),
					'type' => 'string',
					'required' => false
				);
				$endpoint_args['remove_cap'] = array(
					'description' => __( 'The capability slug to be removed', 'tainacan' ),
					'type' => 'string',
					'required' => false
				);
			case \WP_REST_Server::CREATABLE:
				$endpoint_args['name'] = array(
					'description' => __('New role name', 'tainacan'),
					'type' => 'string',
					'required' => $method  == \WP_REST_Server::CREATABLE
				);
				$endpoint_args['capabilities'] = array(
					'description' => __('Array of capabilities, where the keys are capability slugs and values are booleans', 'tainacan'),
					'required' => false,
					'validate_callback' => [$this, 'validate_roles_capabilities_arg']
				);
			break;
			case \WP_REST_Server::READABLE:
			case \WP_REST_Server::DELETABLE:
				$endpoint_args['role'] = array(
					'description' => __( 'Role slug', 'tainacan' ),
					'type' => 'string',
					'required' => $method == \WP_REST_Server::DELETABLE
				);
			break;
		}

		return $endpoint_args;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_capabilities( $request ) {

		$collection_id = isset($request['collection_id']) ? $request['collection_id'] : false;

		$roles = \wp_roles()->roles;

		$caps_return = [];

		if ($collection_id) {
			$caps = \tainacan_roles()->get_collection_caps();
		} else {
			$caps = \tainacan_roles()->get_all_caps();
		}

		foreach ($caps as $cap => $c) {

			$realcap = $cap;
			if ($collection_id) {
				$realcap = str_replace('%d', $collection_id, $cap);
			}

			$caps_return[$realcap] = $caps[$cap];
			$caps_return[$realcap]['roles'] = [];
			$caps_return[$realcap]['roles_inherited'] = [];

			foreach ($roles as $slug => $role) {

				if ( array_key_exists($realcap, $role['capabilities']) ) {
					$caps_return[$realcap]['roles'][$slug] = [
						'slug' => $slug,
						'name' => translate_user_role($role['name']),
					];
					continue;
				}

				// inherited roles
				$supercaps = $c['supercaps'];

				foreach ($supercaps as $supercap) {

					if ($collection_id) {
						$supercap = str_replace('%d', $collection_id, $supercap);
					}

					if ( array_key_exists($supercap, $role['capabilities']) ) {

						$caps_return[$realcap]['roles_inherited'][$slug] = [
							'slug' => $slug,
							'name' => translate_user_role($role['name']),
						];
						break;
					}

				} // for each supercaps



			} // for each role

		} // for each cap

		return new \WP_REST_Response(['capabilities' => $caps_return], 200);

	}

	function get_capabilities_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => "$this->rest_base-capabilities",
			'type' => 'object',
			'tags' => [ $this->rest_base ],
			'properties' => [
				'capabilities' => [
					'description' => __( 'Capabilities for the user role.', 'tainacan' ),
					'type'        => 'object',
					'properties' 	 => [
						'[capability]:string' => [
							'type' => 'object',
							'description' => __( 'Capability object', 'tainacan' ),
							'properties' => [
								'display_name' => [
									'description' => __( 'Display name for the capability.', 'tainacan' ),
									'type'        => 'string'
								],
								'description' => [
									'description' => __( 'Description for the capability.', 'tainacan' ),
									'type'        => 'string'
								],
								'scope' => [
									'description' => __( 'Scope for the capability.', 'tainacan' ),
									'type'        => 'string',
									'enum'		  => [ 'repository', 'collection' ]
								],
								'superpcaps' => [
									'description' => __( 'Super capabilities that have precendence over this capability.', 'tainacan' ),
									'type'        => 'array',
									'items'		  => [
										'type' => 'string'
									]
								],
								'roles' => [
									'description' => __( 'Roles that have this capability.', 'tainacan' ),
									'type'        => 'array',
									'items'		  => [
										'type' => 'object',
										'properties' => [
											'slug' => [
												'description' => __( 'Slug for the role.', 'tainacan' ),
												'type'        => 'string'
											],
											'name' => [
												'description' => __( 'Display name for the role.', 'tainacan' ),
												'type'        => 'string'
											],
										]
									]
								],
								'roles_inherit' => [
									'description' => __( 'Roles that inherit this capability.', 'tainacan' ),
									'type'        => 'array',
									'items'		  => [
										'type' => 'object',
										'properties' => [
											'slug' => [
												'description' => __( 'Slug for the role.', 'tainacan' ),
												'type'        => 'string'
											],
											'name' => [
												'description' => __( 'Display name for the role.', 'tainacan' ),
												'type'        => 'string'
											],
										]
									]
								],
							]
						]
					]
				],
			]
		];

		return $schema;
	}

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => $this->rest_base,
			'type' => 'object',
			'tags' => [ $this->rest_base ],
			'properties' => [
				'slug' => [
					'description' => __( 'Unique identifier for the user role.', 'tainacan' ),
					'type'        => 'string'
				],
				'name' => [
					'description' => __( 'Display name for the user role.', 'tainacan' ),
					'type'        => 'string'
				],
				'capabilities' => [
					'description' => __( 'Capabilities for the user role.', 'tainacan' ),
					'type'        => 'object',
					'properties' 	 => [
						'[capability]:string' => [
							'type' => 'boolean',
							'description' => __( 'Whether the role has the given capability.', 'tainacan' ),
						]
					]
				],
			]
		];

		return $schema;
	}
}
?>
