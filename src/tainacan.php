<?php
/*
Plugin Name: Tainacan
Plugin URI: https://tainacan.org/
Description: Open source, powerful and flexible repository platform for WordPress. Manage and publish you digital collections as easily as publishing a post to your blog, while having all the tools of a professional repository platform.
Author: Tainacan.org
Author URI: https://tainacan.org/
Version: 0.21.8
Requires at least: 5.9
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 0.21.8
Text Domain: tainacan
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

const TAINACAN_VERSION = '0.21.8';

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$TAINACAN_BASE_URL = plugins_url('', __FILE__);
const TAINACAN_BASE_DIR     = __DIR__;
const TAINACAN_API_DIR     = __DIR__ . '/classes/api/';
const TAINACAN_CLASSES_DIR = __DIR__ . '/classes/';
$TAINACAN_API_MAX_ITEMS_PER_PAGE = defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : 96;
require_once(TAINACAN_CLASSES_DIR . 'tainacan-creator.php');
require_once(TAINACAN_API_DIR     . 'tainacan-rest-creator.php');
require_once('migrations.php');

// DEV Interface, used for debugging
function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}

add_action( 'after_setup_theme', function() {
	add_image_size( 'tainacan-small', 40, 40, true );
	add_image_size( 'tainacan-medium', 275, 275, true );
	add_image_size( 'tainacan-medium-full', 205, 1500 );
	add_image_size( 'tainacan-large-full', 480, 860 );
} );

// This enables Tainacan media sizes in the admin interface, including Gutenberg blocks
add_filter( 'image_size_names_choose', function ( $sizes ) {
    return array_merge( $sizes, array(
        'tainacan-small' 		=> __( 'Tainacan small (40x40 - cropped)', 'tainacan' ),
		'tainacan-medium' 		=> __( 'Tainacan medium (275x275 - cropped)', 'tainacan' ),
		'tainacan-medium-full'	=> __( 'Tainacan medium full (205x1500 - not cropped)', 'tainacan' ),
		'tainacan-large-full'	=> __( 'Tainacan large full (480x860 - not cropped)', 'tainacan' )
    ) );
} );


add_action('init', ['Tainacan\Migrations', 'run_migrations']);

//https://core.trac.wordpress.org/ticket/23022
//https://core.trac.wordpress.org/ticket/23022#comment:13
add_filter( 'wp_untrash_post_status', function( $new_status, $post_id, $previous_status ) {
	return $previous_status;
}, 10, 3 );

function wp_kses_tainacan($content, $context='tainacan_content') {
	$allowed_html = wp_kses_allowed_html($context);
	return wp_kses($content, $allowed_html);
}

add_filter('wp_kses_allowed_html', function($allowedposttags, $context) {
	switch ( $context ) {
		case 'tainacan_content':
			$post_allowed_html = wp_kses_allowed_html('post');
			return  array_merge(
				$post_allowed_html, 
				['iframe' => array(
					'src'             => true,
					'height'          => true,
					'width'           => true,
					'frameborder'     => true,
					'allowfullscreen' => true,
				)]
			);
		default:
			return $allowedposttags;
	}
}, 10, 2);


// Function to add rules to [upload_dir]/tainacan/.htaccess
function tainacan_add_htaccess_rules() {
	if( function_exists('insert_with_markers') ) {
		$uploads_dir = wp_upload_dir(); // Uploads directory
		$htaccess_dir = trailingslashit($uploads_dir['basedir']) . 'tainacan'; // Path to the tainacan folder
		$htaccess_file = trailingslashit($htaccess_dir) . '.htaccess'; // Path to the .htaccess file

		// If the folder doesn't exist, create it
		if (!file_exists($htaccess_dir)) {
			wp_mkdir_p($htaccess_dir);
		}

		$marker = 'Tainacan [<wp_upload_dir()>/tainacan] rules'; // Marker name for identification
		$rules = array(
			'# Prevent direct access to files',
			'Order deny,allow',
			'Deny from all'
		); // Rules to be added

		// Add rules to the .htaccess file
		insert_with_markers($htaccess_file, $marker, $rules);
	}
}

// Hook to execute the function when the plugin is activated
register_activation_hook(__FILE__, 'tainacan_add_htaccess_rules');