<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

tainacan_blocks_initialize();

function tainacan_blocks_initialize() {
	global $wp_version;

	if(is_plugin_active('gutenberg/gutenberg.php') ||  $wp_version >= '5') {
		tainacan_blocks_add_gutenberg_blocks_actions();
	}
}

function tainacan_blocks_add_gutenberg_blocks_actions() {
	// add_action('init', 'tainacan_blocks_register_tainacan_items_grid');
	add_action('init', 'tainacan_blocks_register_tainacan_terms_list');
	add_action('init', 'tainacan_blocks_register_tainacan_items_list');
	add_action('init', 'tainacan_blocks_register_tainacan_collections_list');

	add_action('init', 'tainacan_blocks_add_plugin_settings');
	
	//add_action('wp_enqueue_scripts', 'tainacan_blocks_enqueue_on_theme');
	add_filter('block_categories', 'tainacan_blocks_register_categories', 10, 2);
}

function tainacan_blocks_register_categories($categories, $post){

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

function tainacan_blocks_enqueue_on_theme(){
	global $TAINACAN_BASE_URL;

	// wp_enqueue_script(
	// 	'collections-carousel',
	// 	$TAINACAN_BASE_URL . '/assets/gutenberg_collections_carousel-components.js'
	// );
}

// function tainacan_blocks_register_tainacan_items_grid(){
// 	global $TAINACAN_BASE_URL;

// 	wp_register_script(
// 		'items-grid',
// 		$TAINACAN_BASE_URL . '/assets/gutenberg_items_grid-components.js',
// 		array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'underscore')
// 	);

// 	wp_register_style(
// 		'items-grid',
// 		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-blocks-style.css',
// 		array('wp-edit-blocks')
// 	);

// 	if (function_exists('register_block_type')) {
// 		register_block_type( 'tainacan/items-grid', array(
// 			'editor_script' => 'items-grid',
// 			'style'         => 'items-grid'
// 		) );
// 	}
// }

function tainacan_blocks_register_tainacan_terms_list(){
	global $TAINACAN_BASE_URL;

	wp_register_script(
		'terms-list',
		$TAINACAN_BASE_URL . '/assets/gutenberg_terms_list-components.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'underscore')
	);

	wp_register_style(
		'terms-list',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-blocks-style.css',
		array('wp-edit-blocks')
	);

	if(function_exists('register_block_type')) {
		register_block_type( 'tainacan/terms-list', array(
			'editor_script' => 'terms-list',
			'style'         => 'terms-list'
		) );
	}
}

function tainacan_blocks_register_tainacan_items_list(){
	global $TAINACAN_BASE_URL;

	wp_register_script(
		'items-list',
		$TAINACAN_BASE_URL . '/assets/gutenberg_items_list-components.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor')
	);

	wp_register_style(
		'items-list',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-blocks-style.css',
		array('wp-edit-blocks')
	);

	if(function_exists('register_block_type')) {
		register_block_type( 'tainacan/items-list', array(
			'editor_script' => 'items-list',
			'style'         => 'items-list'
		) );
	}
}

function tainacan_blocks_register_tainacan_collections_list(){
	global $TAINACAN_BASE_URL;

	wp_register_script(
		'collections-list',
		$TAINACAN_BASE_URL . '/assets/gutenberg_collections_list-components.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor')
	);

	wp_register_style(
		'collections-list',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-blocks-style.css',
		array('wp-edit-blocks')
	);

	if(function_exists('register_block_type')) {
		register_block_type( 'tainacan/collections-list', array(
			'editor_script' => 'collections-list',
			'style'         => 'collections-list'
		) );
	}
}

function tainacan_blocks_get_plugin_js_settings(){
	global $TAINACAN_BASE_URL;

	$settings = [
		'root'     => esc_url_raw( rest_url() ) . 'tainacan/v2',
		'nonce'    => wp_create_nonce( 'wp_rest' ),
		'base_url' => $TAINACAN_BASE_URL
	];

	return $settings;
}

function tainacan_blocks_add_plugin_settings() {

	$settings = tainacan_blocks_get_plugin_js_settings();

	// wp_localize_script( 'items-grid', 'tainacan_plugin', $settings );
	wp_localize_script( 'terms-list', 'tainacan_plugin', $settings );
	wp_localize_script( 'items-list', 'tainacan_plugin', $settings );
	wp_localize_script( 'collections-list', 'tainacan_plugin', $settings );
}
