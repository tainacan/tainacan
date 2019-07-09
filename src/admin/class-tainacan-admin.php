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

	}

	function add_admin_menu() {
		$dummyEntity = new \Tainacan\Entities\Taxonomy();
		// a capability everybody bu subscriber have.
		// Maybe we will create a specific cap to view_admin later
		$entity_cap = $dummyEntity->get_capabilities()->edit_posts;
		$page_suffix = add_menu_page(
			__( 'Tainacan', 'tainacan' ),
			__( 'Tainacan', 'tainacan' ),
			$entity_cap,
			$this->menu_slug,
			array( &$this, 'admin_page' ),
			plugin_dir_url( __FILE__ ) . 'images/tainacan_logo_symbol.svg'
		);
		
		add_submenu_page( 
			$this->menu_slug, 
			__('System check', 'tainacan'), 
			__('System check', 'tainacan'), 
			'manage_options', 
			'tainacan_systemcheck', 
			array( &$this, 'systemcheck_page' ) 
		);

		add_action( 'load-' . $page_suffix, array( &$this, 'load_admin_page' ) );
	}

	function load_admin_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_js' ), 90 );
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
		
		// wp_enqueue_style( 'style', $TAINACAN_BASE_URL . '/assets/css/fonts/materialdesignicons.css' );
		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/css/fonts/tainacanicons.css', [], TAINACAN_VERSION );
		wp_enqueue_style( 'roboto-fonts', 'https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,700,700i', [], TAINACAN_VERSION );
		wp_enqueue_script('underscore');
	}
	
	function add_admin_css() {
		global $TAINACAN_BASE_URL;
		
		wp_enqueue_style( 'tainacan-admin-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-admin.css', [], TAINACAN_VERSION );
		
//		$undesired_wp_styles = [
//			'admin-menu',
//			'admin-bar',
//			'code-editor',
//			'color-picker',
//			'customize-controls',
//			'customize-nav-menus',
//			'customize-widgets',
//			'dashboard',
//			'dashicons',
//			'deprecated-media',
//			'edit',
//			'wp-pointer',
//			'farbtastic',
//			'forms',
//			'common',
//			'install',
//			'wp-auth-check',
//			'site-icon',
//			'buttons',
//			'l10n',
//			'list-tables',
//			'login',
//			'media',
//			'nav-menus',
//			'revisions',
//			'themes',
//			'widgets',
//			'wp-admin'
//		];
//
//		wp_dequeue_style( $undesired_wp_styles );
//		wp_deregister_style( $undesired_wp_styles );
		
	}
	
	function add_admin_js() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_script( 'tainacan-user-admin', $TAINACAN_BASE_URL . '/assets/user_admin-components.js', ['underscore', 'media-editor', 'media-views', 'customize-controls'], TAINACAN_VERSION, true );
		 
		$settings = $this->get_admin_js_localization_params();

		wp_localize_script( 'tainacan-user-admin', 'tainacan_plugin', $settings );
		wp_enqueue_media();
		wp_enqueue_script('underscore');
		wp_enqueue_script('jcrop');
		wp_enqueue_script( 'customize-controls' );
		
		do_action('tainacan-enqueue-admin-scripts');
		
	}
	
	/**
	 * Also used by DevInterface
	 */
	function get_admin_js_localization_params() {
		global $TAINACAN_BASE_URL, $TAINACAN_API_MAX_ITEMS_PER_PAGE;
		
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Metadata      = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Filters     = \Tainacan\Repositories\Filters::get_instance();
		$Tainacan_Items       = \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Taxonomies  = \Tainacan\Repositories\Taxonomies::get_instance();
		
		$tainacan_admin_i18n = require( 'tainacan-admin-i18n.php' );

		$entities_labels = [
			'collections' => $Tainacan_Collections->get_cpt_labels(),
			'metadata'      => $Tainacan_Metadata->get_cpt_labels(),
			'filters'     => $Tainacan_Filters->get_cpt_labels(),
			'items'       => $Tainacan_Items->get_cpt_labels(),
			'taxonomies'  => $Tainacan_Taxonomies->get_cpt_labels(),
		];

		$tainacan_admin_i18n['entities_labels'] = $entities_labels;

		$components = ( has_filter( 'tainacan_register_web_components' ) ) ? apply_filters( 'tainacan_register_web_components' ) : [];

		$cur_user  = wp_get_current_user();
		$user_caps = array();
		$prefs     = array();
		if ( $cur_user instanceof \WP_User ) {
			if ( is_array( $cur_user->allcaps ) ) {
				foreach ( $cur_user->allcaps as $cap => $bool ) {
					if ( $bool === true ) {
						$user_caps[] = $cap;
					}
				}
			}
			$prefs = get_user_meta( $cur_user->ID, 'tainacan_prefs', true );
		}

		$settings = [
			'tainacan_api_url'         	=> esc_url_raw( rest_url() ) . 'tainacan/v2',
			'wp_api_url'            	=> esc_url_raw( rest_url() ) . 'wp/v2/',
			'wp_ajax_url'            	=> admin_url( 'admin-ajax.php' ),
			'nonce'                  	=> wp_create_nonce( 'wp_rest' ),
			'components'             	=> $components,
			'i18n'                   	=> $tainacan_admin_i18n,
			'user_caps'              	=> $user_caps,
			'user_prefs'             	=> $prefs,
			'base_url'               	=> $TAINACAN_BASE_URL,
			'admin_url'              	=> admin_url(),
			'theme_items_list_url' 		=> esc_url_raw( get_site_url() ) . '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug(),
			'theme_collection_list_url' => get_post_type_archive_link( 'tainacan-collection' ),
			'custom_header_support'  	=> get_theme_support('custom-header'),
			'registered_view_modes'  	=> \Tainacan\Theme_Helper::get_instance()->get_registered_view_modes(),
		    'exposer_mapper_param'   => \Tainacan\Mappers_Handler::MAPPER_PARAM,
			'exposer_type_param'     	=> \Tainacan\Exposers_Handler::TYPE_PARAM,
			'repository_name'	 		=> get_bloginfo('name'),
			'api_max_items_per_page'    => $TAINACAN_API_MAX_ITEMS_PER_PAGE,
			'wp_elasticpress'    		=> \Tainacan\Elastic_Press::get_instance()->is_active(),
		];

		$maps = [
			'collections' => $Tainacan_Collections->get_map(),
			'metadata'      => $Tainacan_Metadata->get_map(),
			'filters'     => $Tainacan_Filters->get_map(),
			'items'       => $Tainacan_Items->get_map(),
			'taxonomies'  => $Tainacan_Taxonomies->get_map(),
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
		
		// add an alternative to enable select all items in all pages while we temporarly disable bulk edit for all (see #199)
		$settings['enable_select_all_items_pages'] = defined('TAINACAN_ENABLE_SELECT_ALL_ITEMS_PAGES') ? TAINACAN_ENABLE_SELECT_ALL_ITEMS_PAGES : false; 
		
		return $settings;
		
	}

	function admin_body_class( $classes ) {
		global $pagenow;
		if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == $this->menu_slug ) {
			$classes .= ' tainacan-admin-page';
		}

		return $classes;
	}

	function admin_page() {
		global $TAINACAN_BASE_URL;

		// TODO move it to a separate file and start the Vue project
		echo "<div id='tainacan-admin-app'></div>";
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

	function ajax_sample_permalink(){

		$id = $_POST['post_id'];
		$title = $_POST['new_title'];
		$name = $_POST['new_slug'];

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

}

