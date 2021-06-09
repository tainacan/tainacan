<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Blocks that to not have a theme script, render the same content always
const TAINACAN_BLOCKS = [
	'items-list' => [],
	'collections-list' => [],
	'search-bar' => [ 'has_theme_script' => true ],
	'facets-list' => [ 'has_theme_script' => true ],
	'dynamic-items-list' => [ 'has_theme_script' => true ],
	'carousel-items-list' => [ 'has_theme_script' => true ],
	'carousel-terms-list' => [ 'has_theme_script' => true ],
	'carousel-collections-list' => [ 'has_theme_script' => true ],
	'terms-list' => [ 'extra_editor_script_deps' => array('undescore') ],
];

tainacan_blocks_initialize();

function tainacan_blocks_initialize() {
	global $wp_version;

	if (is_plugin_active('gutenberg/gutenberg.php') ||  $wp_version >= '5') {
		add_filter('block_categories', 'tainacan_blocks_register_categories', 10, 2);
		add_action('init', 'tainacan_blocks_add_plugin_settings', 90);
		add_action('init', 'tainacan_blocks_add_plugin_admin_settings', 90);
		add_action('init', 'register_tainacan_blocks_add_gutenberg_blocks');
		add_action('wp_enqueue_scripts', 'unregister_tainacan_blocks');
		add_action('admin_enqueue_scripts', 'unregister_tainacan_blocks');
	}
}

function register_tainacan_blocks_add_gutenberg_blocks() {
	tainacan_blocks_get_common_styles();
	tainacan_blocks_register_category_icon();

	foreach(TAINACAN_BLOCKS as $block_slug => $block_options) {
		tainacan_blocks_register_block($block_slug, $block_options);
	}
	tainacan_blocks_register_tainacan_item_submission_form();
	tainacan_blocks_register_tainacan_faceted_search();
}

function unregister_tainacan_blocks() {
	global $post;

	foreach(TAINACAN_BLOCKS as $block_slug => $block_options) {
		if ( !has_block('tainacan/' . $block_slug) || !is_singular() ) {
			wp_deregister_script($block_slug);
			wp_deregister_style($block_slug);

			if ( isset($block_options['has_theme_script']) && $block_options['has_theme_script'] )
				wp_deregister_script($block_slug . '-theme');
		}
	}

	if(!$post) return;

	$not_allowed = apply_filters('posts-names-to-unregister-tainacan-blocks', []);
	$current_page = $post->post_name;

	if ( in_array($current_page, $not_allowed) ) {
		foreach(TAINACAN_BLOCKS as $block_slug => $block_options) {
			wp_deregister_script($block_slug);
			wp_deregister_style($block_slug);

			if ( isset($block_options['has_theme_script']) && $block_options['has_theme_script'] )
				wp_deregister_script($block_slug . '-theme');

			if (function_exists('unregister_block_type'))
				unregister_block_type('tainacan/' . $block_slug);
		}

		wp_deregister_script('faceted-search');
		wp_deregister_script('tainacan-search');
		wp_deregister_script('item-submission-form');
		wp_deregister_script('google-recaptcha-script');
		wp_deregister_script('tainacan-blocks-register-category-icon');

		wp_deregister_style('faceted-search');
		wp_deregister_style('item-submission-form');
		wp_deregister_style('tainacan-blocks-common-styles');
		wp_deregister_style('tainacan-blocks-register-category-icon');

		if (function_exists('unregister_block_type')) {
			unregister_block_type('tainacan/faceted-search');
			unregister_block_type('tainacan/item-submission-form');
		}
	}
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

/*
 * Registers a 'generic' Tainacan Block, according to the TAINACAN_BLOCKs array
 * 
 */
function tainacan_blocks_register_block($block_slug, $options = []) {
	global $TAINACAN_BASE_URL;

	// Creates Register params based on registered scripts and styles
	$register_params = [];

	// Defines dependencies for editor script
	$editor_script_deps = array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor');
	if ( isset($options['extra_editor_script_deps']) )
		array_merge($editor_script_deps, $options['extra_editor_script_deps']);

	// Registers Editor Script
	wp_register_script(
		$block_slug,
		$TAINACAN_BASE_URL . '/assets/js/block_' . str_replace('-', '_' , $block_slug) . '.js',
		$editor_script_deps
	);
	wp_set_script_translations($block_slug, 'tainacan');
	$register_params['editor_script'] = $block_slug;

	// Registers Theme-side Script, if necessary
	if ( isset($options['has_theme_script']) && $options['has_theme_script'] ) {
		wp_enqueue_script(
			$block_slug . '-theme',
			$TAINACAN_BASE_URL . '/assets/js/block_' . str_replace('-', '_' , $block_slug) . '_theme.js',
			array('wp-i18n')
		);
		wp_set_script_translations($block_slug . '-theme', 'tainacan');
		$register_params['script'] = $block_slug;
	}

	// Registers style (works for both editor and theme side)
	wp_register_style(
		$block_slug,
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-' . $block_slug . '.css',
		array('wp-edit-blocks', 'tainacan-blocks-common-styles')
	);
	$register_params['style'] = $block_slug;

	// Registers the new block
	if (function_exists('register_block_type'))
		register_block_type( 'tainacan/' . $block_slug, $register_params );
}

function tainacan_blocks_register_tainacan_item_submission_form() {
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;

	wp_register_script(
		'tainacan-item-submission',
		$TAINACAN_BASE_URL . '/assets/js/item_submission.js',
		['underscore'],
		TAINACAN_VERSION
	);

	wp_register_script(
		'item-submission-form',
		$TAINACAN_BASE_URL . '/assets/js/block_item_submission_form.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor')
	);

	// Registers Google ReCAPTCHA
	wp_register_script(
		'google-recaptcha-script',
		'https://www.google.com/recaptcha/api.js',
		[], false, true 
	);
	wp_enqueue_script('google-recaptcha-script');

	// Registers extra metadata type forms
	$theme_helper = \Tainacan\Metadata_Types\Metadata_Type_Helper::get_instance();
	if (isset($theme_helper))
		$theme_helper->register_metadata_type_compoment();

	wp_register_style(
		'item-submission-form',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-item-submission-form.css',
		array('wp-edit-blocks', 'tainacan-blocks-common-styles')
	);

	if (function_exists('register_block_type')) {
		register_block_type( 'tainacan/item-submission-form', array(
			'editor_script' => 'item-submission-form',
			'style'         => 'item-submission-form',
			'script'		=> 'tainacan-item-submission'
		) );
	}
}

function tainacan_blocks_register_tainacan_faceted_search(){
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;

	wp_register_script(
		'tainacan-search',
		$TAINACAN_BASE_URL . '/assets/js/theme_search.js',
		['underscore'],
		TAINACAN_VERSION
	);

	wp_register_script(
		'faceted-search',
		$TAINACAN_BASE_URL . '/assets/js/block_faceted_search.js',
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor')
	);

	wp_set_script_translations('faceted-search', 'tainacan');

	wp_register_style(
		'faceted-search',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-faceted-search.css',
		array('wp-edit-blocks', 'tainacan-blocks-common-styles')
	);

	if (function_exists('register_block_type')) {
		register_block_type( 'tainacan/faceted-search', array(
			'editor_script' => 'faceted-search',
			'style'         => 'faceted-search',
			'script'		=> 'tainacan-search'
		) );
	}
}

function tainacan_blocks_get_plugin_js_settings(){
	global $TAINACAN_BASE_URL;
	global $wp_version;

	$settings = [
		'wp_version' => $wp_version,
		'root'     	 => esc_url_raw( rest_url() ) . 'tainacan/v2',
		'nonce'   	 => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : false,
		'base_url' 	 => $TAINACAN_BASE_URL,
		'admin_url'  => admin_url(),
		'site_url'	 => site_url(),
		'theme_items_list_url' => esc_url_raw( get_site_url() ) . '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug()
	];
	
	return $settings;
}

function tainacan_blocks_add_plugin_settings() {
	$settings = tainacan_blocks_get_plugin_js_settings();

	foreach(TAINACAN_BLOCKS as $block_slug => $block_options) {
		wp_localize_script( $block_slug, 'tainacan_blocks', $settings );
	}
	
	// The faceded search block also uses this settings for checking gutenberg version
	wp_localize_script( 'faceted-search', 'tainacan_blocks', $settings );
}

function tainacan_blocks_add_plugin_admin_settings() {

	// The faceded search block uses a different settings object, the same used on the theme items list
	wp_localize_script( 'tainacan-search', 'tainacan_plugin', \Tainacan\Admin::get_instance()->get_admin_js_localization_params() );

	// The item submission search block uses a different settings object, the same used on the item submission component
	wp_localize_script( 'tainacan-item-submission', 'tainacan_plugin', \Tainacan\Admin::get_instance()->get_admin_js_localization_params() );
}

function tainacan_blocks_get_common_styles() {
	global $TAINACAN_BASE_URL;

	wp_enqueue_style(
		'tainacan-blocks-common-styles',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-common-styles.css',
		array('wp-edit-blocks')
	);
}

function tainacan_blocks_register_category_icon() {
	global $TAINACAN_BASE_URL;
	
	wp_enqueue_script(
		'tainacan-blocks-register-category-icon',
		$TAINACAN_BASE_URL . '/assets/js/tainacan_blocks_category_icon.js',
		array('wp-blocks')
	);
}