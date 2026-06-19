#!/usr/bin/env php
<?php
/**
 * Generate the Tainacan OpenAPI document using the WordPress test bootstrap.
 *
 * Requires tests/bootstrap-config.php and a WordPress test install (see tests/bin/install-wp-tests.sh).
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "This script must be run from the command line.\n" );
	exit( 1 );
}

$plugin_root = dirname( __DIR__, 2 );
$destination = $plugin_root . '/docs/openapi.json';
$namespace   = 'tainacan/v2';

foreach ( array_slice( $argv, 1 ) as $arg ) {
	if ( strpos( $arg, '--destination=' ) === 0 ) {
		$destination = substr( $arg, strlen( '--destination=' ) );
		if ( $destination === '' || $destination[0] !== '/' ) {
			$destination = $plugin_root . '/' . ltrim( $destination, '/' );
		}
	} elseif ( strpos( $arg, '--namespace=' ) === 0 ) {
		$namespace = substr( $arg, strlen( '--namespace=' ) );
	} elseif ( $arg === '--help' || $arg === '-h' ) {
		fwrite( STDOUT, "Usage: php docs/scripts/generate-openapi.php [--destination=PATH] [--namespace=tainacan/v2]\n" );
		exit( 0 );
	} else {
		fwrite( STDERR, "Unknown argument: $arg\n" );
		exit( 1 );
	}
}

$bootstrap_config = $plugin_root . '/tests/bootstrap-config.php';
if ( ! file_exists( $bootstrap_config ) ) {
	fwrite( STDERR, "Missing tests/bootstrap-config.php\n" );
	fwrite( STDERR, "Copy tests/bootstrap-config-sample.php to tests/bootstrap-config.php and install the WordPress test suite.\n" );
	exit( 1 );
}

require_once $plugin_root . '/docs/scripts/openapi-generator/class-generator.php';

// bootstrap.php loads bootstrap-config.php relative to the tests/ directory.
$previous_cwd = getcwd();
chdir( $plugin_root . '/tests' );
require $plugin_root . '/tests/bootstrap.php';
if ( $previous_cwd ) {
	chdir( $previous_cwd );
}

global $wp_rest_server;
$wp_rest_server = new WP_REST_Server();
do_action( 'rest_api_init' );

$namespaces = rest_get_server()->get_namespaces();
if ( ! in_array( $namespace, $namespaces, true ) ) {
	fwrite( STDERR, "REST namespace not found: $namespace\n" );
	fwrite( STDERR, 'Registered namespaces: ' . implode( ', ', $namespaces ) . "\n" );
	exit( 1 );
}

$routes   = rest_get_server()->get_routes( $namespace );
$data     = rest_get_server()->get_data_for_routes( $routes, 'help' );
$generator = new OpenAPIGenerator\Generator( $namespace, $data, false );
$document = $generator->generateDocument();

$json = json_encode( $document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
if ( $json === false ) {
	fwrite( STDERR, "Failed to encode OpenAPI document as JSON.\n" );
	exit( 1 );
}

$destination_dir = dirname( $destination );
if ( ! is_dir( $destination_dir ) && ! mkdir( $destination_dir, 0755, true ) && ! is_dir( $destination_dir ) ) {
	fwrite( STDERR, "Failed to create directory: $destination_dir\n" );
	exit( 1 );
}

if ( file_put_contents( $destination, $json ) === false ) {
	fwrite( STDERR, "Failed to write OpenAPI document to $destination\n" );
	exit( 1 );
}

fwrite( STDOUT, "OpenAPI document written to $destination\n" );
