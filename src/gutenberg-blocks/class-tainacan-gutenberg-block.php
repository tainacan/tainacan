<?php

namespace Tainacan;
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class GutenbergBlock {

	private static $instance = null;

	function __construct() {
		if(is_plugin_active('gutenberg/gutenberg.php')) {
			$this->add_gutenberg_blocks_actions();
		}
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function add_gutenberg_blocks_actions() {
		add_action('init', array($this, 'register_tainacan_collections_carousel'));
		add_action('init', array($this, 'add_plugin_settings'));

		add_action('wp_enqueue_scripts', array($this, 'enqueue_on_theme'));
		add_filter('block_categories', array($this, 'register_tainacan_block_categories'), 10, 2);
	}

	public function register_tainacan_block_categories($categories, $post){
		if ( $post->post_type !== 'post' ) {
			return $categories;
		}

		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'tainacan-blocks',
					'title' => __( 'Tainacan', 'tainacan' ),
				),
			)
		);
	}

	public function enqueue_on_theme(){
		global $TAINACAN_BASE_URL;

		wp_enqueue_script(
			'collections-carousel',
			$TAINACAN_BASE_URL . '/assets/collections_carousel-components.js',
			array('wp-blocks', 'wp-element')
		);
	}

	public function register_tainacan_collections_carousel(){
		global $TAINACAN_BASE_URL;

		// Collections carousel
		wp_register_script(
			'collections-carousel',
			 $TAINACAN_BASE_URL . '/assets/collections_carousel-components.js',
			array('wp-blocks', 'wp-element')
		);

		wp_register_style(
			'collections-carousel',
			$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-blocks-style.css',
			array('wp-edit-blocks')
		);

		register_block_type('tainacan/collections-carousel', array(
			'editor_script' => 'collections-carousel',
			'style'         => 'collections-carousel'
		));
	}

	public function get_plugin_js_settings(){
		global $TAINACAN_BASE_URL;

		$settings = [
			'root'     => esc_url_raw( rest_url() ) . 'tainacan/v2',
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'base_url' => $TAINACAN_BASE_URL
		];

		return $settings;
	}

	function add_plugin_settings() {

		$settings = $this->get_plugin_js_settings();

		wp_localize_script( 'collections-carousel', 'tainacan_plugin', $settings );
	}
}