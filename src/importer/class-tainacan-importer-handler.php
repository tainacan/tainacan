<?php 

namespace Tainacan;

class Importer_Handler {
	
	private $registered_importers = [];
	
	function __construct() {
		
		$this->bg_importer = new Background_Importer();

		add_action('init', array(&$this, 'init'));
		
	}

	public function init() {

		$this->register_importer([
			'name' => 'CSV',
			'description' => __('Import items from a CSV file to a chosen collection', 'tainacan'),
			'slug' => 'csv',
			'class_name' => '\Tainacan\Importer\CSV'
		]);
		$this->register_importer([
			'name' => 'Test',
			'description' => __('Create 2 test colletions with random items', 'tainacan'),
			'slug' => 'test',
			'class_name' => '\Tainacan\Importer\Test_Importer'
		]);

		$this->register_importer([
			'name' => 'Tainacan Legacy',
			'description' => __('Import structure from legacy version of Tainacan', 'tainacan'),
			'slug' => 'tainacan_old',
			'class_name' => '\Tainacan\Importer\Old_Tainacan'
		]);

		do_action('tainacan_register_importers');

	}
	
	function add_to_queue(\Tainacan\Importer\Importer $importer_object) {
		$data = $importer_object->_to_Array(true);
		$importer = $this->get_importer_by_object($importer_object);
		
		// Translators: The name of the importer process. E.g. CSV Importer, Legacy Tainacan Importer
		$importer_name = sprintf( __('%s Importer', 'tainacan'), $importer['name'] );
		
		$bg_process = $this->bg_importer->data($data)->set_name($importer_name)->save();
		if ( is_wp_error($bg_process->dispatch()) ) {
			return false;
		}
		return $bg_process;
		
	}

	/**
	 * Register Importer
	 * 
	 * @param array $importer 
	 * [
	 *  'slug' => 'example-importer',
	 *  'class_name' => '\Tainacan\Importer\Test_Importer'
	 * ]
	 */
	public function register_importer(array $importer) {
		$this->registered_importers[$importer['slug']] = $importer;
	}

	public function unregister_importer($importer_slug) {
		unset($this->registered_importers[$importer_slug]);
	}

	public function get_registered_importers() {
		return $this->registered_importers;
	}

	public function get_importer($importer_slug) {
		$importers = $this->get_registered_importers();
		if (isset($importers[$importer_slug])) {
			return $importers[$importer_slug];
		}
		return null;
	}

	function get_importer_by_object(\Tainacan\Importer\Importer $importer_object) {
		$class_name = get_class($importer_object);
		// add first bracket
		$class_name = '\\' . $class_name;
		$importers = $this->get_registered_importers();
		foreach ($importers as $importer) {
			if ($importer['class_name'] == $class_name)
				return $importer;
		}
		return null;
	}

	public function initialize_importer($importer_slug) {
		$importer = $this->get_importer($importer_slug);
		if ( is_array($importer) && isset($importer['class_name']) && class_exists($importer['class_name']) ) {
			return new $importer['class_name']();
		}
		return false;
	}
	
	
}

global $Tainacan_Importer_Handler;
$Tainacan_Importer_Handler = new Importer_Handler();

 ?>