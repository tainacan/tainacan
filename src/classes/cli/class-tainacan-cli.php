<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

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
		$normalized = CLI_Output::assoc_args_to_canonical( $assoc_args, $tool->get_params() );
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
		$output = new CLI_Output();
		$tool->run( $normalized, $output );
	}
}

/**
 * Adapts management tools to the CLI context: forwards output to WP_CLI and normalizes WP-CLI args.
 *
 * @since 1.0.0
 */
class CLI_Output implements \Tainacan\Tools\Output {

	/**
	 * Current progress bar instance (WP_CLI), or null if none.
	 *
	 * @var \WP_CLI\Utils\ProgressBar|null
	 */
	private $progress_bar = null;

	/**
	 * Convert WP-CLI assoc_args to canonical args for a tool.
	 * Keys: hyphens to underscores. Values: optional boolean casting from param definitions.
	 *
	 * @param array<string, mixed> $assoc_args       From WP-CLI (e.g. dry-run, collection).
	 * @param array<int, array>    $param_definitions Optional. Tool param definitions (name, type, ...) for casting.
	 * @return array<string, mixed>
	 */
	public static function assoc_args_to_canonical( $assoc_args, array $param_definitions = [] ) {
		$out = [];
		foreach ( $assoc_args as $key => $value ) {
			$canonical = str_replace( '-', '_', $key );
			$out[ $canonical ] = $value;
		}
		foreach ( $param_definitions as $param ) {
			$name = isset( $param['name'] ) ? $param['name'] : null;
			if ( ! $name || ! array_key_exists( $name, $out ) ) {
				continue;
			}
			$type = isset( $param['type'] ) ? strtolower( (string) $param['type'] ) : '';
			if ( $type === 'boolean' ) {
				$out[ $name ] = (bool) $out[ $name ];
			}
		}
		return $out;
	}

	/**
	 * @param string $message Message to log.
	 * @param string $level   One of 'info', 'success', 'warning', 'error'. Default 'info'.
	 */
	public function log( $message, $level = 'info' ) {
		if ( ! class_exists( 'WP_CLI' ) ) {
			return;
		}
		switch ( $level ) {
			case 'success':
				\WP_CLI::success( $message );
				break;
			case 'warning':
				\WP_CLI::warning( $message );
				break;
			case 'error':
				\WP_CLI::error( $message, false );
				break;
			default:
				\WP_CLI::log( $message );
		}
	}

	/**
	 * @param string $message Message to log.
	 */
	public function success( $message ) {
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::success( $message );
		}
	}

	/**
	 * @param string $message Message to log.
	 */
	public function warning( $message ) {
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::warning( $message );
		}
	}

	/**
	 * @param string $message Message to log.
	 * @param bool   $exit    Whether to exit (WP_CLI::error with exit).
	 */
	public function error( $message, $exit = false ) {
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::error( $message, $exit );
		}
	}

	public function start_progress( $total, $label ) {
		$this->progress_bar = null;
		if ( class_exists( 'WP_CLI' ) && $total > 0 ) {
			$this->progress_bar = \WP_CLI\Utils\make_progress_bar( $label, (int) $total );
		}
	}

	public function tick_progress( $increment = 1 ) {
		if ( $this->progress_bar !== null ) {
			$this->progress_bar->tick( (int) $increment );
		}
	}

	public function finish_progress() {
		if ( $this->progress_bar !== null ) {
			$this->progress_bar->finish();
			$this->progress_bar = null;
		}
	}

	/**
	 * @param array<int, array<string, mixed>> $rows
	 * @param array<int, string>               $columns
	 */
	public function output_table( array $rows, array $columns ) {
		if ( class_exists( 'WP_CLI' ) && class_exists( 'WP_CLI\Utils' ) ) {
			\WP_CLI\Utils\format_items( 'table', $rows, $columns );
		}
	}
}
