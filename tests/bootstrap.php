<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Test_Tainacan
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
$_tests_dir = '/home/leo/devel/rhs/tests/wordpress-tests-lib';
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/src/test-tainacan.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
