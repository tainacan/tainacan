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
// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/src/class-tainacan.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
