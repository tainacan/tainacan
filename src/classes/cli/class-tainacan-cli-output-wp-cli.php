<?php

namespace Tainacan\Cli;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Tools\Output_Collector;

/**
 * Adapts management tools to the CLI context: forwards output to WP_CLI and normalizes WP-CLI args.
 *
 * @since 1.0.0
 */
class Cli_Output_Wp_Cli implements Output_Collector {

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
