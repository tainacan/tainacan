<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for getting Admin UI options passed either via query string in
 * the URL or via the 'tainacan-admin-ui-options' filter.
 */
trait Admin_UI_Options {

	/**
	 * 
	 * @return string option value for the given setting
	 */
	public function has_admin_ui_option($option) {

		// Get Admin Options to tweak which components will be displayed
		$admin_options = apply_filters('tainacan-admin-ui-options', $_GET);

		return isset($admin_options[$option]) && ( $admin_options[$option] === 'true' || $admin_options[$option] === true );
	}
}