<?php 

namespace Tainacan;

class Export_Handler {
	
	private $registered_exporters = [];
	
	function __construct() {
		$this->bg_exporter = new Background_Exporter();
		add_action('init', array(&$this, 'init'));
	}

	public function init() {

		$this->register_exporter([
			'name' => 'CSV',
			'description' => __('Allows you to export one collection to a CSV file', 'tainacan'),
			'slug' => 'csv',
			'class_name' => '\Tainacan\Exporter\CSV'
		]);

        $this->register_exporter([
            'name' => 'Vocabulary CSV',
            'description' => __('Allows you to export a taxonomy to a CSV file', 'tainacan'),
            'slug' => 'vocabularycsv',
            'class_name' => '\Tainacan\Exporter\Term_Exporter',
            'manual_mapping' => false,
            'manual_collection' => false
        ]);
		
		do_action('tainacan_register_exporters');

		add_action( 'tainacan-enqueue-admin-scripts', array($this, 'enqueue_scripts') );
	}

	function enqueue_scripts() {
		return null;
	}

	public function register_exporter(array $exporter) {
		
		$defaults = [ //isso aqui vai mudar de acordo com a opção de exportação a se utilizada!
			'manual_mapping' => false,
			'manual_collection' => true
		];

		$attrs = wp_parse_args($exporter, $defaults);

		if (!isset($attrs['slug']) || !isset($attrs['class_name']) || !isset($attrs['name'])) {
			return false;
		}
		
		$this->registered_exporters[$exporter['slug']] = $attrs;

		return true;
	}
	
	function add_to_queue(\Tainacan\Exporter\Exporter $exporter_object) {
		$data = $exporter_object->_to_Array(true);
		$exporter = $this->get_exporter_by_object($exporter_object);
		
		$exporter_name = sprintf( __('%s Exporter', 'tainacan'), $exporter['name'] );
		
		$bg_process = $this->bg_exporter->data($data)->set_name($exporter_name)->save();
		if ( is_wp_error($bg_process->dispatch()) ) {
			return false;
		}
		return $bg_process;
	}

	public function unregister_exporter($slug) {
		unset($this->registered_exporters[$slug]);
	}

	public function get_registered_exporters() {
	 	return $this->registered_exporters;
	}

	public function get_exporter($slug) {
		$exporters = $this->get_registered_exporters();
		if (isset($exporters[$slug])) {
			return $exporters[$slug];
		}
		return null;
	}

	function get_exporter_by_object(\Tainacan\exporter\Exporter $exporter_object) {
		$class_name = get_class($exporter_object);
		$class_name = '\\' . $class_name;
		$exporters = $this->get_registered_exporters();
		foreach ($exporters as $exporter) {
			if ($exporter['class_name'] == $class_name)
				return $exporter;
		}
		return null;
	}

	public function initialize_exporter($slug) {
		$exporter = $this->get_exporter($slug);
		if ( is_array($exporter) && isset($exporter['class_name']) && class_exists($exporter['class_name']) ) {
			return new $exporter['class_name']();
		}
		return false;
	}
}

global $Tainacan_Exporter_Handler;
$Tainacan_Exporter_Handler = new Export_Handler();
 ?>