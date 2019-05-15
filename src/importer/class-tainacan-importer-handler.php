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
			'class_name' => '\Tainacan\Importer\CSV',
			'manual_collection' => true,
			'manual_mapping' => true,
		]);
		
		$this->register_importer([
			'name' => 'Test',
			'description' => __('Create 2 test colletions with random items', 'tainacan'),
			'slug' => 'test',
			'class_name' => '\Tainacan\Importer\Test_Importer',
			'manual_collection' => false,
			'manual_mapping' => false,
		]);

		$this->register_importer([
			'name' => 'Tainacan Legacy',
			'description' => __('Import structure from legacy version of Tainacan', 'tainacan'),
			'slug' => 'tainacan_old',
			'class_name' => '\Tainacan\Importer\Old_Tainacan',
			'manual_collection' => false,
			'manual_mapping' => false,
		]);

		$this->register_importer([
			'name' => 'Vocabulary CSV',
			'description' => __('Import a vaculary from a CSV file into a taxonomy', 'tainacan'),
			'slug' => 'terms',
			'class_name' => '\Tainacan\Importer\Term_Importer',
			'manual_collection' => false,
			'manual_mapping' => false,
		]);

        $this->register_importer([
            'name' => 'OAI-PMH (Experimental)',
            'description' => __('Import items from an OAI-PMH data source', 'tainacan'),
            'slug' => 'oaipmh_importer',
            'class_name' => '\Tainacan\Importer\Oaipmh_Importer',
            'manual_collection' => false,
            'manual_mapping' => false,
        ]);

        $this->register_importer([
            'name' => 'Youtube (Experimental)',
            'description' => __('Import items from an Youtube URL', 'tainacan'),
            'slug' => 'youtube_importer',
            'class_name' => '\Tainacan\Importer\Youtube_Importer',
            'manual_collection' => true,
            'manual_mapping' => true,
        ]);

        $this->register_importer([
            'name' => 'Flickr (Experimental)',
            'description' => __('Import items from an Flickr URL', 'tainacan'),
            'slug' => 'flickr_importer',
            'class_name' => '\Tainacan\Importer\Flickr_Importer',
            'manual_collection' => true,
            'manual_mapping' => true,
        ]);

		do_action('tainacan_register_importers');

		add_action( 'tainacan-enqueue-admin-scripts', array($this, 'enqueue_scripts') );
	}

	function enqueue_scripts() {
	 	global $TAINACAN_BASE_URL;
	 	wp_enqueue_script('import_term_csv_script', $TAINACAN_BASE_URL . '/importer/term-importer/js/term.js', false, TAINACAN_VERSION, true);
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
	 * 
	 * 
	 * @param array $importer {
	 *     Required. Array or string of arguments describing the importer
	 * 
	 * 	   @type string		 $name					The name of the importer. e.g. 'Example Importer'
	 * 	   @type string		 $slug					A unique slug for the importer. e.g. 'This is an example importer description'
	 * 	   @type string		 $description			The importer description. e.g. 'example-importer'
	 * 	   @type string		 $class_name			The Importer Class. e.g. '\Tainacan\Importer\Test_Importer'
	 * 	   @type bool		 $manual_mapping		Wether Tainacan must present the user with an interface to manually map 
	 * 												the metadata from the source to the target collection.
	 *
	 * 												If set to true, Importer Class must implement the method 
	 * 												get_source_metadata() to return the metadatum found in the source.
	 *												
	 * 												Note that this will only work when importing items to one single collection.
	 * 
	 * 	   @type bool		 $manual_collection		Wether Tainacan will let the user choose a destination collection.
	 *
	 * 												If set to true, the API endpoints will handle Collection creation and will assign it to 
	 * 												the importer object using add_collection() method.
	 *												
	 * 												Otherwise, the child importer class must create the collections and add them to the collections property also 
	 * 												using add_collection()
	 * 
	 */
	public function register_importer(array $importer) {
		
		$defaults = [
			'manual_mapping' => false,
			'manual_collection' => true
		];

		$attrs = wp_parse_args($importer, $defaults);

		if (!isset($attrs['slug']) || !isset($attrs['class_name']) || !isset($attrs['name'])) {
			return false;
		}
		
		$this->registered_importers[$importer['slug']] = $attrs;

		return true;
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