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
			'name' => 'Test Importer',
			'description' => __('Create 2 test colletions with random items', 'tainacan'),
			'slug' => 'test',
			'class_name' => '\Tainacan\Importer\Test_Importer'
		]);

		do_action('tainacan_register_importers');

	}
	
	function add_to_queue(\Tainacan\Importer\Importer $importer_object) {
		$data = $importer_object->_to_Array(true);
		$importer_object = $this->bg_importer->data($data)->save()->dispatch();
		return $importer_object;
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