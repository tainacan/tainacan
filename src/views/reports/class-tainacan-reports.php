<?php

namespace Tainacan;

class Reports extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

    public function add_admin_menu() {
        $reports_page_suffix = add_submenu_page(
			$this->other_links_slug,
			__('Reports', 'tainacan'),
			__('Reports', 'tainacan'),
			'manage_tainacan',
			'tainacan_reports',
			array( &$this, 'render_page' )
		);
		add_action( 'load-' . $reports_page_suffix, array( &$this, 'load_page' ) );
    }

    function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 'tainacan-fonts', $TAINACAN_BASE_URL . '/assets/css/tainacanicons.css', [], TAINACAN_VERSION );
		wp_enqueue_style( 'tainacan-reports-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-reports.css', [], TAINACAN_VERSION );
	}

	function admin_enqueue_js() {

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

	public function render_page_content() {
		require_once('page.php');
	}
}