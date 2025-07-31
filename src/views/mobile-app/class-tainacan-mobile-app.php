<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Mobile_App extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	protected function get_page_slug() : string {
        return 'tainacan_mobile_app';
    }
	public function add_admin_menu() {
		$mobile_app_page_suffix = add_submenu_page(
			'tainacan-no-show-menu', // Mobile app page is not listed in the menu
			__('Mobile App', 'tainacan'),
			__('Mobile App', 'tainacan'),
			'manage_tainacan',
			$this->get_page_slug(),
			array( &$this, 'render_page' )
		);
		add_action( 'load-' . $mobile_app_page_suffix, array( &$this, 'load_page' ) );
	}

	function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_style( 'tainacan-mobile-app-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-mobile-app.css', [], TAINACAN_VERSION );
	}

	public function render_page() {
		$this->render_page_content();
	}

	public function render_page_content() {
		require_once('page.php');
	}
}