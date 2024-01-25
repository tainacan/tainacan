<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class MetadataTypeHelper
 */
class Metadata_Type_Helper {

	private static $instance = null;
	/**
	 * Stores external metadata type available to be used in Tainacan
	 */
	private $registered_metadata_type;
	private $Tainacan_Metadata;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->registered_metadata_type = [];
		$this->Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$this->init();
	}

	private function init() {
		//register metadatum types
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Text');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Textarea');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Date');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Numeric');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Selectbox');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Relationship');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Taxonomy');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Compound');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\User');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Control');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\GeoCoordinate');
		$this->Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\URL');

		// the priority should see less than on function 
		// `load_admin_page()` of class `Admin` in file /src/views/class-tainacan-admin.php
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_metadata_type_component' ), 80 ); 
		do_action('tainacan-register-metadata-type', $this);
	}

	public function register_metadata_type_component() {
		foreach($this->registered_metadata_type as $handle => $component) {
			wp_enqueue_script($handle, $component['script_path']);
		}
	}

	/**
	 * Register a new Metadata Type
	 * 
	 * @param string $handle name of the component. Should be unique.
	 * @param string $class a class name of the metadata type
	 * @param array|string $args
	 */
	public function register_metadata_type($handle, $class_name, $script_path, $args = []) {
		global $TAINACAN_EXTRA_SCRIPTS;

		$this->Tainacan_Metadata->register_metadata_type($class_name);
		if ( ! in_array( $handle, $this->registered_metadata_type ) ) {
			$TAINACAN_EXTRA_SCRIPTS[] = $handle;

			$this->registered_metadata_type[$handle] = [
				'handle' => $handle,
				'class_name' => $class_name,
				'script_path' => $script_path, 
				'args' => $args
			];
		}

	}

	/**
	 * Get a list of all registered metadata type
	 * 
	 * @return array The list of registered metadata type
	 */
	public function get_registered_metadata_type() {
		return $this->registered_metadata_type;
	}

	/**
	 * Get one specific metadata type by its slug
	 * 
	 * @param string $handle Name of the component
	 * 
	 * @return array|false The metadata type definition or false if it is not found
	 */
	public function get_metadata_type($handle) {
		return isset($this->registered_metadata_type[$handle]) ? $this->registered_metadata_type[$handle] : false;
	}

}