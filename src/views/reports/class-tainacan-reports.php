<?php

namespace Tainacan;

class Reports {

    private $other_links_slug = 'tainacan_other_links';

    public function __construct() {
        add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
    }

    public function add_admin_menu() {
        $reports_page_suffix = add_submenu_page(
			$this->other_links_slug,
			__('Reports', 'tainacan'),
			__('Reports', 'tainacan'),
			'manage_tainacan',
			'tainacan_reports',
			array( &$this, 'reports_page' )
		);
		add_action( 'load-' . $reports_page_suffix, array( &$this, 'load_reports_page' ) );
    }

    function load_reports_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_reports_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_reports_js' ), 90 );
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

		$settings = \Tainacan\Views::get_instance()->get_admin_js_localization_params();
		wp_localize_script( 'tainacan-pages-common-scripts', 'tainacan_plugin', $settings );
		wp_enqueue_script('underscore');
		wp_enqueue_script('wp-i18n');

		do_action('tainacan-enqueue-reports-scripts');
	}

	function reports_page() {
		\Tainacan\Views::get_instance()->the_admin_navigation_menu();
		echo "<div id='tainacan-reports-app'  data-module='reports'></div>";
	}
}