<?php
/*
Plugin Name: Tainacan
Plugin URI: https://tainacan.org/
Description: Open source, powerful and flexible repository platform for WordPress. Manage and publish you digital collections as easily as publishing a post to your blog, while having all the tools of a professional repository platform.
Author: Tainacan.org
Author URI: https://tainacan.org/
Version: 1.0.3
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.0
Stable tag: 1.0.3
Text Domain: tainacan
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Prevent direct access to the file
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Basic constants
const TAINACAN_VERSION = '1.0.3';
const TAINACAN_BASE_DIR    = __DIR__;
const TAINACAN_CLASSES_DIR = __DIR__ . '/classes/';

$TAINACAN_BASE_URL = plugins_url('', __FILE__);
$TAINACAN_API_MAX_ITEMS_PER_PAGE = defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? max(TAINACAN_API_MAX_ITEMS_PER_PAGE, 12) : get_option('tainacan_option_search_results_per_page', 96);

// Initialization logic (loads most classes and instantiates singletons)
require_once(TAINACAN_CLASSES_DIR . 'tainacan-creator.php');

// Perform migrations
require_once('migrations.php');
add_action('init', ['Tainacan\Migrations', 'run_migrations']);

// Hook to update .htaccess when the plugin is activated
register_activation_hook(__FILE__, ['Tainacan\Private_Files', 'add_htaccess_rules']);
