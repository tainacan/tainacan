<?php 

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use WP_CLI;

/**
 * Handles WP-CLI command registration for Tainacan.
 *
 * Registers all available Tainacan WP-CLI commands including
 * garbage collection, attachment management, and collection operations.
 *
 * @since 1.0.0
 */
class Cli {
	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Initializes the CLI functionality.
	 *
	 * Sets up WP-CLI command registration after WordPress loads.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function init() {
		\WP_CLI::add_hook( 'after_wp_load', [$this, 'add_commands'] );
	}
	
	/**
	 * Registers all Tainacan WP-CLI commands.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function add_commands() {
		\WP_CLI::add_command('tainacan garbage-collector', 'Tainacan\Cli_Garbage_Collector');
		\WP_CLI::add_command('tainacan move-attachments-to-items-folder', 'Tainacan\Cli_Move_Attachments');
		\WP_CLI::add_command('tainacan collection', 'Tainacan\Cli_Collection');
		\WP_CLI::add_command('tainacan index-content', 'Tainacan\Cli_Document');
		\WP_CLI::add_command('tainacan control-metadata', 'Tainacan\Cli_Control_Metadata');
	}
	
}
