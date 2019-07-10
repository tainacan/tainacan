<?php
/*
Plugin Name: Tainacan
Plugin URI: https://tainacan.org/
Description: powerfull and flexible repository platform for WordPress. Manage and publish you digital collections as easily as publishing a post to your blog, while having all the tools of a professional respository platform.
Author: Media Lab / UFG
Version: 0.10.1 
Text Domain: tainacan
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

const TAINACAN_VERSION = '0.10.1';

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$TAINACAN_BASE_URL = plugins_url('', __FILE__);
const TAINACAN_BASE_DIR     = __DIR__;
const TAINACAN_API_DIR     = __DIR__ . '/api/';
const TAINACAN_CLASSES_DIR = __DIR__ . '/classes/';
$TAINACAN_API_MAX_ITEMS_PER_PAGE = defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : 96;
require_once(TAINACAN_CLASSES_DIR . 'tainacan-creator.php');
require_once(TAINACAN_API_DIR     . 'tainacan-rest-creator.php');
require_once('migrations.php');

// DEV Interface, used for debugging
function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}
if ( tnc_enable_dev_wp_interface() ) {
	require_once('dev-interface/class-tainacan-dev-interface.php');
	require_once('dev-interface/class-tainacan-helpers-html.php');
	$Tainacan_Dev_interface = \Tainacan\DevInterface\DevInterface::get_instance();
}

function tainacan_load_plugin_textdomain() {
    load_plugin_textdomain( 'tainacan', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'tainacan_load_plugin_textdomain' );

add_action( 'after_setup_theme', function() {
	add_image_size( 'tainacan-small', 40, 40, true );
	add_image_size( 'tainacan-medium', 275, 275, true );
	add_image_size( 'tainacan-medium-full', 205, 1500 );
} );

add_action('init', ['Tainacan\Migrations', 'run_migrations']);
