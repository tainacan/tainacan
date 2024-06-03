<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class FilterTypeHelper
 */
class Filter_Type_Helper {

	private static $instance = null;
	/**
	 * Stores external filter type available to be used in Tainacan
	 */
	private $registered_filter_type;
	private $Tainacan_Filters;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->registered_filter_type = [];
		$this->Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();
		$this->init();
	}

	private function init() {
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Date');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Numeric');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Taginput');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Checkbox');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Selectbox');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Autocomplete');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Date_Interval');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Dates_Intersection');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Numeric_Interval');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\TaxonomyTaginput');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\TaxonomyCheckbox');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Numeric_List_Interval');
		$this->Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Numerics_Intersection');

		// the priority should see less than on function 
		// `load_admin_page()` of class `Admin` in file /src/views/class-tainacan-admin.php
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_filter_type_compoment' ), 80 );
		do_action('tainacan-register-filter-type', $this);
	}

	public function register_filter_type_compoment() {
		foreach($this->registered_filter_type as $handle => $component) {
			wp_enqueue_script($handle, $component['script_path']);
		}
	}

	/**
	 * Register a new Filter Type
	 * 
	 * @param string $handle name of the component. Should be unique.
	 * @param string $class a class name of the filter type
	 * @param array|string $args
	 */
	public function register_filter_type($handle, $class_name, $script_path, $args = []) {
		global $TAINACAN_EXTRA_SCRIPTS;

		$this->Tainacan_Filters->register_filter_type($class_name);
		if ( ! in_array( $handle, $this->registered_filter_type ) ) {
			$TAINACAN_EXTRA_SCRIPTS[] = $handle;

			$this->registered_filter_type[$handle] = [
				'handle' => $handle,
				'class_name' => $class_name,
				'script_path' => $script_path, 
				'args' => $args
			];
		}

	}

	/**
	 * Get a list of all registered filter type
	 * 
	 * @return array The list of registered filter type
	 */
	public function get_registered_filter_type() {
		return $this->registered_filter_type;
	}

	/**
	 * Get one specific filter type by its slug
	 * 
	 * @param string $handle Name of the component
	 * 
	 * @return array|false The filter type definition or false if it is not found
	 */
	public function get_filter_type($handle) {
		return isset($this->registered_filter_type[$handle]) ? $this->registered_filter_type[$handle] : false;
	}

}