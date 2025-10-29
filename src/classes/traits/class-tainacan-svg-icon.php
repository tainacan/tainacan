<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for getting inline svg icons based on the Tainacan Icon font and new symbols needed
 * The svg files have a fill of currentColor and width and height of 1em to allow external customization.
 */
trait SVG_Icon {

	/**
	 * 
	 * @return string icon_slug with that points to the icon file
	 */
	public function get_svg_icon($icon_slug) {

		$root_path = plugin_dir_path( dirname( __FILE__, 2 ) );
		$icons_folder_path = $root_path . 'assets/icons/';

		$icons_folder_path = apply_filters('tainacan-svg-icons-folder-path', $icons_folder_path);

		$svg = file_get_contents($icons_folder_path . $icon_slug . '.svg');

		return $svg;
	}
}