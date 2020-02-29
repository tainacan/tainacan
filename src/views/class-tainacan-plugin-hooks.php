<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Plugins_Hooks
 */
class Plugin_Hooks {

	private static $instance = null;
	/**
	 * Stores external vue plugin available to be used in Tainacan
	 */
	private $registered_plugin;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->registered_plugin = [];
		$this->init();
	}

	private function init() {
		// the priority should see less than on function
		// `load_admin_page()` of class `Admin` in file /src/views/class-tainacan-admin.php
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_plugin' ), 80 );
		do_action('tainacan-register-vuejs-plugin', $this);
	}

	public function register_plugin() {
		foreach($this->registered_plugin as $handle => $plugin) {
			wp_enqueue_script($handle, $plugin['script_path']);
		}
	}

	/**
	 * Register a new vuejs plugin
	 * 
	 * @param string $handle name of the plugin. Should be unique.
	 * @param string $script_path path of file plugin
	 * @param array|string $args
	 */
	public function register_vuejs_plugin($handle, $script_path, $args = []) {
		global $TAINACAN_EXTRA_SCRIPTS;

		if ( ! in_array( $handle, $this->registered_plugin ) ) {
			$TAINACAN_EXTRA_SCRIPTS[] = $handle;
			$this->registered_plugin[$handle] = [
				'handle' => $handle,
				'script_path' => $script_path,
				'args' => $args
			];
		}
	}

	/**
	 * Get a list of all registered plugin
	 * 
	 * @return array The list of registered plugin
	 */
	public function get_registered_plugin() {
		return $this->registered_plugin;
	}

	/**
	 * Get one specific plugin by its slug
	 * 
	 * @param string $handle Name of the plugin
	 * 
	 * @return array|false The plugin definition or false if it is not found
	 */
	public function get_plugin($handle) {
		return isset($this->registered_plugin[$handle]) ? $this->registered_plugin[$handle] : false;
	}

}