<?php

namespace Tainacan;

class Mobile_App {

	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
	}

	public function add_admin_menu() {
		$mobile_app_page_suffix = add_submenu_page(
			'tainacan-no-show-menu', // Mobile app page is not listed in the menu
			__('Mobile App', 'tainacan'),
			__('Mobile App', 'tainacan'),
			'manage_tainacan',
			'tainacan_mobile_app',
			array( &$this, 'mobile_app_page' )
		);
		add_action( 'load-' . $mobile_app_page_suffix, array( &$this, 'load_mobile_app_page' ) );
	}

	function load_mobile_app_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_mobile_app_css' ), 90 );
	}

	function add_mobile_app_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-mobile-app-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-mobile-app.css', [], TAINACAN_VERSION );
	}

	public function mobile_app_page() {
		$this->admin_page();
	}

	public function admin_page() {
		include('admin-page.php');
	}
}