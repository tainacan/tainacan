<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Components_Hooks
 */
class Component_Hooks {

	private static $instance = null;
	/**
	 * Stores external component type available to be used in Tainacan
	 */
	private $registered_component;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->registered_component = [];
		$this->init();
	}

	private function init() {
		// the priority should see less than on function
		// `load_admin_page()` of class `Admin` in file /src/views/class-tainacan-admin.php
		add_action( 'init', array( &$this, 'register_component' ), 80 );
	}

	public function register_component() {
		do_action('tainacan-register-vuejs-component', $this);
		foreach($this->registered_component as $handle => $component) {
			$deps = isset($component['args']['deps']) ? $component['args']['deps'] : [];

			if ( is_admin() ) {
				wp_enqueue_script($handle, $component['script_path'], $deps);
			} else {
				if (isset($component['args']['public']) == true && $component['args']['public'] != false) {
					wp_enqueue_script($handle, $component['script_path'], $deps);
				}
			}
		}
	}

	/**
	 * Register a new vuejs component
	 * 
	 * @param string $handle name of the component. Should be unique.
	 * @param string $script_path path of file component
	 * @param array|string $args
	 */
	public function register_vuejs_component($handle, $script_path, $args = []) {
		global $TAINACAN_EXTRA_SCRIPTS;

		if ( ! in_array( $handle, $this->registered_component ) ) {
			$TAINACAN_EXTRA_SCRIPTS[] = $handle;
			$this->registered_component[$handle] = [
				'handle' => $handle,
				'script_path' => $script_path,
				'args' => $args
			];
		}
	}

	/**
	 * Get a list of all registered component
	 * 
	 * @return array The list of registered component
	 */
	public function get_registered_component() {
		return $this->registered_component;
	}

	/**
	 * Get one specific component by its slug
	 * 
	 * @param string $handle Name of the component
	 * 
	 * @return array|false The component definition or false if it is not found
	 */
	public function get_component($handle) {
		return isset($this->registered_component[$handle]) ? $this->registered_component[$handle] : false;
	}

}