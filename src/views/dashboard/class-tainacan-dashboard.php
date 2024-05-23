<?php

namespace Tainacan;

class Dashboard {

	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
	}


	function add_admin_menu() {

		// Main Page, Dashboard
		$dashboard_page_suffix = add_menu_page(
			__( 'Tainacan', 'tainacan' ),
			__( 'Tainacan', 'tainacan' ),
			'read',
			'tainacan_dashboard',
			array( &$this, 'dashboard_page' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iNC40bW0iIGhlaWdodD0iNC4zbW0iIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDE3LjUxIDE3LjU1NCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CjxtZXRhZGF0YT4KPHJkZjpSREY+CjxjYzpXb3JrIHJkZjphYm91dD0iIj4KPGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+CjxkYzp0eXBlIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiLz4KPC9jYzpXb3JrPgo8L3JkZjpSREY+CjwvbWV0YWRhdGE+CjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKC03NS45MTEgLTExOC44OSkiPgo8ZyB0cmFuc2Zvcm09Im1hdHJpeCguMzUyNzggMCAwIC0uMzUyNzggOTIuMjg3IDEzMy45NCkiIGZpbGw9IiNmZmYiPgo8cGF0aCBkPSJtMCAwYy0xZS0zIC0xZS0zIC0yZS0zIC0yZS0zIC0yZS0zIC0zZS0zIC0xZS0zIC0xZS0zIC0xZS0zIC0yZS0zIC0yZS0zIC00ZS0zIC0yLjQwNi01LjEyNC04Ljk4NC02LjAzOC0xOC4xNjgtMS43MzggMC41NjIgMi43MDcgMS4xNzMgNi4zMTggMS4yNzEgOC44NjUgMWUtMyA2ZS0zIDAgMC4wMSAwIDAuMDE2IDIuODE0IDEuODE5IDUuNzI0IDQuMDk2IDguNjM4IDYuOTQzLTAuMTI2IDAuMDY5LTAuMTA0IDAuMDU4IDAgMCA3LjY3Mi00LjE2OCAxMC40ODYtOS4zNDYgOC4yNjMtMTQuMDc5bS0xMC44MTMgMTUuMzQ2cy0xZS0zIDAtMWUtMyAxZS0zYy0yLjA2NS0xLjk5Mi00LjEwNi0zLjY3My02LjA4NS01LjA5Mi0wLjA3OSAyLjM3Mi0wLjM1IDQuOTY5LTAuOTExIDcuNzU5IDIuMjE0LTAuNjczIDQuNTUxLTEuNTQ3IDYuOTk2LTIuNjY3IDAuMDUxIDAuMDQ5IDAuMDM2IDAuMDM1IDAgMCAwLTFlLTMgMWUtMyAtMWUtMyAxZS0zIC0xZS0zbS0xMS4xNCAxNS4xMWM2LjcxIDYuMjA0IDEyLjczMSA3LjI0MiAxNi41MjYgMy40NTkgMWUtMyAwIDJlLTMgLTFlLTMgM2UtMyAtMmUtM3MxZS0zIC0yZS0zIDJlLTMgLTNlLTNjMy44MDktMy43OTUgMi43NzMtOS44NDctMy40NjMtMTYuNjA0LTAuMDMtMC4wMzMtMC4wMzctMC4wMzkgMCAwLTAuMDM4IDAuMDE4LTMuNjM2IDEuODg4LTkuNTc3IDMuNTMzLTAuNDQ2IDIuNDY1LTMuMDY3IDguOTk0LTMuNDkxIDkuNjE3bTIuNjEyLTIxLjg0NmMtMy4yMzktMi4wNC02LjI1OS0zLjM5NS04Ljg3Ni00LjI5Ny0wLjkzNiAwLjcyMS0xLjgwNCAxLjQ0NS0yLjYxIDIuMTY1djFlLTNjLTEuMzQxIDEuMzEtMi43MiAyLjgzNC00LjA5IDQuNjA0IDAuOTA1IDIuNjQzIDIuMjcgNS42OTEgNC4zMjcgOC45NjIgMi44MjYgMC4yMjEgMTAuMDA3LTEuMTM5IDEwLjAyNi0xLjI3OCAwLjIwOS0wLjAzMSAxLjM2Ni03LjI3OSAxLjIyMy0xMC4xNTdtLTEuMTQ3LTkuMjA3Yy0wLjMwNC0wLjAyNS00LjA1IDIuMDcxLTUuMzUgMy4xODl2MWUtM2MyLjAxIDAuNzYxIDYuMzczIDIuOTkyIDYuMzczIDIuOTkyczdlLTMgNGUtMyAwLjAxIDZlLTNjMC01ZS0zIC0wLjYwNy00LjIxMS0xLjAzMy02LjE4OG0tMTYuMjA1IDMuMTMzYzAuMDU5IDEuMDU2IDAuMjQgMi45MTkgMC44MzYgNS4zNTIgMC42MzktMC43NzUgMy43MDQtMy44MjYgNC40OTYtNC41MDUtMi40MTMtMC41OTctNC4yNjctMC43ODItNS4zMzItMC44NDdtLTMuMjEyIDE2LjM4M2MyLjAyOCAwLjQzNSA0LjU2OCAwLjkwOCA2LjMwOCAxLjAzMiAwIDFlLTMgMWUtMyAyZS0zIDFlLTMgM2UtMyAwLTFlLTMgLTFlLTMgLTJlLTMgLTFlLTMgLTNlLTMgLTEuMjkyLTIuMjQ2LTIuMjk4LTQuNDEtMy4wODEtNi40NDEtMS4xIDEuNjE2LTMuMjIxIDUuMzk4LTMuMjI3IDUuNDA5bTAuNTkgMjAuNTg0IDJlLTMgMmUtMyAzZS0zIDFlLTNjNC43ODggMi4yMzQgMTAuMDE1LTAuNjc0IDE0LjE4NS04LjUzMy0yLjgyOC0yLjg2Ny01LjEtNS43My02LjkyMS04LjUwMSAwLTFlLTMgMC0yZS0zIC0xZS0zIC0yZS0zIDFlLTMgMCAxZS0zIDFlLTMgMWUtMyAyZS0zIC0yLjU4MS0wLjA3OC01LjgyNy0wLjc0My04Ljk5Mi0xLjI2NS00LjQ0MSA5LjM5Ni0zLjQxNyAxNS44OTkgMS43MjMgMTguMjk2bTE4LjAxNy0xNy45OTNjLTIuNzU1IDAuNTY3LTUuMzIyIDAuODUzLTcuNjcyIDAuOTQ2IDEuNDA5IDEuOTcgMy4wNzUgNCA1LjA0NiA2LjA1My00ZS0zIDhlLTMgMS45NjYtNC43ODUgMi42MjYtNi45OTltMTQuOTA5LTUuNDkzYzYuOTY2IDcuODI1IDcuNjYyIDE0Ljc1OCAyLjg1NSAxOS41OTJsLTZlLTMgNmUtM2MtNC44MTIgNC44MzgtMTEuOTY5IDQuMDkyLTE5LjYyOS0yLjc2NnYtMWUtMyAxZS0zYy00Ljc2NSA4LjYwOC0xMS4wNiAxMS41ODItMTcuMTA4IDguNzgxbC00ZS0zIC0yZS0zYy0xZS0zIDAtM2UtMyAtMWUtMyAtNGUtMyAtMmUtMyAtOC4xNTItMy40MTgtOC45OTYtMTQuODI2IDIuNDgzLTMxLjQxNmgxZS0zYy0xZS0zIDAtMWUtMyAtMWUtMyAtMWUtMyAtMWUtMyAtMS40MDMtNC43NDctMS41OTItOC40NDgtMS41OTYtMTAuMjY1IDEuNzk0LTVlLTMgNS41MDUgMC4xNjUgMTAuMjggMS41NTEgMTguNzA5LTEyLjcyNCAyOC40MjgtOS4zNDMgMzEuMzI1LTIuNTkyIDAgMWUtMyAxZS0zIDJlLTMgMmUtMyA0ZS0zIDAgMWUtMyAxZS0zIDNlLTMgMmUtMyA0ZS0zIDIuODIyIDYuMDExLTAuMzY2IDEyLjY3OC04LjYgMTcuMTA2IiBmaWxsPSIjZmZmIi8+CjwvZz4KPC9nPgo8L3N2Zz4K'
		);
		add_action( 'load-' . $dashboard_page_suffix, array( &$this, 'load_dashboard_page' ) );

	}

	function load_dashboard_page() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_dashboard_css' ), 90 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_dashboard_js' ), 90 );
	}

	function add_dashboard_css() {
		global $TAINACAN_BASE_URL;

		wp_admin_css( 'dashboard' );
		//wp_enqueue_style( 'tainacan-dashboard-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-dashboard.css', [], TAINACAN_VERSION );
	}

	function add_dashboard_js() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_script( 'dashboard' );
	}


	public function dashboard_page() {
		\Tainacan\Views::get_instance()->the_admin_navigation_menu();
		$this->admin_page();
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
