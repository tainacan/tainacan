<?php

/**
 * Test Importer
 *
 * Example Importer class 
 *
 * used to learn how to write an importer and to 
 * create test collections and items
 *
 * Example how to invoke it
 *
 * add_action('init', function() {
 * 	if ( isset($_GET['run_test_importer']) && $_GET['run_test_importer'] == 'go' ) {
 * 		global $Tainacan_Importer_Handler;
 * 		$test = new \Tainacan\Importer\Test_Importer();
 * 		$Tainacan_Importer_Handler->add_to_queue($test);
 * 	}
 * });
 *
 * Put this code somewhere and access any URL of your site with ?run_test_importer=go
 * 
 * TODO: check validate() methods and write log & abort importer in case of error.
 */

namespace Tainacan\Importer;
use \Tainacan\Entities;

class Test_Importer extends Importer {
	
	protected $manual_mapping = false;
	protected $manual_collection = false;
	
	protected $steps = [
		
		[
			'name' => 'Create Taxonomies',
			'callback' => 'create_taxonomies'
		],
		[
			'name' => 'Create Collections',
			'callback' => 'create_collections'
		],
		[
			'name' => 'Import Items',
			'callback' => 'process_collections'
		],
		[
			'name' => 'Post-configure taxonomies',
			'callback' => 'close_taxonomies'
		],
		[
			'name' => 'Finalize',
			'callback' => 'finish_processing'
		]
		
	];
	
	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		
		$this->tax_repo = \Tainacan\Repositories\Taxonomies::get_instance();
		$this->col_repo = \Tainacan\Repositories\Collections::get_instance();
		$this->items_repo = \Tainacan\Repositories\Items::get_instance();
		$this->metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
		
	}
	
	public function create_taxonomies() {
		
		$tax1 = new Entities\Taxonomy();
		$tax1->set_name('Color');
		$tax1->set_allow_insert('yes');
		$tax1->set_status('publish');
		$tax1->validate();
		$tax1 = $this->tax_repo->insert($tax1);
		
		$this->add_transient('tax_1_id', $tax1->get_id());
		
		$tax2 = new Entities\Taxonomy();
		$tax2->set_name('Quality');
		$tax2->set_allow_insert('yes');
		$tax2->set_status('publish');
		$tax2->validate();
		$tax2 = $this->tax_repo->insert($tax2);
		
		$this->add_transient('tax_2_id', $tax2->get_id());
		
		return false;
		
	}
	
	public function create_collections() {
		
		$col1 = new Entities\Collection();
		$col1->set_name('Collection 1');
		$col1->set_status('publish');
		$col1->validate();
		$col1 = $this->col_repo->insert($col1);
		
		$col2 = new Entities\Collection();
		$col2->set_name('Collection 2');
		$col2->set_status('publish');
		$col2->validate();
		$col2 = $this->col_repo->insert($col2);
		
		$col1_map = [];
		$col2_map = [];
		
		// metadata
		
		// core metadata
		$col1_core_title = $col1->get_core_title_metadatum();
		$col1_core_description = $col1->get_core_description_metadatum();
		$col1_map[$col1_core_title->get_id()] = 'metadatum1';
		$col1_map[$col1_core_description->get_id()] = 'metadatum2';
		
		$metadatum = new Entities\Metadatum();
		$metadatum->set_name('Color');
		$metadatum->set_collection($col1);
		$metadatum->set_metadatum_type('Tainacan\Metadatum_Types\Category');
		$metadatum->set_metadatum_type_options([
			'taxonomy_id' => $this->get_transient('tax_1_id'),
			'allow_new_terms' => true
		]);
		$metadatum->set_status('publish');
		$metadatum->validate();
		$metadatum = $this->metadata_repo->insert($metadatum);
		$col1_map[$metadatum->get_id()] = 'metadatum3';
		$this->add_transient('tax_1_metadatum', $metadatum->get_id());
		
		
		$metadatum = new Entities\Metadatum();
		$metadatum->set_name('Quality');
		$metadatum->set_collection($col1);
		$metadatum->set_metadatum_type('Tainacan\Metadatum_Types\Category');
		$metadatum->set_metadatum_type_options([
			'taxonomy_id' => $this->get_transient('tax_2_id'),
			'allow_new_terms' => true
		]);
		$metadatum->set_status('publish');
		$metadatum->validate();
		$metadatum = $this->metadata_repo->insert($metadatum);
		$col1_map[$metadatum->get_id()] = 'metadatum4';
		$this->add_transient('tax_2_metadatum', $metadatum->get_id());
		
		$this->add_collection([
			'id' => $col1->get_id(),
			'map' => $col1_map,
			'total_items' => $this->get_col1_number_of_items(),
			'source_id' => 'col1'
		]);
		
		// Collection 2
		// core metadata
		$col2_core_title = $col2->get_core_title_metadatum();
		$col2_core_description = $col2->get_core_description_metadatum();
		$col2_map[$col2_core_title->get_id()] = 'metadatum1';
		$col2_map[$col2_core_description->get_id()] = 'metadatum2';
		
		$metadatum = new Entities\Metadatum();
		$metadatum->set_name('Test Metadatum');
		$metadatum->set_collection($col2);
		$metadatum->set_metadatum_type('Tainacan\Metadatum_Types\Text');
		$metadatum->set_status('publish');
		$metadatum->validate();
		$metadatum = $this->metadata_repo->insert($metadatum);
		$col2_map[$metadatum->get_id()] = 'metadatum3';
		
		$this->add_collection([
			'id' => $col2->get_id(),
			'map' => $col2_map,
			'total_items' => $this->get_col2_number_of_items(),
			'source_id' => 'col2'
		]);
		
		return false;
		
	}
	
	public function close_taxonomies() {
		
		$tax1 = $this->tax_repo->fetch( $this->get_transient('tax_1_id') );
		$tax1->set_allow_insert('no');
		$tax1->validate();
		$tax1 = $this->tax_repo->insert($tax1);
		
		$tax2 = $this->tax_repo->fetch( $this->get_transient('tax_2_id') );
		$tax2->set_allow_insert('no');
		$tax2->validate();
		$tax2 = $this->tax_repo->insert($tax2);
		
		
		$metadatum1 = $this->metadata_repo->fetch( $this->get_transient('tax_1_metadatum') );
		$options = $metadatum1->get_metadatum_type_options();
		$options['allow_new_terms'] = false;
		$metadatum1->set_metadatum_type_options($options);
		$metadatum1->validate();
		$this->metadata_repo->insert($metadatum1);
		
		$metadatum2 = $this->metadata_repo->fetch( $this->get_transient('tax_2_metadatum') );
		$options = $metadatum2->get_metadatum_type_options();
		$options['allow_new_terms'] = false;
		$metadatum2->set_metadatum_type_options($options);
		$metadatum2->validate();
		$this->metadata_repo->insert($metadatum2);
		
	}
	
	public function finish_processing() {
		
		// Lets just pretend we are doing something really important
		$important_stuff = 5;
		$current = $this->get_in_step_count();
		if ($current <= $important_stuff) {
			// This is very important
			sleep(5);
			$current ++;
			return $current;
		} else {
			return false;
		}
		
	}
	
	public function process_item($index, $collection_definition) {
		
		$method = 'get_' . $collection_definition['source_id'] . '_item';
		$item = $this->$method($index);
		return $item;
		
	}
	
	
	
	/**
	 * Dummy methods
	 *
	 * This could be reading from a file, or making requests to an API
	 *
	 * Here we are just returning random values
	 */
	public function get_col1_number_of_items() {
		return 10;
	}
	public function get_col2_number_of_items() {
		return 20;
	}
	public function get_col1_item($index) {
		
		$terms1 = [
			'orange', 'red', 'purple', 'blue', 'black', 'yellow'
		];
		$terms2 = [
			'good', 'awesome', 'disgusting', 'bad', 'horrible', 'regular'
		];
		
		return [
			'metadatum1' => 'Title ' . $index,
			'metadatum2' => 'Description ' . $index,
			'metadatum3' => $terms1[array_rand($terms1)],
			'metadatum4' => $terms2[array_rand($terms2)],
		];
	}
	public function get_col2_item($index) {
		return [
			'metadatum1' => 'Collection 2 item ' . $index,
			'metadatum2' => 'Collection 2 item description ' . $index,
			'metadatum3' => 'Collection 2 whatever ' . $index,
		];
	}
	
	
	
}