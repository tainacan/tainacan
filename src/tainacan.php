<?php
/*
Plugin Name: Tainacan
Plugin URI: https://tainacan.org/new
Description: powerfull and flexible repository platform for WordPress. Manage and publish you digital collections as easily as publishing a post to your blog, while having all the tools of a professional respository platform.
Author: Media Lab / UFG
Version: 0.1
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$TAINACAN_BASE_URL = plugins_url('', __FILE__);

const TAINACAN_API_DIR     = __DIR__ . '/api/';
const TAINACAN_CLASSES_DIR = __DIR__ . '/classes/';

require_once(TAINACAN_CLASSES_DIR . 'tainacan-creator.php');
require_once(TAINACAN_API_DIR     . 'tainacan-rest-creator.php');

require_once('dev-interface/class-tainacan-dev-interface.php');
if ( tnc_enable_dev_wp_interface() ) {
	$Tainacan_Dev_interface = \Tainacan\DevInterface\DevInterface::get_instance();
}

function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}

$Tainacan_Capabilities = \Tainacan\Capabilities::get_instance();
register_activation_hook( __FILE__, array( $Tainacan_Capabilities, 'init' ) );

// TODO move it somewhere else?
require_once('admin/class-tainacan-admin.php');
$Tainacan_Admin = \Tainacan\Admin::get_instance();

require_once('theme-helper/class-tainacan-theme-helper.php');
require_once('theme-helper/template-tags.php');
$Tainacan_Admin = \Tainacan\Theme_Helper::get_instance();

function tainacan_load_plugin_textdomain() {
    load_plugin_textdomain( 'tainacan', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'tainacan_load_plugin_textdomain' );