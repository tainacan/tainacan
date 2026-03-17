<?php

namespace Tainacan\Tools;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Registry of management tools. Used by the REST Tools controller and CLI adapters.
 *
 * @since 1.0.0
 */
class Tools_Registry {

	/**
	 * @var Management_Tool[]|null Cached instances by id.
	 */
	private static $tools = null;

	/**
	 * Default tool class names (filterable).
	 *
	 * @return array<int, string> Class names implementing Management_Tool.
	 */
	private static function get_default_tool_classes() {
		return [
			Management_Tool\Tool_Collection_List::class,
			Management_Tool\Tool_Control_Metadata::class,
			Management_Tool\Tool_Index_Content::class,
			Management_Tool\Tool_Move_Attachments::class,
			Management_Tool\Tool_Collection_Clean::class,
			Management_Tool\Tool_Garbage_Collector::class,
		];
	}

	/**
	 * Get all registered tools (instances). Uses filter 'tainacan_register_management_tools'
	 * with an array of class names; filter can add or remove.
	 *
	 * @return Management_Tool[]
	 */
	public static function get_all_tools() {
		if ( self::$tools !== null ) {
			return array_values( self::$tools );
		}
		$classes = apply_filters( 'tainacan_register_management_tools', self::get_default_tool_classes() );
		self::$tools = [];
		foreach ( $classes as $class_or_instance ) {
			$tool = is_object( $class_or_instance ) ? $class_or_instance : new $class_or_instance();
			if ( ! $tool instanceof Management_Tool ) {
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
	 * @return Management_Tool|null
	 */
	public static function get_tool( $id ) {
		if ( self::$tools === null ) {
			self::get_all_tools();
		}
		return isset( self::$tools[ $id ] ) ? self::$tools[ $id ] : null;
	}
}
