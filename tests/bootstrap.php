<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Test_Tainacan
 */
$bootstrap_cfg = require('bootstrap-config.php');

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = $bootstrap_cfg['tests_dir'];
}

if (isset($bootstrap_cfg['tests_url'])) {
	define( 'TAINACAN_TESTS_URL', $bootstrap_cfg['tests_url'] );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/src/tainacan.php';
	add_action('plugins_loaded', function() {
		do_action('activate_' . substr(dirname( dirname( __FILE__ ) ), 1) . '/src/tainacan.php');
	}); 
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require_once(__DIR__ . '/factories/class-tainacan-entity-factory.php');
require_once(__DIR__ . '/factories/class-tainacan-field-factory.php');
require_once(__DIR__ . '/factories/class-tainacan-filter-factory.php');
require_once(__DIR__ . '/factories/class-tainacan-item-metadata-factory.php');
require_once(__DIR__ . '/tainacan-unit-test-case.php');
require_once(__DIR__ . '/tainacan-unit-api-test-case.php');

