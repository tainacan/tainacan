<?php

namespace Tainacan;

class Dashboard {

	public function __construct() {
		$this->page_init();
	}

	public function page_init() {

	}

	/**
	 * Registers the dashboard widgets to be displayed
	 */
	function tainacan_register_widgets() {
		wp_add_dashboard_widget( 'tainacan_widget_1', __( 'Custom Widget 1' ), [$this, 'tainacan_widget_1'], null, null, 'normal' );
		wp_add_dashboard_widget( 'tainacan_widget_2', __( 'Custom Widget 2' ), [$this, 'tainacan_widget_2'], null, null, 'side' );
		wp_add_dashboard_widget( 'tainacan_widget_3', __( 'Custom Widget 3' ), [$this, 'tainacan_widget_3'], null, null, 'side' );
	}


	/**
	 * Creates the display code for the custom widget
	 */
	function tainacan_widget_1() {
		?>

		<p>Custom widget 1 info here</p>

		<?php
	}

	/**
	 * Creates the display code for the custom widget
	 */
	function tainacan_widget_2() {
		?>

		<p>Custom widget 2 info here</p>

		<?php
	}

	/**
	 * Creates the display code for the custom widget
	 */
	function tainacan_widget_3() {
		?>

		<p>Custom widget 3 info here</p>

		<?php
	}

	/**
	 * This displays the template for the dashboard for my custom plugin
	 */
	function admin_page() {
		// Load the admin dashboard code from core
		require_once ABSPATH . 'wp-admin/includes/dashboard.php';

		// Register Widgets TO Be Displayed
		$this->tainacan_register_widgets();

		include('admin-page.php');
	}

}
