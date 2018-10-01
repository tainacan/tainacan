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
}