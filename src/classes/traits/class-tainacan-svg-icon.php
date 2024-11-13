<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for getting inline svg icons based on the Tainacan Icon font and new symbols needed
 */
trait SVG_Icon {

	/**
	 * 
	 * @return string icon_slug with that points to the icon file
	 */
	public function get_svg_icon($icon_slug, $icon_color = null, $icon_size = null) {

		$root_path = plugin_dir_path( dirname( __FILE__, 2 ) );
		$icons_folder_path = $root_path . 'assets/icons/';

		$icons_folder_path = apply_filters('tainacan-svg-icons-folder-path', $icons_folder_path);

		$svg = file_get_contents($icons_folder_path . $icon_slug . '.svg');

		if ($icon_size) {
			$svg = str_replace('width="32"', 'width="' . $icon_size . '"', $svg);
			$svg = str_replace('height="32"', 'height="' . $icon_size . '"', $svg);
		}

		if ($icon_color) {
			$svg = str_replace('<svg ', '<svg fill="' . $icon_color . '"', $svg);
		}

		return $svg;
	}
}