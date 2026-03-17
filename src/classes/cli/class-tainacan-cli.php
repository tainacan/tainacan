<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Cli\Cli_Output_Wp_Cli;
use Tainacan\Tools\Tools_Registry;
use WP_CLI;

/**
 * Handles WP-CLI command registration for Tainacan.
 * All tools (including collection-list and collection-clean) are registered from the registry in a loop.
 *
 * @since 1.0.0
 */
class Cli {
	use \Tainacan\Traits\Singleton_Instance;

	private function init() {
		\WP_CLI::add_hook( 'after_wp_load', [ $this, 'add_commands' ] );
	}

	function add_commands() {
		$tools = Tools_Registry::get_all_tools();
		foreach ( $tools as $tool ) {
			$id = $tool->get_id();
			$name = str_replace( '_', '-', $id );
			\WP_CLI::add_command( 'tainacan ' . $name, new Cli_Tool_Command( $id ) );
		}

	}
}

/**
 * Generic WP-CLI invoker for a single management tool. Used by Cli::add_commands() in a loop.
 *
 * @since 1.0.0
 */
class Cli_Tool_Command {

	/** @var string */
	private $tool_id;

	public function __construct( $tool_id ) {
		$this->tool_id = $tool_id;
	}

	/**
	 * Tool id => param name to fill from $args[0] (for commands that take one positional id).
	 */
	private static $positional_param = [
		'collection_clean' => 'collection_id',
	];

	/**
	 * @param array<int, mixed>    $args       Positional args; first may map to a tool param (e.g. collection_id).
	 * @param array<string, mixed> $assoc_args Associative args from WP-CLI.
	 */
	public function __invoke( $args, $assoc_args ) {
		$tool = Tools_Registry::get_tool( $this->tool_id );
		if ( ! $tool ) {
			if ( class_exists( 'WP_CLI' ) ) {
				\WP_CLI::error( __( 'Tool not registered.', 'tainacan' ) );
			}
			return;
		}
		$normalized = Cli_Output_Wp_Cli::assoc_args_to_canonical( $assoc_args, $tool->get_params() );
		$param_from_arg = isset( self::$positional_param[ $this->tool_id ] ) ? self::$positional_param[ $this->tool_id ] : null;
		if ( $param_from_arg !== null && isset( $args[0] ) ) {
			$normalized[ $param_from_arg ] = $args[0];
		}
		if ( $this->tool_id === 'garbage_collector' && isset( $assoc_args['run'] ) ) {
			$normalized['run'] = true;
		}
		if ( $this->tool_id === 'garbage_collector' && ! empty( $normalized['run'] ) && class_exists( 'WP_CLI' ) ) {
			\WP_CLI::confirm( __( 'Are you sure you want to look for and DELETE all the garbage?', 'tainacan' ), $assoc_args );
		}
		$output = new Cli_Output_Wp_Cli();
		$tool->run( $normalized, $output );
	}
}
