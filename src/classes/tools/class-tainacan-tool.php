<?php

namespace Tainacan\Tools;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Tools\Output;

/**
 * Contract for a tool that can be run from REST (Tools UI) or WP-CLI.
 * Tools receive args with canonical keys (underscores). Consumers (REST controller,
 * CLI adapters) are responsible for normalizing their input to this format.
 *
 * @since 1.1.0
 */
interface Tool {

	/**
	 * Stable id used in REST and for registry lookup (e.g. 'control_metadata').
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Translatable label for the tool.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Translatable description.
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Param definitions for the tool. Use underscore names for consistency.
	 * Each entry: name, type, required, label (optional), default (optional), description (optional).
	 *
	 * @return array<int, array{name: string, type: string, required: bool, label?: string, default?: mixed, description?: string}>
	 */
	public function get_params();

	/**
	 * Whether the tool is destructive (e.g. prompts confirmation in the UI).
	 *
	 * @return bool
	 */
	public function is_destructive();

	/**
	 * Whether the tool should be exposed in the REST API (list and run). When false, it will not appear in GET tools or be runnable via POST. CLI always registers all tools.
	 *
	 * @return bool
	 */
	public function is_exposed_to_rest();

	/**
	 * Run the tool. Args use canonical keys (underscores).
	 *
	 * @param array<string, mixed> $args   Normalized arguments (e.g. collection, dry_run).
	 * @param Output               $output Where to send log/success/warning/error output.
	 * @return void
	 */
	public function run( array $args, Output $output );
}

