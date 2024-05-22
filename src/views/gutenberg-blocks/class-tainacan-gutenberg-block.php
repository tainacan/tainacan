<?php

// Slugs and options for the Tainacan Blocks.
const TAINACAN_BLOCKS = [
	'items-list' => [],
	'collections-list' => [],
	'search-bar' => [],
	'facets-list' => [ 'set_script_translations' => true ],
	'dynamic-items-list' => [ 'set_script_translations' => true ],
	'carousel-items-list' => [ 'set_script_translations' => true ],
	'carousel-terms-list' => [ 'set_script_translations' => true ],
	'carousel-collections-list' => [ 'set_script_translations' => true ],
	'related-items-list' => [],
	'terms-list' => [],
	'faceted-search' => [],
	'item-submission-form' => [],
	'item-gallery' => [  'set_script_translations' => true, 'render_callback' => 'tainacan_blocks_render_items_gallery' ],
	'item-metadata-sections' => ['render_callback' => 'tainacan_blocks_render_metadata_sections'],
	'item-metadata-section' => ['render_callback' => 'tainacan_blocks_render_metadata_section'],
	'item-metadata' => ['render_callback' => 'tainacan_blocks_render_item_metadata'],
	'item-metadatum' => ['render_callback' => 'tainacan_blocks_render_item_metadatum'],
	'geocoordinate-item-metadatum' => ['render_callback' => 'tainacan_blocks_render_geocoordinate_item_metadatum'],
	'metadata-section-name' => [],
	'metadata-section-description' => []
];

// Lets do this!
tainacan_blocks_initialize();

/** 
 * Initialize the Gutenberg Blocks logic, only if possible
 */
function tainacan_blocks_initialize() {

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
	}

	// On the admin side, we need the blocks registered and their assets (editor-side)
	// The reason why we don't use admin_init here is because server side blocks
	// need to be registered whithin the init
	add_action('init', 'tainacan_blocks_register_and_enqueue_all_blocks');
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
				'title' => __( 'Tainacan Blocks', 'tainacan' ),
			),
			array(
				'slug' => 'tainacan-blocks-variations',
				'title' => __( 'Tainacan Query Loop Variations', 'tainacan' ),
			),
		)
	);
}

/** 
 * Calls the routines responsible for Registering the global style, category and 
 * both 'generic' and 'special' blocks
 */
function tainacan_blocks_register_and_enqueue_all_blocks() {
	// Only needed inside the editor
	if ( is_admin() ) {
		tainacan_blocks_get_category_icon_script();
		tainacan_blocks_get_common_editor_styles();
		tainacan_blocks_get_variations_script();
	}
	// May be needed outside the editor, if server side render is used
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

	// Creates Register params based on registered scripts and styles
	$register_params = [];

	// If there is a server side render callback, we add its render function
	if ( isset($options['render_callback']) ) {
		require_once( __DIR__ . '/blocks/' . $block_slug . '/save.php' );
		$register_params['render_callback'] = $options['render_callback'];
		$register_params['skip_inner_blocks'] = true;

	// Also, none of the rest is necessary regarding 
	// blocks that are non server side, their content
	// is independent of editor side scripts and styles.
	} else if ( !is_admin() ) {
		return;
	}

	// Defines dependencies for editor script
	$editor_script_deps = array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-server-side-render', 'wp-data');
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
	wp_set_script_translations( $block_slug, 'tainacan' );
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
		array(),
		$TAINACAN_VERSION
	);
	$register_params['style'] = $block_slug;

	// Makes sure translations that use wp.i18n work with our lazy loading strategy
	if ( isset($options['set_script_translations']) && $options['set_script_translations'] ) {
		wp_register_script(
			'tainacan-chunks-' . $block_slug . '-theme',
			$TAINACAN_BASE_URL . '/assets/js/tainacan-chunks-' . $block_slug . '-theme.js',
			array('wp-i18n'),
			$TAINACAN_VERSION
		);
		wp_set_script_translations( 'tainacan-chunks-' . $block_slug . '-theme', 'tainacan' );
		wp_add_inline_script( 'wp-i18n', wp_scripts()->print_translations('tainacan-chunks-' . $block_slug . '-theme', false) );
	}

	// Registers the new block
	if (function_exists('register_block_type')) 
		register_block_type( __DIR__ . '/blocks/' . $block_slug, $register_params );
	
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
	global $TAINACAN_API_MAX_ITEMS_PER_PAGE;
	global $wp_version;

	$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
	$collections = $Tainacan_Collections->fetch( [], 'OBJECT' );
	$cpts = [];
	foreach ( $collections as $col ) {
		$cpts[$col->get_db_identifier()] = $col->get_name();
	}

	$settings = [
		'wp_version' => $wp_version,
		'root'     	 => esc_url_raw( rest_url() ) . 'tainacan/v2',
		'nonce'   	 => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : false,
		'base_url' 	 => $TAINACAN_BASE_URL,
		'api_max_items_per_page'    => $TAINACAN_API_MAX_ITEMS_PER_PAGE,
		'admin_url'  => admin_url(),
		'site_url'	 => site_url(),
		'theme_items_list_url' => esc_url_raw( get_site_url() ) . '/' . \Tainacan\Theme_Helper::get_instance()->get_items_list_slug(),
		'collections_post_types' => $cpts,
		'registered_view_modes' => \Tainacan\Theme_Helper::get_instance()->get_registered_view_modes(),
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

	wp_set_script_translations( 'tainacan-blocks-common-scripts', 'tainacan' );

	$block_settings = tainacan_blocks_get_plugin_js_settings();
	$plugin_settings = \Tainacan\Admin::get_instance()->get_admin_js_localization_params();

	wp_localize_script( 'tainacan-blocks-common-scripts', 'tainacan_blocks', $block_settings);
	wp_localize_script( 'tainacan-blocks-common-scripts', 'tainacan_plugin', $plugin_settings);

	// Necessary do this only when the item submission block is present
	function tainacan_enqueue_extra_item_submission_assets() {
		if ( has_block( 'tainacan/item-submission-form', get_the_ID() ) )
			tainacan_blocks_add_extra_item_submission_assets();
	}
	add_action('wp', 'tainacan_enqueue_extra_item_submission_assets');
}

/** 
 * Registers the extra scripts necessary for item submission block
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
		array('wp-blocks', 'wp-element'),
		$TAINACAN_VERSION
	);
}

/** 
 * Registers the script that inserts the Query Loop Block variations
 */
function tainacan_blocks_get_variations_script() {
	global $TAINACAN_BASE_URL;
	global $TAINACAN_VERSION;

	wp_enqueue_script(
		'tainacan-blocks-query-variations',
		$TAINACAN_BASE_URL . '/assets/js/tainacan_blocks_query_variations.js',
		array('wp-blocks', 'wp-components', 'wp-i18n'),
		$TAINACAN_VERSION
	);

	$block_settings = tainacan_blocks_get_plugin_js_settings();
	wp_localize_script( 'tainacan-blocks-query-variations', 'tainacan_blocks', $block_settings );
}

