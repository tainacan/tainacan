<?php

namespace Tainacan;



class Admin {

    private $menu_slug = 'tainacan_admin';
    
    public function __construct() {
        
        add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
        add_filter( 'admin_body_class', array(&$this, 'admin_body_class') );
	    add_action('admin_enqueue_scripts', array(&$this, 'add_user_admin_js'));


    }
    
    function add_admin_menu() {
        $page_suffix = add_menu_page( __('Tainacan', 'tainacan'), __('Tainacan', 'tainacan'), 'edit_posts', $this->menu_slug, array(&$this, 'admin_page') );
        add_action( 'load-' . $page_suffix, array(&$this, 'load_admin_page'));
    }
    
    function load_admin_page() {
        add_action( 'admin_enqueue_scripts', array(&$this, 'add_admin_css') );
    }
    
    function add_admin_css() {
        global $TAINACAN_BASE_URL;
        wp_enqueue_style('tainacan-admin-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-admin.css' );
    }
    
    function admin_body_class($classes) {
        global $pagenow;
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == $this->menu_slug) {
            $classes .= ' tainacan-admin-page';
        }
        return $classes;
    }
    
    function admin_page() {
	    global $TAINACAN_BASE_URL;

	    // TODO move it to a separate file and start the Vue project
        echo "<div id='tainacan-admin-app'></div>";
        //wp_enqueue_script( 'tainacan-dev-admin', $TAINACAN_BASE_URL . '/assets/dev_admin-components.js', [] , null, true);
	    wp_enqueue_script( 'tainacan-user-admin', $TAINACAN_BASE_URL . '/assets/user_admin-components.js', [] , null, true);
    }


	function add_user_admin_js() {
		global $TAINACAN_BASE_URL;
		$components = ( has_filter( 'tainacan_register_web_components' ) ) ? apply_filters('tainacan_register_web_components') : [];

		wp_enqueue_script('wp-settings',$TAINACAN_BASE_URL . '/js/wp-settings.js');

		$settings = [
			'root' => esc_url_raw( rest_url() ).'tainacan/v2',
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'components' => $components
		];

		wp_localize_script( 'wp-settings', 'wp_settings', $settings );
	}

}

