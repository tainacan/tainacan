<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Slugs and options for the Tainacan Blocks.
const TAINACAN_BLOCKS = [
	'items-list' => [],
	'collections-list' => [],
	'search-bar' => [],
	'facets-list' => [],
	'dynamic-items-list' => [],
	'carousel-items-list' => [],
	'carousel-terms-list' => [],
	'carousel-collections-list' => [],
	'related-items-list' => [],
	'terms-list' => [],
	'faceted-search' => [],
	'item-submission-form' => []
];

// Lets do this!
tainacan_blocks_initialize();

/** 
 * Initialize the Gutenberg Blocks logic, only if possible
 */
function tainacan_blocks_initialize() {
	global $wp_version;

	if (is_plugin_active('gutenberg/gutenberg.php') || $wp_version >= '5') {

		// Via Gutenberg filters, we create the Tainacan category
		if ( class_exists('WP_Block_Editor_Context') ) { // Introduced WP 5.8
			add_filter( 'block_categories_all', 'tainacan_blocks_register_categories', 10, 2 );
		} else {
			add_filter( 'block_categories', 'tainacan_blocks_register_categories', 10, 2 );
		}

		// On the theme side, all we need is the common scripts, 
		// that handle dynamically the imports using conditioner.js
		if ( !is_admin() ) {
			add_action( 'init', 'tainacan_blocks_add_common_theme_scripts', 90 );
			add_action( 'init', 'tainacan_blocks_get_common_theme_styles', 90 );
			// On the admin side, we need the blocks registered and their assets (editor-side)
		}
		add_action('admin_init', 'tainacan_blocks_register_and_enqueue_all_blocks');
	}
}

/** 
 * Registers the Tainacan category on the blocks inserter
 */
function tainacan_blocks_register_categories($categories, $editor_context) {
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

/** 
 * Calls the routines responsible for Registering the global style, category and 
 * both 'generic' and 'special' blocks
 */
function tainacan_blocks_register_and_enqueue_all_blocks() {
	tainacan_blocks_get_category_icon_script();
	tainacan_blocks_get_common_editor_styles();

	foreach(TAINACAN_BLOCKS as $block_slug => $block_options) {
		tainacan_blocks_register_block($block_slug, $block_options);
	}
}

/** 
 * Registers a 'generic' Tainacan Block, according to the TAINACAN_BLOCKs array
 * 
 * * @param array $options {
 *     Optional. Array of arguments.
 *     @type array		 $extra_editor_script_deps		Array of strings containing script dependencies of the editor side script
 *  }
 */
function tainacan_blocks_register_block($block_slug, $options = []) {
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;
	global $wp_version;

	// Creates Register params based on registered scripts and styles
	$register_params = [];

	// Defines dependencies for editor script
	$editor_script_deps = array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components');
	if ( version_compare( $wp_version, '5.2', '<') )
		$editor_script_deps[] = 'wp-editor';
	else
		$editor_script_deps[] = 'wp-block-editor';
	
	if ( isset($options['extra_editor_script_deps']) )
		array_merge($editor_script_deps, $options['extra_editor_script_deps']);

	// Registers Editor Script
	wp_register_script(
		$block_slug,
		$TAINACAN_BASE_URL . '/assets/js/block_' . str_replace('-', '_' , $block_slug) . '.js',
		$editor_script_deps,
		$TAINACAN_VERSION
	);
	$register_params['editor_script'] = $block_slug;

	// Passes global variables to the blocks editor side
	$block_settings = tainacan_blocks_get_plugin_js_settings();
	$plugin_settings = \Tainacan\Admin::get_instance()->get_admin_js_localization_params();
	wp_localize_script( $block_slug, 'tainacan_blocks', $block_settings);
	wp_localize_script( $block_slug, 'tainacan_plugin', $plugin_settings);

	// Registers style
	wp_register_style(
		$block_slug,
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-' . $block_slug . '.css',
		array('tainacan-blocks-common-editor-styles'),
		$TAINACAN_VERSION
	);
	$register_params['style'] = $block_slug;

	// Registers the new block
	if (function_exists('register_block_type')) {
		if ( version_compare( $wp_version, '5.8-RC', '>=') )
			register_block_type( __DIR__ . '/blocks/' . $block_slug );
		else
			register_block_type( 'tainacan/' . $block_slug, $register_params );
	}
}

/** 
 * Enqueues the global theme styles necessary for the majority of the blocks
 */
function tainacan_blocks_get_common_theme_styles() {
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;

	wp_enqueue_style(
		'tainacan-blocks-common-theme-styles',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-common-theme-styles.css',
		array(),
		$TAINACAN_VERSION
	);
}

/** 
 * Enqueues the global editor styles necessary for the majority of the blocks
 */
function tainacan_blocks_get_common_editor_styles() {
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;

	wp_enqueue_style(
		'tainacan-blocks-common-editor-styles',
		$TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-common-editor-styles.css',
		array(),
		$TAINACAN_VERSION
	);
}

/** 
 * Generates the global 'tainacan_blocks' that contains some info from PHP necessary 
 * to the blocks scripts in JS
 */
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

/** 
 * Efectivelly enqueues the common js and passes the necessary global variables
 */
function tainacan_blocks_add_common_theme_scripts() {
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;

	wp_enqueue_script(
		'tainacan-blocks-common-scripts',
		$TAINACAN_BASE_URL . '/assets/js/tainacan_blocks_common_scripts.js',
		array('wp-i18n'),
		$TAINACAN_VERSION
	);

	$block_settings = tainacan_blocks_get_plugin_js_settings();
	$plugin_settings = \Tainacan\Admin::get_instance()->get_admin_js_localization_params();

	wp_localize_script( 'tainacan-blocks-common-scripts', 'tainacan_blocks', $block_settings);
	wp_localize_script( 'tainacan-blocks-common-scripts', 'tainacan_plugin', $plugin_settings);

	// Necessary while we don't have a better way to do this only
	// when item submission block is present
	tainacan_blocks_add_extra_item_submission_assets();
}

/** 
 * Registers the extra scripts necessary for item submission block,
 * even on theme side :/
 */
function tainacan_blocks_add_extra_item_submission_assets() {
	
	// Registers extra script for Google ReCAPTCHA
	wp_register_script(
		'tainacan-google-recaptcha-script',
		'https://www.google.com/recaptcha/api.js',
		[], false, true 
	);
	wp_enqueue_script('tainacan-google-recaptcha-script');

	// Registers extra metadata type forms
	$theme_helper = \Tainacan\Metadata_Types\Metadata_Type_Helper::get_instance();
	if (isset($theme_helper))
		$theme_helper->register_metadata_type_component();
}

/** 
 * Registers the script that inserts the Tainacan icon on the blocks category
 */
function tainacan_blocks_get_category_icon_script() {
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;
	
	wp_enqueue_script(
		'tainacan-blocks-register-category-icon',
		$TAINACAN_BASE_URL . '/assets/js/tainacan_blocks_category_icon.js',
		array('wp-blocks'),
		$TAINACAN_VERSION
	);
}
