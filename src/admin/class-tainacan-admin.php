<?php

namespace Tainacan;



class Admin {

    private $menu_slug = 'tainacan_admin';
    
    public function __construct() {

        add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
        add_filter( 'admin_body_class', array(&$this, 'admin_body_class') );
		
		add_action( 'init', array(&$this, 'register_user_meta') );

    }

    function add_admin_menu() {
        $page_suffix = add_menu_page(
        	__('Tainacan', 'tainacan'),
	        __('Tainacan', 'tainacan'),
	        'edit_posts',
	        $this->menu_slug,
	        array(&$this, 'admin_page'),
	        plugin_dir_url(__FILE__) . 'images/tainacan_logo_symbol.svg'
        );

        add_action( 'load-' . $page_suffix, array(&$this, 'load_admin_page'));
    }
    
    function load_admin_page() {
        add_action( 'admin_enqueue_scripts', array(&$this, 'add_admin_css'), 90 );
    }
    
    function login_styles_reset( $style ) {
        if ( strpos( $style, 'wp-admin-css') !== FALSE ) {
            $style = NULL;
        }
        return $style;
    }

    function add_admin_css() {
        global $TAINACAN_BASE_URL; 

        wp_enqueue_style('tainacan-admin-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-admin.css' );

        $undesired_wp_styles = [ 'admin-menu', 'admin-bar', 'code-editor', 'color-picker',
            'customize-controls', 'customize-nav-menus', 'customize-widgets', 'dashboard', 
            'dashicons', 'deprecated-media', 'edit', 'wp-pointer', 'farbtastic', 'forms', 'common',
            'install', 'wp-auth-check', 'site-icon', 'buttons', 'l10n', 'list-tables','login',
            'media', 'nav-menus', 'revisions', 'themes', 'widgets', 'wp-admin'];
        
        wp_dequeue_style($undesired_wp_styles);
        wp_deregister_style($undesired_wp_styles);
        
    }
    
    function admin_body_class($classes) {
        global $pagenow;
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == $this->menu_slug) {
            $classes .= ' tainacan-admin-page';
        }
        return $classes;
    }
    
    function admin_page() {
	    global $TAINACAN_BASE_URL, $Tainacan_Collections, $Tainacan_Fields, $Tainacan_Filters, $Tainacan_Items, $Tainacan_Taxonomies;

	    // TODO move it to a separate file and start the Vue project
        echo "<div id='tainacan-admin-app'></div>";
        //wp_enqueue_script( 'tainacan-dev-admin', $TAINACAN_BASE_URL . '/assets/dev_admin-components.js', [] , null, true);
	    wp_enqueue_script( 'tainacan-user-admin', $TAINACAN_BASE_URL . '/assets/user_admin-components.js', [] , null, true);
        
        $tainacan_admin_i18n = require('tainacan-admin-i18n.php');
		
		$entities_labels = [
			'collections' => $Tainacan_Collections->get_cpt_labels(),
			'fields' => $Tainacan_Fields->get_cpt_labels(),
			'filters' => $Tainacan_Filters->get_cpt_labels(),
			'items' => $Tainacan_Items->get_cpt_labels(),
			'taxonomies' => $Tainacan_Taxonomies->get_cpt_labels(),
		];
		
		$tainacan_admin_i18n['entities_labels'] = $entities_labels;
		
        $components = ( has_filter( 'tainacan_register_web_components' ) ) ? apply_filters('tainacan_register_web_components') : [];

        $cur_user = wp_get_current_user();
        $user_caps = array();
		$prefs = array();
        if ($cur_user instanceof \WP_User) {
            if (is_array($cur_user->allcaps)) {
                foreach ($cur_user->allcaps as $cap => $bool)
                    if ($bool === true)
                        $user_caps[] = $cap;
            }
			$prefs = get_user_meta($cur_user->ID, 'tainacan_prefs', true);
        }
        
        $settings = [
            'root' => esc_url_raw( rest_url() ).'tainacan/v2',
            'root_wp_api' => esc_url_raw( rest_url() ).'wp/v2/',
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'components' => $components,
            'i18n' => $tainacan_admin_i18n,
            'user_caps' => $user_caps,
            'user_prefs' => $prefs,
            'base_url' => $TAINACAN_BASE_URL
		];
        
        

        wp_localize_script( 'tainacan-user-admin', 'tainacan_plugin', $settings );
		
    }
	
	function register_user_meta() {
		$args = array(
			//'sanitize_callback' => 'sanitize_my_meta_key',
			//'auth_callback' => 'authorize_my_meta_key',
			'type' => 'array',
			'description' => 'Tainacan admin user preferences',
			'single' => true,
			'show_in_rest' => true,
		);
		register_meta( 'user', 'tainacan_prefs', $args );
	}

}

