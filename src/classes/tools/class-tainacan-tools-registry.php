<?php

namespace Tainacan\Tools;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Registry of tools. Used by the REST Tools controller and CLI adapters.
 *
 * @since 1.1.0
 */
class Tools_Registry {

	/**
	 * @var Tool[]|null Cached instances by id.
	 */
	private static $tools = null;

	/**
	 * Default tool class names (filterable).
	 *
	 * @return array<int, string> Class names implementing Tool.
	 */
	private static function get_default_tool_classes() {
		return [
			Tool\Collection_List::class,
			Tool\Control_Metadata::class,
			Tool\Index_Content::class,
			Tool\Move_Attachments::class,
			Tool\Collection_Clean::class,
			Tool\Garbage_Collector::class,
		];
	}

	/**
	 * Get all registered tools (instances). Uses filter 'tainacan_register_tools'
	 * with an array of class names; filter can add or remove.
	 *
	 * @return Tool[]
	 */
	public static function get_all_tools() {
		if ( self::$tools !== null ) {
			return array_values( self::$tools );
		}
		$classes = apply_filters( 'tainacan_register_tools', self::get_default_tool_classes() );
		self::$tools = [];
		foreach ( $classes as $class_or_instance ) {
			$tool = is_object( $class_or_instance ) ? $class_or_instance : new $class_or_instance();
			if ( ! $tool instanceof Tool ) {
				continue;
			}
			$id = $tool->get_id();
			self::$tools[ $id ] = $tool;
		}
		return array_values( self::$tools );
	}

	/**
	 * Get a single tool by id.
	 *
	 * @param string $id Tool id (e.g. 'control_metadata').
	 * @return Tool|null
	 */
	public static function get_tool( $id ) {
		if ( self::$tools === null ) {
			self::get_all_tools();
		}
		return isset( self::$tools[ $id ] ) ? self::$tools[ $id ] : null;
	}
}
