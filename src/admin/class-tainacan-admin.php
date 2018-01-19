<?php

namespace Tainacan;



class Admin {

    private $menu_slug = 'tainacan_admin';
    
    public function __construct() {
        
        add_action( 'admin_menu', array(&$this, 'add_admin_menu') );
        add_filter( 'admin_body_class', array(&$this, 'admin_body_class') );
        

    
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
        // TODO move it to a separate file and start the Vue project
        echo "<div id='tainacan-admin-app'>Here we go!</div>";
    }

}

