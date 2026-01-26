<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Dashboard extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	protected function get_page_slug() : string {
        return 'tainacan_dashboard';
    }
	private $vue_component_page_slug = 'tainacan_admin';
	private $tainacan_dashboard_cards = [];
	private $disabled_cards = [];

	private $default_news_feed_options = array(
		'feed_url' => 'https://tainacan.org/feed/',
		'title' => 'tainacan.org',
		'view_all_link' => 'https://tainacan.org/blog/',
		'posts_per_feed' => 3,
	);

	public function init() {
		parent::init();
		add_action( 'wp_ajax_tainacan_fetch_dashboard_news', array($this, 'tainacan_ajax_fetch_dashboard_news') );
	}

	function add_admin_menu() {

		// Main Page, Dashboard
		$dashboard_page_suffix = add_menu_page(
			__( 'Tainacan', 'tainacan' ),
			__( 'Tainacan', 'tainacan' ),
			'read',
			$this->get_page_slug(),
			array( &$this, 'render_page' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4KCjxzdmcKICAgdmVyc2lvbj0iMS4xIgogICBpZD0iQ2FtYWRhXzEiCiAgIHg9IjBweCIKICAgeT0iMHB4IgogICB2aWV3Qm94PSIwIDAgMjQuNTgyOTUzIDI0LjU5Nzc2NyIKICAgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIKICAgd2lkdGg9IjI0LjU4Mjk1MiIKICAgaGVpZ2h0PSIyNC41OTc3NjciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnMKICAgaWQ9ImRlZnMxMSI+CgoJCgkKCgoJCQoJCQoJCQoJCQoJCQoJCQoJCQoJCQoJCgkJCQoJCQkKCQk8L2RlZnM+CjxzdHlsZQogICB0eXBlPSJ0ZXh0L2NzcyIKICAgaWQ9InN0eWxlMSI+Cgkuc3Qwe2ZpbGw6I0ZGRkZGRjt9Cjwvc3R5bGU+CjxwYXRoCiAgIGlkPSJwYXRoMSIKICAgc3R5bGU9ImZpbGw6I2ZmZmZmZjtmaWxsLW9wYWNpdHk6MTtzdHJva2Utd2lkdGg6MSIKICAgY2xhc3M9InN0MCIKICAgZD0iTSA1LjQzNTU0NjksLTAuMjk0OTIxODggQyAyLjcyNzI3OSwtMC4zMjk3ODgxNCAwLjEyNzgwMjY1LDEuNzI2NTA4NiAtMC4yMDExNzE4OCw0LjQ3ODUxNTYgLTAuNjYwNzI0ODksNy41NzgxNjMyIDAuNzM3MjUzNDQsMTAuNDc4MTQyIDEuOTkwMjM0NCwxMy4wOTk2MDkgYSAwLjI5NDk5MDUsMC4yOTQ5OTA1IDAgMCAwIDAuMDAxOTUsMC4wMDc4IGMgMC41Mzc5NzgyLDEuMDUzODYxIDEuMTUwMTAwMywxLjgwMTQ1MSAxLjU4MDA3ODEsMi43ODkwNjIgLTAuMzk0OTQ4OCwxLjU0OTU3NiAtMC42MDE4MzM4LDMuMjY0MjI2IC0wLjU3MDMxMjUsNC44NTc0MjIgYSAwLjI5NDk5MDUsMC4yOTQ5OTA1IDAgMCAwIDAuMjg5MDYyNSwwLjI4OTA2MyBjIDEuNzIwMDc4MSwwLjAzNTE4IDMuNDMzODA2MiwtMC4yMTYxMzIgNS4wODU5Mzc1LC0wLjY2Nzk2OSAyLjA5MTA5MjksMS41OTY0MzIgNC41MTg1MjM5LDIuNjY2MDY1IDYuOTc4NTE1OSwzLjQ3MDcwMyBhIDAuMjk0OTkwNSwwLjI5NDk5MDUgMCAwIDAgMC4wMDc4LDAuMDAyIGMgMi4zMzc4MTIsMC43MDA5MTIgNS4yNzQ0MDksMC43NzQ5ODggNy4xOTUzMTMsLTEuMTE1MjM0IDEuNzAyODMxLC0xLjY2Mjc2NiAyLjI4MjQ5NCwtNC4zNjk1OTMgMS4xNDQ1MzEsLTYuNTI1MzkxIGwgLTAuMDAyLC0wLjAwMzkgYyAtMC43Njc5OTgsLTEuNTUzOTkxIC0yLjEzNDM1OSwtMi42ODYxNzcgLTMuNDk0MTQxLC0zLjU4Nzg5MSAxLjU3ODMwOCwtMS45MzE0NSAyLjg4NzQ1OCwtNC41MTkxNTExIDIuMTA3NDIyLC03LjEyMzA0NjUgYSAwLjI5NDk5MDUsMC4yOTQ5OTA1IDAgMCAwIDAsLTAuMDAxOTUgQyAyMS41MTI3MiwyLjgyNTU0MyAxOC42MjQxODIsMC45OTE4MTcxOCAxNS44NjEzMjgsMS42MTMyODEyIDE0LjE4NTIwMywxLjkxNTUyNjMgMTIuNzExNzY3LDIuODUxMDIxOCAxMS40NDcyNjYsMy44NTE1NjI1IDEwLjE0NDQ4OCwxLjc2ODY2ODYgOC4xMTc2NDE5LC0wLjI1OTUwNjEyIDUuNDM1NTQ2OSwtMC4yOTQ5MjE4OCBaIE0gNC44MTQ0NTMxLDEuMzAwNzgxMiBjIDAuNDE0MzU5OCwtMC4wNTQzMzEgMC44MzU2MDMzLC0wLjA0Nzg3OCAxLjI0NDE0MDYsMC4wMjM0MzcgMC41NDQ4NjUyLDAuMDk1MTE0IDEuMDY1OTIyNywwLjMwMzM0NjggMS41MjM0Mzc1LDAuNjM2NzE4OCBhIDAuMjk0OTkwNSwwLjI5NDk5MDUgMCAwIDAgMC4wMTM2NzIsMC4wMDk3NyBjIDEuMTAzODA2NiwwLjcxODc5MzQgMS45NDU0NDY3LDEuNzkwMDM5NiAyLjY0NDUzMDksMi45Mzc1IEMgOC45MjUxODg0LDYuMTcyMTc4OCA3LjczNzY4NzYsNy41NzM4NDAzIDYuNzMwNDY4Nyw5LjA5OTYwOTQgNS4xOTc3ODE2LDkuMTc0ODYzNCAzLjY3Mzk1NzksOS40MzEzMDMzIDIuMTk3MjY1Niw5Ljg0OTYwOTQgMS4zMzQ1MTY3LDcuNzQ2MDg5OCAwLjcxMTEwNzQ3LDUuMjk3NzAzOSAxLjc3NzM0MzcsMy4yNzE0ODQ0IGMgMi44NDllLTQsLTUuNDE0ZS00IC0yLjg1MWUtNCwtMC4wMDE0MSAwLC0wLjAwMTk1IEMgMi4zOTI0MjY0LDIuMTY2MTQyNSAzLjU3MzcwNjMsMS40NjM0Njg0IDQuODE0NDUzMSwxLjMwMDc4MTIgWiBNIDE2LjYwMTU2MiwzLjEyNSBjIDAuNTQ2NzY0LC0wLjA0NzE1NCAxLjA5NzI0NSwxLjQwM2UtNCAxLjY1MDM5MSwwLjE3MTg3NSAwLjU2NDA3NCwwLjE4NjEzOSAxLjE5Mzc5OCwwLjU5MDA0MjEgMS41MjczNDQsMC45NzQ2MDk0IGEgMC4yOTQ5OTA1LDAuMjk0OTkwNSAwIDAgMCAwLjAyMzQ0LDAuMDIzNDM3IGMgMS40MTY4OSwxLjMwMTU0MDEgMS40NDkwNTEsMy41Mjg4NTYgMC41OTE3OTcsNS4xOTE0MDYyIC0wLjI1MzUyLDAuNDM1NTkxMiAtMC41MzIzNDQsMC45NjQyNDQ5IC0wLjgyMDMxMiwxLjQzNzQ5OTkgLTAuMjU3MzE0LDAuNDIyODc3IC0wLjUxOTg2LDAuNzQ3MTk0IC0wLjc0NjA5NCwwLjk1MzEyNSBDIDE3LjM0NDA0NSwxMS4xMzA5NzYgMTUuNjgxODU2LDEwLjQ0MTY5MiAxNC4wODM5ODQsOS45Nzg1MTU2IDEzLjYyNzgxOSw4LjM0MjYyMDEgMTIuOTg1OTQ4LDYuNzY1OTI2NiAxMi4yNDIxODgsNS4yNSAxMy40OTczMzEsNC4yMDUzNTMgMTUuMDMxNDE2LDMuMjYwNDExOCAxNi42MDE1NjIsMy4xMjUgWiBtIC01LjU5NzY1NiwzLjE0NjQ4NDQgYyAwLjEwNzUwNCwwLjE4OTU4ODUgMC4yMTMyMTksMC4zMzMxMzMxIDAuMzM5ODQ0LDAuNzIyNjU2MiBhIDAuMjk0OTkwNSwwLjI5NDk5MDUgMCAwIDAgMC4wMTE3MiwwLjAyOTI5NyBjIDAuMzY3NDUzLDAuODEwNjQxMyAwLjY2Nzk2NCwxLjY0OTgwMTQgMC45NDE0MDYsMi40OTQxNDA2IEMgMTEuMDg5MjMsOS4yNzI2MjEgOS44NjMxNTg0LDkuMTE0NjM4MiA4LjYyNjk1MzEsOS4wNjQ0NTMxIDkuMzU1MjkxNSw4LjA4MTU4NTMgMTAuMTQ5ODU1LDcuMTQ5MTYyNCAxMS4wMDM5MDYsNi4yNzE0ODQ0IFogTSA3LjU2NjQwNjIsMTAuNjE5MTQxIGMgMC44NzEyMDA1LC03Ljk3ZS00IDEuNzQwMzQzNiwwLjAxODU3IDIuNTkzNzQ5OCwwLjEyMzA0NyBhIDAuMjk0OTkwNSwwLjI5NDk5MDUgMCAwIDAgMC4wNTQ2OSwwLjAwMiBjIDAuMDAxNCwtOS41ZS01IDAuMTM0MjkzLDAuMDA2NiAwLjI4OTA2MiwwLjAyOTMgMC4xNTQ3NjksMC4wMjI2NiAwLjM0OTg3MSwwLjA1NTQ1IDAuNTU2NjQxLDAuMDkzNzUgMC4zOTI1OSwwLjA3MjcxIDAuODE4MDgyLDAuMTU4Njk2IDEuMTM0NzY2LDAuMjE4NzUgMC40MjQ3NDksMC4yMjkxMTYgMC41NzkwNzIsMC4zNDE1NjkgMC42NTYyNSwwLjQ5MjE4NyAwLjA3OTkzLDAuMTU1OTk4IDAuMTIyNDIxLDAuNDY5MDQzIDAuMjM4MjgxLDEuMDgyMDMxIGwgLTAuMDAyLC0wLjAwMiBjIDAuMjMyNjY0LDEuMjYyOTYxIDAuMzA5ODI2LDIuNTUwOTM5IDAuMjkyOTY4LDMuODQxNzk3IC0xLjQ3ODEzNSwwLjkxOTI1NiAtMy4wNTYyNjEsMS42NjM2MzEgLTQuNzEwOTM3MSwyLjE5MTQwNiBDIDguMjcyOTU0LDE4LjM3MjAyNCA3Ljg4OTU4MDQsMTguMDMxOTk4IDcuNTE1NjI1LDE3LjY3NTc4MSA2Ljc1MTc2ODUsMTYuOTQzNjE0IDYuMDM3MjE3MiwxNi4xNjMyNzUgNS4zNjEzMjgxLDE1LjM0OTYwOSBjIDAuMTE2NzY0NCwtMC4zMjgxOSAwLjIyODczNDIsLTAuNjg3MjU1IDAuMzMzOTg0NCwtMS4wMTc1NzggMC4xMjM4MjQsLTAuMzg4NjE2IDAuMjY3Mzg2NSwtMC43MzY3NjMgMC4zMjgxMjUsLTAuODE2NDA2IGEgMC4yOTQ5OTA1LDAuMjk0OTkwNSAwIDAgMCAwLjAzNTE1NiwtMC4wNjA1NSBDIDYuNDg5MDg3MywxMi40NzMwNTYgNi45OTczOTIxLDExLjUyNTM4NSA3LjU2NjQwNjIsMTAuNjE5MTQxIFogTSA1LjcwMzEyNSwxMC43NDIxODggQyA1LjE0MzA1OTgsMTEuNzQyODg3IDQuNjU5NDQ5LDEyLjc4MjkwNSA0LjI1NzgxMjUsMTMuODU3NDIyIDMuNzA2NDA4NiwxMy4wNDA4NDcgMy4yMjE2MjU2LDEyLjE4MjAyNSAyLjgzOTg0MzgsMTEuMjc5Mjk3IGMgMC4zMzQ5NTM0LC0wLjExNTYzMSAwLjcwMDI5NzcsLTAuMTcwMDM1IDEuMjgzMjAzMSwtMC4zMjgxMjUgMC4wMDM5NiwtMC4wMDExIDAuMDA1NzksLTguNjhlLTQgMC4wMDk3NywtMC4wMDIgMC41MTg4MDk1LC0wLjA5NTQyIDEuMDQ0NDUyOSwtMC4xNTA1MjcgMS41NzAzMTI1LC0wLjIwNzAzMSB6IG0gOC43MTY3OTcsMC45NTcwMzEgYyAxLjEzMDM2LDAuMzYyMzUyIDIuMjMzMzIsMC44MTM0MzggMy4zMTI1LDEuMzI0MjE5IC0wLjg5NTg1NCwwLjg3MzM3OCAtMS44NDAwNTcsMS42OTAwNTIgLTIuODQ1NzAzLDIuNDE5OTIxIC0wLjA0NzE0LC0xLjI2MDg3OSAtMC4yMDc2MTIsLTIuNTEyNjA1IC0wLjQ2Njc5NywtMy43NDQxNCB6IG0gNC42NzM4MjgsMi4xMzA4NTkgYyAwLjg5NDM0OCwwLjU0NzQxMSAxLjc4ODMwMywxLjIyNTM2OSAyLjQ1MzEyNSwyLjA0MTAxNiAwLjcxMDA4OCwwLjg3MTE4MiAxLjE2MjI5NSwxLjg3MTUzOCAxLjEyNjk1MywzLjAxNzU3OCAtMC4wNzYsMS45NzgxOTcgLTEuNzQ1NTUsMy43NzA4NSAtMy43MzQzNzUsMy44ODg2NzIgYSAwLjI5NDk5MDUsMC4yOTQ5OTA1IDAgMCAwIC0wLjAxMTcyLDAuMDAyIGMgLTEuNjQ0MjA3LDAuMTY1NTU0IC0zLjI0NTI4NSwtMC4zMDU2MDcgLTQuNzkyOTY4LC0wLjk0OTIxOSAwLjQyMDg2NywtMS40NTM2MjcgMC42NzQ0LC0yLjk1NjkyMyAwLjc1LC00LjQ2ODc1IDEuNTE1Mjk0LC0xLjAzODYzNCAyLjkxNjg5OSwtMi4yMzEzMjkgNC4yMDg5ODQsLTMuNTMxMjUgeiBtIC0xNC4xOTkyMTg4LDMuMzQzNzUgYyAwLjYzNTE4NjgsMC42OTQzMjIgMS4zMDA3NTI4LDEuMzYxNjc5IDEuOTk4MDQ2OSwxLjk5NjA5NCAtMC43NzYwOTU4LDAuMTY1MDQ4IC0xLjU1OTMxNzUsMC4yNzU3MSAtMi4yOTEwMTU2LDAuMjg3MTA5IDAuMDQ4NzAxLC0wLjc1Mzg1MiAwLjE0ODY3MTIsLTEuNTM2ODI3IDAuMjkyOTY4NywtMi4yODMyMDMgeiBtIDguNDE5OTIxOCwxLjE4NTU0NyBjIC0wLjAwMTUsMC4wNjMyNSAtMC4wMSwwLjE3ODU3NyAtMC4wMTE3MiwwLjIzMjQyMiAtMC4wMDU4LDAuMTc3MzU2IC0wLjAxNjU5LDAuMzMyMzM1IC0wLjA4MDA4LDAuNjcxODc1IGEgMC4yOTQ5OTA1LDAuMjk0OTkwNSAwIDAgMCAtMC4wMDIsMC4wMTE3MiBjIC0wLjA5MjcyLDAuNjM2MTY2IC0wLjA2OTExLDAuNjQ0OTIzIC0wLjI1LDEuMjk2ODc1IC0wLjA3Njk4LDAuMjgwMTkgLTAuMTYxODU3LDAuNDA3Mzc5IC0wLjE5NzI2NSwwLjQzNTU0NiAtMC4wMzU0MSwwLjAyODE3IC0wLjA1MzYsMC4wNDAwMSAtMC4yMDUwNzksLTAuMDAyIC0wLjMwMjI5MSwtMC4wODM3MyAtMC45MDkzMDEsLTAuNDQwODA1IC0xLjgyMjI2NSwtMC44MjAzMTIgLTAuMDAyLC04LjI5ZS00IC0wLjAwMTksLTAuMDAxMSAtMC4wMDM5LC0wLjAwMiBDIDEwLjY2MjUzOCwyMC4xMTkwMTQgMTAuNTg2NDUsMjAuMDU0NTI1IDEwLjQ5NDE0MSwyMCBjIC0wLjEwMTcwMSwtMC4wNjAwNyAtMC4yMDEyMjcsLTAuMTE4Mjk2IC0wLjI5NDkyMiwtMC4xNzU3ODEgMS4wODMzOTIsLTAuMzkxNjkyIDIuMTE5OTM2LC0wLjg5NjI5NSAzLjExNTIzNCwtMS40NjQ4NDQgeiBtIDAuNzY3NTc4LDMuNjY0MDYyIDAuMDAzOSwwLjAyNTM5IC0wLjA1MDc4LDAuMDU4NTkgYSAwLjI5NDk5MDUsMC4yOTQ5OTA1IDAgMCAwIDAuMDQ2ODcsLTAuMDgzOTggeiIKICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4yOTA2NzE1LDAuMjk1MzU4NjgpIiAvPgoKCjwvc3ZnPgo=',
			58
		);
		add_action( 'load-' . $dashboard_page_suffix, array( &$this, 'load_page' ) );
	}

	function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;
		wp_admin_css( 'dashboard' );
		wp_enqueue_style( 'tainacan-dashboard-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-dashboard.css', [], TAINACAN_VERSION );
	}

	/**
	 * Enqueue the scripts for the dashboard page, using WordPress existing 'dashboard' and 'postbox' scripts
	 */
	function admin_enqueue_js() {
		global $TAINACAN_BASE_URL;

		wp_enqueue_script('dashboard');
		wp_enqueue_script('postbox');

		wp_enqueue_script(
			'tainacan-dashboard-scripts',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_dashboard.js',
			array('jquery', 'postbox'),
			TAINACAN_VERSION,
			true
		);

		$dashboard_settings = array(
			'disable_cards_sorting' => $this->has_admin_ui_option('disableDashboardCardsSorting'),
			'disable_news_card' => $this->has_admin_ui_option('hideDashboardNewsCard'),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'tainacan_dashboard_news_nonce' ),
			'error_message' => __('Something went wrong while loading the news.', 'tainacan'),
		);
		wp_localize_script(
			'tainacan-dashboard-scripts',
			'tainacan_dashboard',
			$dashboard_settings
		);
	}

	function load_page() {
		parent::load_page();

		$screen = get_current_screen();
    
		// Safety check
		if (!$screen)
			return;

		// Load the admin dashboard code from core
		require_once ABSPATH . 'wp-admin/includes/dashboard.php';

		// Register Tainacan Cards using WordPress Widgets API
		$this->register_cards();

		// // Add help tabs if needed
		// $screen->add_help_tab(array(
		// 	'id' => 'tainacan_dashboard_help_tab',
		// 	'title' => __('Dashboard Options', 'tainacan'),
		// 	'content' => '<p>' . __('You can customize which cards appear on this dashboard.', 'tainacan') . '</p>',
		// ));

		// $screen->set_help_sidebar(
		// 	'<p>' . __('For more information:', 'tainacan') . '</p>' .
		// 	'<p><a href="https://tainacan.org/" target="_blank">' . __('Tainacan Documentation', 'tainacan') . '</a></p>'
		// );
	}

	public function render_page_content() {
		require_once('page.php');
	}

	/**
	 * Registers the deafult dashboard cards to be displayed
	 */
	function register_cards() {

		/**
		 * Option that stores the user disabled cards
		 */
		$this->disabled_cards = get_option(
			'tainacan_dashboard_disabled_cards',
			array()
		);
		
		// Counter to keep default cards in first line by default
		$default_cards_counter = 0;

		/**
		 * Filling the array containing the default cards
		 * based on user capabilities
		 */
		$has_repository_card = !$this->has_admin_ui_option('hideDashboardRepositoryCard') &&
			(
				current_user_can( 'manage_tainacan' ) ||
				current_user_can( 'tnc_rep_edit_taxonomies') ||
				current_user_can( 'tnc_rep_edit_metadata') ||
				current_user_can( 'tnc_rep_edit_filters') ||
				current_user_can( 'tnc_rep_edit_users')
			);
		if ( $has_repository_card ) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-repository-card',
				'title' => __( 'Repository', 'tainacan' ),
				'description' => __('Area responsible for gathering all the structural settings that affect the collections of your repository.', 'tainacan'),
				'content' => [$this, 'tainacan_repository_dashboard_card'],
				'icon' => $this->get_svg_icon( 'repository' ),
				'color' => 'blue'
			);
			$default_cards_counter++;
		}

		$has_collections_card = !$this->has_admin_ui_option('hideDashboardCollectionsCard');
		if ( $has_collections_card ) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-collections-card',
				'title' => __( 'Collections', 'tainacan' ),
				'description' => __('Collections are groups of items in the repository that share the same set of metadata.', 'tainacan'),
				'content' => [$this, 'tainacan_collections_dashboard_card'],
				'icon' => $this->get_svg_icon( 'collections' ),
				'color' => 'turquoise',
				'position' => $default_cards_counter >= 1 ? 'side' : 'normal'
			);
			$default_cards_counter++;
		}

		if ( !$this->has_admin_ui_option('hideDashboardInfoCard') ) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-info-card',
				'title' => __( 'Help content and tutorials', 'tainacan' ),
				'description' => __('The Tainacan community provides some help resources. Below we list the main ones for you to clear your doubts.', 'tainacan'),
				'content' => array( $this, 'tainacan_help_dashboard_card' ),
				'icon' => $this->get_svg_icon( 'info' ),
				'color' => 'gray',
				'position' => $default_cards_counter >= 2 ? 'column3' : ( $default_cards_counter >= 1 ? 'side' : 'normal' )
			);
			$default_cards_counter++;
		}

		if ( !$this->has_admin_ui_option('hideDashboardNewsCard') ) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-news-card',
				'title' => __( 'News and events', 'tainacan' ),
				'description' => __('Keep an eye on oficial Tainacan news.', 'tainacan'),
				'content' => array( $this, 'tainacan_news_dashboard_card' ),
				'constrol' => array( $this, 'tainacan_news_dashboard_card_control' ),
				'icon' => $this->get_svg_icon( 'openurl' ),
				'color' => 'gray',
				'position' => $default_cards_counter >= 2 ? 'column3' : ( $default_cards_counter >= 1 ? 'side' : 'normal' )
			);
			$default_cards_counter++;
		}

		if ( !$this->has_admin_ui_option('hideDashboardCollectionCards') ) {
			
			$collections_query = array(
				'posts_per_page' => -1, // Get all collections,
			);

			if ( $this->has_admin_ui_option('showOnlyCollectionCardsAuthoredByUser') )
				$collections_query['author'] = get_current_user_id();

			// Fetch all collections
			$collections = tainacan_collections()->fetch($collections_query, 'OBJECT');

			foreach( $collections as $index => $collection ) {

				if ( $this->has_admin_ui_option('showOnlyCollectionCardsThatUserCanEdit') && !current_user_can( 'tnc_col_' . $collection->get_id() . '_edit_items' ) )
					continue;

				$tainacan_dashboard_cards[] = array(
					'id' => 'tainacan-dashboard-collection-card-' . $collection->get_id(),
					'title' => $collection->get_name(),
					'description' => $collection->get_description(),
					'content' => array( $this, 'tainacan_collection_dashboard_card' ),
					'content_args' => array( 'collection_id' => $collection->get_id() ),
					'icon' => $this->get_svg_icon( 'collection' ),
					'color' => 'turquoise',
					'position' => ['normal', 'side', 'column3'][$index % 3]
				);
			}
		}

		/**
		 * Use this filter to add or remove dashboard cards.
		 * Remeber to return an array containing the card objects
		 * with a structure that contains the id, title, description,
		 * content (a callback), icon (an svg or img html tag), color
		 * (one of gray, blue and turquoise) and position (normal, side, column3, column4)
		 * 
		 * If you remove any card from the array, users won't be able to add it anyway.
		 * If you just remove its id from the 'tainacan_dashboard_disabled_cards' wp option, 
		 * users will be able to add it again.
		 */
		$tainacan_dashboard_cards = apply_filters( 'tainacan-dashboard-cards', $tainacan_dashboard_cards );

		foreach ($tainacan_dashboard_cards as $card) {
			if ( in_array( $card['id'], $this->disabled_cards ) )
				continue;
			
			$this->add_dashboard_card(
				$card['id'],
				$card['title'],
				$card
			);
		}
	}

	/**
	 * Wrapper for the wp_add_dashboard_widget function
	 * that also accepts an icon and do not expects controle_callback or control_callback_args
	 * 
	 * @param string $id
	 * @param string $title
	 * @param array $args {
	 *    Optional. Array of arguments for adding a dashboard card.
	 * 		@type string description Summary or small description for the card.
	 * 		@type callable callback function to return HTML content inside the card.
	 * 		@type array $content_args Arguments to be passed to the content callback.
	 * 	 	@type string $icon Icon to be displayed on the card.
	 * 		@type string $color Color of the card. One of 'gray', 'blue', 'turquoise'.
	 * 		@type string $position Position of the card. One of 'normal', 'side', 'column3', 'column4'.
	 * }
	 */
	function add_dashboard_card( $id, $title, $args = array() ) {

		$defaults = array(
			'description' => '',
			'content' => null,
			'content_args' => null,
			'icon' => '',
			'color' => 'gray',
			'position' => 'normal',
			'control' => null
		);

		$args = wp_parse_args( $args, $defaults );

		$widget_name = '<span class="tainacan-dashboard-card-title">' . $title . '</span>';
		$widget_name = $args['icon'] ? ('<span class="icon" style="background-color: var(--tainacan-' . $args['color'] . '5);">' . $args['icon'] . '</span>' . $widget_name) : $widget_name;

		$content_callback = $args['content'];
		$control_callback = $args['control'];
		$callback_args = $args['content_args'];
		$private_callback_args = array( '__widget_basename' => $widget_name );

		if ( is_null( $callback_args ) )
			$callback_args = $private_callback_args;
		elseif ( is_array( $callback_args ) )
			$callback_args = array_merge( $callback_args, $private_callback_args );

		$content_callback = function () use ($args, $callback_args) {
			if ( $args['description'] )
				echo '<p class="tainacan-dashboard-card-description">' . wp_kses($args['description'], wp_kses_allowed_html('tainacan_content')) . '</p>';

			echo '<hr>';
			call_user_func($args['content'], $callback_args);
		};
		
		wp_add_dashboard_widget(
			$id,
			$widget_name,
			$content_callback,
			$control_callback,
			$callback_args,
			$args['position']
		);

		return $id;
	}


	/**
	 * Creates the display code for the repository card
	 */
	function tainacan_repository_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list" data-color-scheme="blue">
			<?php if ( ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_taxonomies') ) && !$this->has_admin_ui_option('hideDashboardRepositoryCardTaxonomiesButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/taxonomies')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('taxonomies'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Taxonomies', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_metadata') ) && !$this->has_admin_ui_option('hideDashboardRepositoryCardMetadataButton' ) ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/metadata')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('metadata'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Metadata', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_filters') ) && !$this->has_admin_ui_option('hideDashboardRepositoryCardFiltersButton' ) ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/filters')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('filters'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Filters', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) && !$this->has_admin_ui_option('hideDashboardRepositoryCardImportersButton' )  ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/importers')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('importers'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Importers', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) && $this->has_admin_ui_option('showDashboardRepositoryCardExportersButton' )  ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/exporters')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('export'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Exporters', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'read' ) && $this->has_admin_ui_option('showDashboardRepositoryCardProcessesButton' )  ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/processes')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('processes'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Processes', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'read' ) && $this->has_admin_ui_option('showDashboardRepositoryCardActivitiesButton' )  ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/activities')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('activities'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Activities', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'tnc_rep_edit_users' ) && $this->has_admin_ui_option('showDashboardRepositoryCardCapabilitiesButton' )  ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/capabilities')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('capability'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Capabilities', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) && $this->has_admin_ui_option('showDashboardRepositoryCardReportsButton' )  ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/reports')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('reports'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Reports', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the collections card
	 */
	function tainacan_collections_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list">
			<?php if ( !$this->has_admin_ui_option('hideDashboardCollectionsCardCollectionsListButton' ) ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('collections'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Collections list', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardCollectionsCardItemsListButton' ) ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/items')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('items'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Items list', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( ( current_user_can('manage_tainacan') || current_user_can('tnc_rep_edit_collections') ) && !$this->has_admin_ui_option('hideDashboardCollectionsCardNewCollectionButton' ) ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/new')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('add'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('New collection', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardCollectionsCardMyItemsListButton' ) ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/my-items?' . http_build_query(['authorid' => get_current_user_id()]) ) ); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('userfill'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('My Items list', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the info and help card
	 */
	function tainacan_help_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list" data-color-scheme="gray">
			<?php if ( !$this->has_admin_ui_option('hideDashboardInfoCardForumButton' ) ) : ?>
				<li>
					<a href="https://tainacan.discourse.group" target="_blank">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('discourse'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text">
							<?php esc_html_e('User\'s forum', 'tainacan'); ?>
							<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span> 
							<span class="external-link-icon"></span>
						</span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardInfoCardFAQButton' ) ) : ?>
				<li>
					<a href="<?php echo esc_url(__('https://tainacan.github.io/tainacan-wiki/#/faq', 'tainacan')); ?>" target="_blank">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('help'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text">
							<?php esc_html_e('F.A.Q.', 'tainacan'); ?>
							<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span> 
							<span class="external-link-icon"></span>
						</span>
					</span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardInfoCardWikiButton' ) ) : ?>
				<li>
					<a href="https://tainacan.github.io/tainacan-wiki/#/" target="_blank">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('info'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text">
							<?php esc_html_e('Wiki', 'tainacan'); ?>
							<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span> 
							<span class="external-link-icon"></span></span>
						</span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardInfoCardSourceCodeButton' ) ) : ?>
				<li>
					<a href="https://github.com/tainacan/tainacan" target="_blank">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('github'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text">
							<?php esc_html_e('GitHub', 'tainacan'); ?>
							<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span> 
							<span class="external-link-icon"></span></span>
						</span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardInfoCardVideosButton' ) ) : ?>
				<li>
					<a href="https://www.youtube.com/@Tainacan/videos" target="_blank">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('youtube'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text">
							<?php esc_html_e('YouTube', 'tainacan'); ?>
							<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span> 
							<span class="external-link-icon"></span></span>
						</span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the news card,
	 * featuring RSS feed from Tainacan website. At start
	 * there is only the loading placeholder, since fetching 
	 * is done via ajax.
	 */
	function tainacan_news_dashboard_card( $args = null ) {
		?>
		<ul id="tainacan-dashboard-news" class="tainacan-dashboard-links-list">
			<p><em><?php echo esc_html__( 'Loading news…', 'tainacan' ); ?></em></p>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the news card,
	 * featuring RSS feed from Tainacan website
	 */
	function tainacan_ajax_fetch_dashboard_news() {
		check_ajax_referer( 'tainacan_dashboard_news_nonce', '_nonce' );

		// Apply filters to allow customization
		$feed_options = apply_filters('tainacan_dashboard_news_feed', $this->default_news_feed_options);
		
		// Includes required library for fetching rss
		include_once(ABSPATH . WPINC . '/feed.php');
				
		// Actually fetches the feed  (this function already caches the feed every 12 hours using wp transient)
		$rss = fetch_feed($feed_options['feed_url']);

		// Checks if there was an error fetching the feed
		if ( is_wp_error($rss) ) { 
			wp_send_json_error([
				'message' => sprintf(
					/* translators: 1: feed title, 2: feed URL */
					__('The feed "%1$s" could not be loaded at the moment. <a href="%2$s" target="_blank" rel="noopener">Visit the website</a> to check its content.', 'tainacan'),
					esc_html($feed_options['title']),
					esc_url($feed_options['view_all_link'])
				)
			]);
		}
		
		// Fetches the items from the feed
		$max_items = $rss->get_item_quantity($feed_options['posts_per_feed']);
		$rss_items = $rss->get_items(0, $max_items);
		$feed_data = array();

		foreach ($rss_items as $item) {
			$feed_data[] = array(
				'title' => $item->get_title(),
				'link' => $item->get_permalink(),
				'date' => $item->get_date('U')
			);
		}

		ob_start();
    
		if ( empty($feed_data) ) : ?>
			<p><?php esc_html_e('No news available at the moment.', 'tainacan'); ?></p>
		<?php else : ?>
			<ul class="tainacan-dashboard-links-list">
				<?php foreach ($feed_data as $feed_item) : ?>
					<li>
						<a href="<?php echo esc_url($feed_item['link']); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $feed_item['title'] ); ?>
							<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span>
							<span class="external-link-icon"></span>
						</a>
						<?php
							$date = $feed_item['date'];
							if ( $date ) {
								$date = sprintf(
									/* translators: %s = human readable time period */
									_x('%s ago', '%s = human readable time period', 'tainacan'),
									human_time_diff($date, current_time('timestamp'))
								);
							}
						if ( $date ) : ?>
							<small class="link-subtitle"><?php echo esc_html($date); ?></small>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>

				<li style="margin-inline-start: auto;">
					<a href="<?php echo esc_url($feed_options['view_all_link']);?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e('See all news', 'tainacan'); ?>
						<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span>
						<span class="external-link-icon"></span>
					</a>
				</li>
			</ul>
		<?php endif;

		$html = ob_get_clean();
		wp_send_json_success( [ 'html' => $html ] );
	}
		
	/**
	 * Creates the display code for a collection card
	 */
	function tainacan_collection_dashboard_card($args = null) {
		$collection_id = isset($args['collection_id']) ? $args['collection_id'] : null;

		if ( is_null($collection_id) )
			return;
	
	?>
		<ul class="tainacan-dashboard-card-list">
			<?php if ( !$this->has_admin_ui_option('hideDashboardCollectionCardsItemsButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/items')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('items'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Items list', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardCollectionCardsExternalLinkButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(get_permalink( $collection_id )); ?>" target="_blank" rel="noopener noreferrer">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('openurl'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text">
							<?php esc_html_e('Visit page', 'tainacan'); ?>
							<span class="screen-reader-text"><?php esc_html_e(' (open in a new tab)', 'tainacan'); ?></span>
							<span class="external-link-icon"></span>
						</span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardCollectionCardsMyItemsButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/my-items?' . http_build_query(['authorid' => get_current_user_id()]))); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('userfill'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('My Items list', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardCollectionCardsMetadataButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/metadata')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('metadata'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Metadata', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( !$this->has_admin_ui_option('hideDashboardCollectionCardsSettingsButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/settings')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('settings'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Settings', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardCollectionCardsFiltersButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/filters')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('filters'), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Filters', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardCollectionCardsImportersButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/importers')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('importers' ), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Importers', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardCollectionCardsExportersButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/exporters')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('export' ), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Exporters', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardCollectionCardsActivitiesButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/activities')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('activities' ), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Activities', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardCollectionCardsCapabilitiesButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/capabilities')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('capability' ), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Capabilities', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( $this->has_admin_ui_option('showDashboardCollectionCardsReportsButton') ) : ?>
				<li>
					<a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/reports')); ?>">
						<span class="icon">
							<?php echo wp_kses( $this->get_svg_icon('reports' ), wp_kses_allowed_html('tainacan_menu_link') ); ?>
						</span>
						<span class="text"><?php esc_html_e('Reports', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	<?php
	}

}
