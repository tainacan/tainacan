<?php

namespace Tainacan;

class Roles_Editor {

    private $other_links_slug = 'tainacan_other_links';

    public function __construct() {
        add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
    }

    public function add_admin_menu() {
        $roles_page_suffix = add_submenu_page(
			$this->other_links_slug,
			__('User Roles', 'tainacan'),
			__('User Roles', 'tainacan'),
			'tnc_rep_edit_users',
			'tainacan_roles',
			array( &$this, 'roles_page' )
		);
		add_action( 'load-' . $roles_page_suffix, array( &$this, 'load_roles_page' ) );
    }

    function load_roles_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_roles_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_roles_js' ), 90 );
	}

    function add_roles_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-roles-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-roles.css', [], TAINACAN_VERSION );
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

		$settings = \Tainacan\Views::get_instance()->get_admin_js_localization_params();
		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_plugin', $settings );
		wp_enqueue_script('underscore');
		wp_enqueue_script('wp-i18n');

		do_action('tainacan-enqueue-roles-scripts');
	}

    function roles_page() {
		\Tainacan\Views::get_instance()->the_admin_navigation_menu();
		echo "<div id='tainacan-roles-app' data-module='roles'></div>";
	}
}