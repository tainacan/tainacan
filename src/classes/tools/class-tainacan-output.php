<?php

namespace Tainacan\Tools;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Contract for where tool output goes (terminal, REST response, etc.).
 * Lets the same tool logic run from WP-CLI or from the REST Tools API.
 *
 * @since 1.1.0
 */
interface Output {

	/**
	 * Log a message with optional level.
	 *
	 * @param string $message Message to log.
	 * @param string $level   One of 'info', 'success', 'warning', 'error'. Default 'info'.
	 */
	public function log( $message, $level = 'info' );

	/**
	 * Log a success message.
	 *
	 * @param string $message Message to log.
	 */
	public function success( $message );

	/**
	 * Log a warning message.
	 *
	 * @param string $message Message to log.
	 */
	public function warning( $message );

	/**
	 * Log an error message. May throw or exit depending on implementation.
	 *
	 * @param string $message Message to log.
	 * @param bool   $exit    Whether to exit after logging (CLI adapter). Default false.
	 */
	public function error( $message, $exit = false );

	/**
	 * Start a progress phase. Call tick_progress() in a loop, then finish_progress().
	 * The collector decides how to present progress (e.g. progress bar vs log lines).
	 *
	 * @param int    $total Total number of steps (e.g. items to process).
	 * @param string $label Short description for the progress phase.
	 */
	public function start_progress( $total, $label );

	/**
	 * Advance progress by a number of steps. May be called many times.
	 *
	 * @param int $increment Number of steps completed. Default 1.
	 */
	public function tick_progress( $increment = 1 );

	/**
	 * End the current progress phase.
	 */
	public function finish_progress();

	/**
	 * Output a table (e.g. for list commands). CLI may use format_items; REST may store for response.
	 *
	 * @param array<int, array<string, mixed>> $rows    List of row arrays (e.g. [ ['ID' => 1, 'title' => 'Foo'], ... ]).
	 * @param array<int, string>               $columns Column keys in order (e.g. [ 'ID', 'title' ]).
	 */
	public function output_table( array $rows, array $columns );
}

