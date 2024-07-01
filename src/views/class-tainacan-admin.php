<?php

namespace Tainacan;


class Admin {

	private $menu_slug = 'tainacan_admin';
	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {

		add_action( 'wp_ajax_tainacan-sample-permalink', array( &$this, 'ajax_sample_permalink') );

		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
		add_filter( 'admin_body_class', array( &$this, 'admin_body_class' ) );

		add_action( 'init', array( &$this, 'register_user_meta' ) );
		add_action( 'after_setup_theme', array( &$this, 'load_theme_files'));

		add_action( 'admin_init', array( &$this, 'register_user_setting') );
	}

	function add_admin_menu() {

		$page_suffix = add_menu_page(
			__( 'Tainacan', 'tainacan' ),
			__( 'Tainacan', 'tainacan' ),
			'read',
			$this->menu_slug,
			array( &$this, 'admin_page' ),
			plugin_dir_url( __FILE__ ) . '../assets/images/tainacan_logo_symbol.svg'
		);

		add_submenu_page(
			$this->menu_slug,
			__('System check', 'tainacan'),
			__('System check', 'tainacan'),
			'manage_options',
			'tainacan_systemcheck',
			array( &$this, 'systemcheck_page' )
		);

		$roles_page_suffix = add_submenu_page(
			$this->menu_slug,
			__('User Roles', 'tainacan'),
			__('User Roles', 'tainacan'),
			'tnc_rep_edit_users',
			'tainacan_roles',
			array( &$this, 'roles_page' )
		);

		$reports_page_suffix = add_submenu_page(
			$this->menu_slug,
			__('Reports', 'tainacan'),
			__('Reports', 'tainacan'),
			'manage_tainacan',
			'tainacan_reports',
			array( &$this, 'reports_page' )
		);

		add_submenu_page(
			$this->menu_slug,
			__('Item Submission', 'tainacan'),
			__('Item Submission', 'tainacan'),
			'manage_options',
			'tainacan_item_submission',
			array( &$this, 'item_submission' )
		);

		$mobile_app_page_suffix = add_submenu_page(
			'tainacan-no-show-menu', // Mobile app page is not listed in the menu
			__('Mobile App', 'tainacan'),
			__('Mobile App', 'tainacan'),
			'manage_tainacan',
			'tainacan_mobile_app',
			array( &$this, 'mobile_app' )
		);
		
		add_action( 'load-' . $page_suffix, array( &$this, 'load_admin_page' ) );
		add_action( 'load-' . $roles_page_suffix, array( &$this, 'load_roles_page' ) );
		add_action( 'load-' . $reports_page_suffix, array( &$this, 'load_reports_page' ) );
		add_action( 'load-' . $mobile_app_page_suffix, array( &$this, 'load_mobile_app_page' ) );
	}

	function load_admin_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_js' ), 90 );
		add_action( 'admin_enqueue_scripts', array(&$this, 'add_theme_files') );
	}

	function load_roles_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_roles_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_roles_js' ), 90 );
	}

	function load_reports_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_reports_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_reports_js' ), 90 );
	}

	function load_mobile_app_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_mobile_app_css' ), 90 );
	}

	function login_styles_reset( $style ) {
		if ( strpos( $style, 'wp-admin-css' ) !== false ) {
			$style = null;
		}

		return $style;
	}

	function load_theme_files() {
		add_action( 'wp_enqueue_scripts', array(&$this, 'add_theme_files') );
	}

	function add_theme_files() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/css/tainacanicons.css', [], TAINACAN_VERSION );
		wp_enqueue_script('underscore');
	}

	function add_roles_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-roles-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-roles.css', [], TAINACAN_VERSION );
	}

	function add_mobile_app_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-mobile-app-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-mobile-app.css', [], TAINACAN_VERSION );
	}

	function add_roles_js() {

		global $TAINACAN_BASE_URL;

		wp_enqueue_script(
			'tainacan-pages-common-scripts',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_pages_common_scripts.js',
			['underscore', 'wp-i18n'],
			TAINACAN_VERSION
		);
		wp_set_script_translations('tainacan-pages-common-scripts', 'tainacan');

		$settings = $this->get_admin_js_localization_params();
		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_plugin', $settings );
		wp_enqueue_script('underscore');
		wp_enqueue_script('wp-i18n');

		do_action('tainacan-enqueue-roles-scripts');
	}

	function roles_page() {
		$allowed_html = [
			'div' => [
				'id' => true,
				'style' => true,
				'class' => true,
				'data-module' => true
			]
		];
		echo wp_kses( "<div id='tainacan-roles-app' data-module='roles'></div>", $allowed_html );
	}

	function add_reports_css() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/css/tainacanicons.css', [], TAINACAN_VERSION );
		wp_enqueue_style( 'tainacan-reports-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-reports.css', [], TAINACAN_VERSION );
	}

	function add_reports_js() {

		global $TAINACAN_BASE_URL;

		wp_enqueue_script(
			'tainacan-pages-common-scripts',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_pages_common_scripts.js',
			['underscore', 'wp-i18n'],
			TAINACAN_VERSION
		);
		wp_set_script_translations('tainacan-pages-common-scripts', 'tainacan');

		$settings = $this->get_admin_js_localization_params();
		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_plugin', $settings );
		wp_enqueue_script('underscore');
		wp_enqueue_script('wp-i18n');

		do_action('tainacan-enqueue-reports-scripts');
	}

	function reports_page() {
		
		$allowed_html = [
			'div' => [
				'id' => true,
				'style' => true,
				'class' => true,
				'data-module' => true
			]
		];
		echo wp_kses( "<div id='tainacan-reports-app'  data-module='reports'></div>", $allowed_html );
	}

	function add_admin_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'roboto-fonts', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i', [] );
		wp_enqueue_style( 'tainacan-admin-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-admin.css', [], TAINACAN_VERSION );

		// $undesired_wp_styles = [
		// 	'admin-menu',
		// 	'admin-bar',
		// 	'code-editor',
		// 	'color-picker',
		// 	'customize-controls',
		// 	'customize-nav-menus',
		// 	'customize-widgets',
		// 	'dashboard',
		// 	'dashicons',
		// 	'deprecated-media',
		// 	'edit',
		// 	'wp-pointer',
		// 	'farbtastic',
		// 	'forms',
		// 	'common',
		// 	'install',
		// 	'wp-auth-check',
		// 	'site-icon',
		// 	'buttons',
		// 	'l10n',
		// 	'list-tables',
		// 	'login',
		// 	'media',
		// 	'nav-menus',
		// 	'revisions',
		// 	'themes',
		// 	'widgets',
		// 	'wp-admin'
		// ];

		// wp_dequeue_style( $undesired_wp_styles );
		// wp_deregister_style( $undesired_wp_styles );

	}

	function add_admin_js() {
		global $TAINACAN_BASE_URL;
		global $TAINACAN_EXTRA_SCRIPTS;

		$deps = ['underscore', 'media-editor', 'media-views', 'customize-controls', 'wp-i18n'];
		if ( !empty($TAINACAN_EXTRA_SCRIPTS) ) {
			foreach($TAINACAN_EXTRA_SCRIPTS as $dep) {
				$deps[] = $dep;
			}
		}

		wp_enqueue_script(
			'tainacan-pages-common-scripts',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_pages_common_scripts.js',
			$deps,
			TAINACAN_VERSION
		);
		$settings = $this->get_admin_js_localization_params();

		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_plugin', $settings );
		wp_enqueue_media(
			 //[ 'post' => 131528 ]
		);
		wp_enqueue_script('underscore');
		wp_enqueue_script('jcrop');
		wp_enqueue_script( 'customize-controls' );

		do_action('tainacan-enqueue-admin-scripts');

	}

	function get_admin_js_localization_params() {
		global $TAINACAN_BASE_URL, $TAINACAN_API_MAX_ITEMS_PER_PAGE;

		$Tainacan_Collections 		= \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Metadata    		= \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Metadata_Sections = \Tainacan\Repositories\Metadata_Sections::get_instance();
		$Tainacan_Filters     		= \Tainacan\Repositories\Filters::get_instance();
		$Tainacan_Items       		= \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Taxonomies  		= \Tainacan\Repositories\Taxonomies::get_instance();

		$tainacan_admin_i18n = require( 'tainacan-i18n.php' );

		$entities_labels = [
			'collections' 		=> $Tainacan_Collections->get_cpt_labels(),
			'metadata'      	=> $Tainacan_Metadata->get_cpt_labels(),
			'metadata-sections' => $Tainacan_Metadata_Sections->get_cpt_labels(),
			'filters'     		=> $Tainacan_Filters->get_cpt_labels(),
			'items'       		=> $Tainacan_Items->get_cpt_labels(),
			'taxonomies'  		=> $Tainacan_Taxonomies->get_cpt_labels(),
		];

		$tainacan_admin_i18n['entities_labels'] = $entities_labels;

		$cur_user  = wp_get_current_user();
		$user_caps = array();
		$prefs     = array();
		$user_data = array();
		if ( $cur_user instanceof \WP_User ) {
			$tainacan_caps = \tainacan_roles()->get_repository_caps_slugs();
			foreach ($tainacan_caps as $tcap) {
				$user_caps[$tcap] = current_user_can( $tcap );
			}
			$prefs = get_user_meta( $cur_user->ID, 'tainacan_prefs', true );

			if ( $cur_user->data && isset($cur_user->data->user_email) && isset($cur_user->data->display_name) ) {
				$user_data = array(
					'ID' => $cur_user->ID,
					'email' => $cur_user->data->user_email,
					'display_name' => $cur_user->data->display_name
				);
			}
		}

		$settings = [
			'tainacan_api_url'         	=> esc_url_raw( rest_url() ) . 'tainacan/v2',
			'wp_api_url'            	=> esc_url_raw( rest_url() ) . 'wp/v2/',
			'wp_ajax_url'            	=> admin_url( 'admin-ajax.php' ),
			'nonce'                  	=> is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : false,
			'classes'                	=> array(),
			'i18n'                   	=> $tainacan_admin_i18n,
			'user_caps'              	=> $user_caps,
			'user_prefs'             	=> $prefs,
			'user_data'					=> $user_data,
			'base_url'               	=> $TAINACAN_BASE_URL,
			'plugin_dir_url'			=> plugin_dir_url( __DIR__ ),
			'admin_url'              	=> admin_url(),
			'theme_items_list_url' 		=> esc_url_raw( get_site_url() ) . '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug(),
			'theme_collection_list_url' => get_post_type_archive_link( 'tainacan-collection' ),
			'theme_taxonomy_list_url' 	=> get_post_type_archive_link( 'tainacan-taxonomy' ),
			'custom_header_support'  	=> get_theme_support('custom-header'),
			'registered_view_modes'  	=> \Tainacan\Theme_Helper::get_instance()->get_registered_view_modes(),
			'exposer_mapper_param'   	=> \Tainacan\Mappers_Handler::MAPPER_PARAM,
			'exposer_type_param'     	=> \Tainacan\Exposers_Handler::TYPE_PARAM,
			'repository_name'	 		=> get_bloginfo('name'),
			'api_max_items_per_page'    => $TAINACAN_API_MAX_ITEMS_PER_PAGE,
			'wp_elasticpress'    		=> \Tainacan\Elastic_Press::get_instance()->is_active(),
			'item_submission_captcha_site_key' => get_option("tnc_option_recaptch_site_key"),
			'tainacan_enable_core_metadata_on_advanced_search' => ( !defined('TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH') || false === TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH ),
			'tainacan_enable_relationship_metaquery' => ( defined('TAINACAN_ENABLE_RELATIONSHIP_METAQUERY') && true === TAINACAN_ENABLE_RELATIONSHIP_METAQUERY ),
			'has_permalinks_structure' => get_option('permalink_structure') !== ''
		];
		
		$maps = [
			'collections' 		=> $Tainacan_Collections->get_map(),
			'metadata'    		=> $Tainacan_Metadata->get_map(),
			'metadata-sections' => $Tainacan_Metadata_Sections->get_map(),
			'filters'     		=> $Tainacan_Filters->get_map(),
			'items'       		=> $Tainacan_Items->get_map(),
			'taxonomies'  		=> $Tainacan_Taxonomies->get_map(),
		];

		$metadata_types = $Tainacan_Metadata->fetch_metadata_types();

		foreach( $maps as $type => $map ){
			foreach ( $map as $metadatum => $details){
				$settings['i18n']['helpers_label'][$type][$metadatum] = [ 'title' => $details['title'], 'description' => $details['description'] ];
			}
		}
		foreach ( $metadata_types as $index => $metadata_type){
			$class = new $metadata_type;
			$settings['i18n']['helpers_label'][$class->get_component()] = $class->get_form_labels();
		}

		$filter_types = $Tainacan_Filters->fetch_filter_types();
		
		foreach ( $filter_types as $index => $filter_type){
			$class = new $filter_type;
			$settings['i18n']['helpers_label'][$class->get_component()] = $class->get_form_labels();
		}

		$settings['form_hooks'] = Admin_Hooks::get_instance()->get_registered_hooks();

		$wp_post_types = get_post_types(['show_ui' => true], 'objects');
		if (isset($wp_post_types['attachment'])) {
			unset($wp_post_types['attachment']);
		}

		$wp_post_types = array_map(function($i) {
			return [
				'slug' => $i->name,
				'label' => $i->label
			];
		}, $wp_post_types);

		$settings['wp_post_types'] = $wp_post_types;

		// Key-valued array with extra options to be passed to every request in the admin (goes the header)
		$admin_request_options = [];
		$admin_request_options = apply_filters('tainacan-admin-extra-request-options', $admin_request_options);
		$settings['admin_request_options'] = $admin_request_options;

		return $settings;

	}

	function admin_body_class( $classes ) {

		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->menu_slug )
			$classes .= ' tainacan-admin-page';
		
		return $classes;
	}

	function admin_page() {
		global $TAINACAN_BASE_URL;
		
		// @deprecated: use tainacan-admin-ui-options instead
		$admin_options = apply_filters('set_tainacan_admin_options', $_GET);
		$admin_options = apply_filters('tainacan-admin-ui-options', $_GET);
		$admin_options = json_encode($admin_options);

		$allowed_html = [
			'div' => [
				'id' => true,
				'style' => true,
				'class' => true,
				'data-module' => true,
				'data-options' => true
			]
		];
		echo wp_kses( "<div id='tainacan-admin-app' data-module='admin' data-options='$admin_options'></div>", $allowed_html );
	}

	function register_user_meta() {
		$args = array(
			//'sanitize_callback' => array(&$this, 'santize_user_tainacan_prefs'),
			//'auth_callback' => 'authorize_my_meta_key',
			'type'         => 'string',
			'description'  => 'Tainacan admin user preferences',
			'single'       => true,
			'show_in_rest' => true,
		);
		register_meta( 'user', 'tainacan_prefs', $args );
	}

	function register_user_setting() {
		register_setting(
			'tainacan_item_submission_recaptcha',
			'tnc_option_recaptch_site_key',
			'sanitize_text_field'
		);
	
		register_setting(
			'tainacan_item_submission_recaptcha',
			'tnc_option_recaptch_secret_key',
			'sanitize_text_field'
		);
	}

	function ajax_sample_permalink(){

		$id = sanitize_text_field($_POST['post_id']);
		$title = sanitize_text_field($_POST['new_title']);
		$name = sanitize_text_field($_POST['new_slug']);

		$post = get_post( $id );
		if ( ! $post )
			return array( '', '' );

		$ptype = get_post_type_object($post->post_type);

		// Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
		if ( in_array( $post->post_status, array( 'auto-draft', 'draft', 'pending', 'future' ) ) ) {
			$post->post_status = 'publish';
			$post->post_name = sanitize_title($post->post_name ? $post->post_name : $post->post_title, $post->ID);
		}

		// If the user wants to set a new name -- override the current one
		// Note: if empty name is supplied -- use the title instead, see #6072
		if ( !is_null($name) )
			$post->post_name = sanitize_title($name ? $name : $title, $post->ID);

		$post->post_name = wp_unique_post_slug($post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent);
		if ( $post->post_type === \Tainacan\Entities\Taxonomy::$post_type ) {
			$post_name = $post->post_name;
			$tax = \get_taxonomies(array('name' => $post_name));
			$suffix = 2;
			while ( !empty($tax) ) {
				$post_name = _truncate_post_slug( $post_name, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$tax = \get_taxonomies(array('name' => $post_name));
				$suffix++;
			};
			$post->post_name = $post_name;
		}

		$post->filter = 'sample';

		$permalink = get_permalink($post, true);

		// Replace custom post_type Token with generic pagename token for ease of use.
		$permalink = str_replace("%$post->post_type%", '%pagename%', $permalink);

		// Handle page hierarchy
		if ( $ptype->hierarchical ) {
			$uri = get_page_uri($post);
			if ( $uri ) {
				$uri = untrailingslashit($uri);
				$uri = strrev( stristr( strrev( $uri ), '/' ) );
				$uri = untrailingslashit($uri);
			}

			/** This filter is documented in wp-admin/edit-tag-form.php */
			$uri = apply_filters( 'editable_slug', $uri, $post );
			if ( !empty($uri) )
				$uri .= '/';
			$permalink = str_replace('%pagename%', "{$uri}%pagename%", $permalink);
		}

		/** This filter is documented in wp-admin/edit-tag-form.php */
		$permalink = array( 'permalink' => $permalink, 'slug' => apply_filters( 'editable_slug', $post->post_name, $post ) );

		echo json_encode($permalink);

		wp_die();
	}

	public function systemcheck_page() {
		require_once('system-check/class-tainacan-system-check.php');
		$check = new System_Check();
		$check->admin_page();
	}

	public function item_submission() {
		require_once('item-submission/class-tainacan-item-submission.php');
		$submission = new Item_Submission();
		$submission->admin_page();
	}

	public function mobile_app() {
		require_once('mobile-app/class-tainacan-mobile-app.php');
		$Mobile_app = new Mobile_App();
		$Mobile_app->admin_page();
	}

}

