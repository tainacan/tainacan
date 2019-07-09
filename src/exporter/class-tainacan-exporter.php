<?php
namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

abstract class CommunImportExport {

	public function __construct( ) {
		if (!session_id()) {
			@session_start();
		}
		$this->id = uniqid();
		$author = get_current_user_id();
		if($author) {
			$this->add_transient('author', $author);
		}
	}

	/**
	 * The ID for this importer/exporter session
	 *
	 * When creating a new importer/exporter session via API, an id is returned and used to access this
	 * importer/exporter instance in the SESSION array
	 * 
	 * @var identifier
	 */
	protected $id;

	/**
	 * Stores the options for the importer/exporter. Each importer/exporter might use this property to save
	 * their own specific option
	 * @var array
	 */
	protected $options = [];

	/**
	 * Stores the default options for the importer/exporter options
	 * @var array
	 */
	protected $default_options = [];

	/**
	 * Declares what are the steps the importer/exporter will run, in the right order.
	 *
	 * By default, there is only one step, and the callback is the process_collections method 
	 * that process items for the collections in the collections array.
	 *
	 * Child classes may declare as many steps as they want and can keep this default step to use 
	 * this method for importer/exporter the items. But it is optional.
	 * 
	 * @var array
	 */
	protected $steps = [
		[
			'name' => 'Process Items',
			'progress_label' => 'Process Items',
			'callback' => 'process_collections'
		]
	];

	/**
	 * This array holds the structure that the default step 'process_collections' will handle.
	 *
	 * Its an array of the target collections, with their IDs, an identifier from the source, the total number of items to be importer/exporter, the mapping array 
	 * from the source structure to the ID of the metadata metadata in tainacan
	 *
	 * The format of the map is an array where the keys are the metadata IDs of the destination collection and the 
	 * values are the identifier from the source. This could be an ID or a string or whatever the importer/exporter finds appropriate to handle
	 *
	 * The source_id can be anyhting you like, that helps you relate this collection to your source.
	 * 
	 * Example of the structure of this propery for one collection:
	 * 0 => [
	 * 	'id' => 12,
	 * 	'total_items' => 1234,
	 * ],
	 *
	 * use add_collection() and remove_collection() to interact with thiis array.
	 *
	 * 
	 * @var array
	 */
	protected $collections = [];

	public function add_collection(array $collection) {
		if (isset($collection['id'])) {
			$this->remove_collection($collection['id']);
			$this->collections[] = $collection;
			$this->collections = array_values($this->collections);
		}
	}

	public function remove_collection($col_id) {
		foreach ($this->get_collections() as $index => $col) {
			if ($col['id'] == $col_id) {
				unset($this->collections[$index]);
				break;
			}
		}
	}
	
	public function update_collection($index, $collection_definition) {
		if (isset($this->collections[$index]))
			$this->collections[$index] = $collection_definition;
	}
	
	public function update_current_collection($collection_definition) {
		$current_collection = $this->get_current_collection();
		return $this->update_collection($current_collection, $collection_definition);
	}

	public function next_item() {
		$current_collection = $this->get_current_collection();
		$current_collection_item = $this->get_current_collection_item();
		$collections = $this->get_collections();
		$collection = $collections[$current_collection];
		$current_collection_item ++;
		$this->set_current_collection_item($current_collection_item);
		if ($current_collection_item >= $collection['total_items']) {
			return $this->next_collection();
		}
		return $current_collection_item;
	}

	public function next_collection() {
		$current_collection = $this->get_current_collection();
		$collections = $this->get_collections();
		$this->set_current_collection_item(0);
		$current_collection ++;
		if (isset($collections[$current_collection])) {
			$this->set_current_collection($current_collection);
			return $current_collection;
		}
		return false;
	}

	/**
	 * Transients is used to store temporary data to be used accross multiple requests
	 *
	 * Add and remove transient data using add_transient() and delete_transient() methods
	 *
	 * Transitens can be strings, numbers or arrays. Avoid storing objects.
	 * 
	 * @var array
	 */
	protected $transients = [];

	/**
	 * Wether to abort importer/exporter execution.
	 * @var bool
	 */
	protected $abort = false;

	protected $current_step = 0;
	protected $in_step_count = 0;

	protected $current_collection = 0;
	protected $current_collection_item = 0;

	protected $log = [];
	protected $error_log = [];

	/**
	 * List of attributes that are saved in DB and that are used to 
	 * reconstruct the object, this property need be overwrite in custom import/export.
	 * @var array
	 */
	protected $array_attributes = [
		'in_step_count',
		'current_step',
		'transients',
		'options',
		'collections',
	];

	/**
	 * @return string
	 */
	public function get_id(){
		return $this->id;
	}

	public function get_current_step() {
		return $this->current_step;
	}
	
	public function set_current_step($value) {
		$this->current_step = $value;
	}

	public function get_in_step_count() {
		return $this->in_step_count;
	}

	public function set_in_step_count($value) {
		$this->in_step_count = $value;
	}

	public function get_current_collection() {
		return $this->current_collection;
	}

	public function set_current_collection($value) {
		$this->current_collection = $value;
	}

	public function get_current_collection_item() {
		return $this->current_collection_item;
	}

	public function set_current_collection_item($value) {
		$this->current_collection_item = $value;
	}

	public function get_collections() {
		return $this->collections;
	}

	public function set_collections($value) {
		$this->collections = $value;
	}
	
	public function get_current_collection_object() {
		$current_collection = $this->get_current_collection();
		$collections = $this->get_collections();
		if ( isset( $collections[$current_collection] ) && isset( $collections[$current_collection]['id'] ) ) {
			return \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $collections[$current_collection]['id'] );
		}
		return false;
	}

	/**
	 * Gets the options for this import/export, including default values for options
	 * that were not set yet.
	 * @return array import/export options
	 */
	public function get_options() {
		return array_merge($this->default_options, $this->options);
	}

	/**
	 * Set the options array
	 * @param array $options 
	 */
	public function set_options($options) {
		$this->options = $options;
	}

	/**
	 * Gets one option from the options array.
	 *
	 * Checks if option exist or if it have a default value. Otherwise return an empty string
	 * 
	 * @param  string $key the desired option
	 * @return mixed the option value, the default value or an empty string
	 */
	public function get_option($key) {
		$options = $this->get_options();
		return isset($options[$key]) ? $options[$key] : '';
	}

	/**
	 * Set the default options values.
	 *
	 * Must be called from the __construct method of the child import/export class to set default values.
	 * 
	 * @param array $options 
	 */
	protected function set_default_options($options) {
		$this->default_options = $options;
	}

	public function set_steps($steps) {
		$this->steps = $steps;
	}

	public function get_steps() {
		return $this->steps;
	}

	protected function get_transients() {
		return $this->transients;
	}

	protected function set_transients(array $data) {
		$this->transients = $data;
	}

	public function add_transient($key, $data) {
		$this->transients[$key] = $data;
	}

	public function delete_transient($key) {
		if (isset($this->transients[$key]))
			unset($this->transients[$key]);
	}

	public function get_transient($key) {
		if (isset($this->transients[$key]))
			return $this->transients[$key];
		return null;
	}

	public function get_log() {
		return $this->log;
	}

	public function get_error_log() {
		return $this->error_log;
	}

	public function add_log($message ) {
		$this->log[] = $message;
	}

	public function add_error_log($message ) {
		$this->error_log[] = $message;
	}

	public function is_finished() {
		if($this->current_step >= count($this->steps)) {
			return true;
		}
		return false;
	}

	/**
	 * Cancel Scheduled abortion at the end of run()
	 * @return void
	 */
	protected function cancel_abort() {
		$this->abort = false;
	}

	/**
	 * Schedule importer abortion at the end of run()
	 * @return void
	 */
	protected function abort() {
		$this->abort = true;
	}

	/**
	 * Return wether importer should abort execution or not
	 * @return bool 
	 */
	public function get_abort() {
		return $this->abort;
	}

	/**
	 * Gets the current label to be displayed below the progress bar to give
	 * feedback to the user.
	 * 
	 * It automatically gets the attribute progress_label from the current step running.
	 * 
	 * Importers/Exporters may change this label whenever they want
	 * 
	 * @return string
	 */
	public function get_progress_label() {
		$current_step = $this->get_current_step();
		$steps = $this->get_steps();
		$label = '';
		if ( isset($steps[$current_step]) ) {
			$step = $steps[$current_step];
			if ( isset($step['progress_label']) ) {
				$label = $step['progress_label'];
			} elseif ( isset($step['name']) ) {
				$label = $step['name'];
			}
		}

		if ( sizeof($steps) > 1 ) {
			$preLabel = sprintf( __('Step %d of %d', 'tainacan'), $current_step + 1, sizeof($steps) );
			$label = $preLabel . ': ' . $label;
		}

		if ( empty($label) ) {
			$label = __('Running Importer', 'tainacan');
		}

		return $label;
	}

	/**
	 * Gets the current value to build the progress bar and give feedback to the user
	 * on the background process that is running the importer.
	 * 
	 * It does so by comparing the "size" attribute with the $in_step_count class attribute
	 * where size indicates the total size of iterations the step will take and $this->in_step_count 
	 * is the current iteration.
	 * 
	 * For the step with "process_items" as a callback, this method will look for the the $this->collections array
	 * and sum the value of all "total_items" attributes of each collection. Then it will look for 
	 * $this->get_current_collection and $this->set_current_collection_item to calculate the progress.
	 * 
	 * The value must be from 0 to 100
	 * 
	 * If a negative value is passed, it is assumed that the progress is unknown
	 */
	public function get_progress_value() {
		$current_step = $this->get_current_step();
		$steps = $this->get_steps();
		$value = -1;

		if ( isset($steps[$current_step]) ) {
			$step = $steps[$current_step];
			if ($step['callback'] == 'process_collections') {
				$totalItems = 0;
				$currentItem = $this->get_current_collection_item();
				$current_collection = $this->get_current_collection();
				$collections = $this->get_collections();
				foreach ($collections as $i => $col) {
					if ( isset($col['total_items']) && is_integer($col['total_items']) ) {
						$totalItems += $col['total_items'];
						if ($i < $current_collection) {
							$currentItem += $col['total_items'];
						}
					}
				}
				if ($totalItems > 0) {
					$value = round( ($currentItem/$totalItems) * 100 );
				}
			} else {
				if ( isset($step['total']) && is_integer($step['total']) && $step['total'] > 0 ) {
					$current = $this->get_in_step_count();
					$value = round( ($current/$step['total']) * 100 );
				}
			}
		}
		return $value;
	}

	/**
	 * Sets the total attribute for the current step
	 * 
	 * The "total" attribute of a step indicates the number of iterations this step will take to complete.
	 * 
	 * The iteration is counted using $this->in_step_count attribute, and comparing the two values gives us
	 * the current progress of the process.
	 * 
	 */
	protected function set_current_step_total($value) {
		$this->set_step_total($this->get_current_step(), $value);
	}

	/**
	 * Sets the total attribute for a given step
	 * 
	 * The "total" attribute of a step indicates the number of iterations this step will take to complete.
	 * 
	 * The iteration is counted using $this->in_step_count attribute, and comparing the two values gives us
	 * the current progress of the process.
	 * 
	 */
	protected function set_step_total($step, $value) {
		$steps = $this->get_steps();
		if (isset($steps[$step]) && is_array($steps[$step])) {
			$steps[$step]['total'] = $value;
			$this->set_steps($steps);
		}
	}

	protected function next_step() {
		$current_step = $this->get_current_step();
		$steps = $this->get_steps();
		$current_step ++;
		$this->set_current_step($current_step);
		if (isset($steps[$current_step])) {
			return $current_step;
		}
		return false;
	}

	// Abstract methods
	abstract public function _to_Array($short = false);

}

////////////////////////////////////
class Exporter extends CommunImportExport {
	
	private $output_files = [];
	private $mapping_accept = [
		'any'  => true,
		'list' => false
	];
	private $send_email = null;
	protected $mapping_list = [];
	public $mapping_selected = "";
	protected $accept_no_mapping = true;

	protected $steps = [
		[
			'name' => 'Begin Exporter',
			'progress_label' => 'Begin Exporter Process',
			'callback' => 'begin_exporter'
		],[
			'name' => 'Process Items',
			'progress_label' => 'Process Items',
			'callback' => 'process_collections'
		],[
			'name' => 'End Exporter',
			'progress_label' => 'End Exporter Process',
			'callback' => 'end_exporter'
		]
	];

	public function __construct($attributess = array()) {
		$this->array_attributes = array_merge($this->array_attributes, [
			'mapping_selected', 
			'current_collection_item', 
			'current_collection',
			'output_files',
			'send_email'
		]);
		parent::__construct();
		$_SESSION['tainacan_exporter'][$this->get_id()] = $this;
		if (!empty($attributess)) {
			foreach ($attributess as $attr => $value) {
				$method = 'set_' . $attr;
				if (method_exists($this, $method)) {
					$this->$method($value);
				}
			}
		}
	}

	public function _to_Array($short = false) {
		$return = ['id' => $this->get_id()];
		foreach ($this->array_attributes as $attr) {
			$method = 'get_' . $attr;
			$return[$attr] = $this->$method();
		}
		$return['class_name'] = get_class($this);

		global $Tainacan_Exporter_Handler;
		$exporter_definition = $Tainacan_Exporter_Handler->get_exporter_by_object($this);

		if ($short === false) {
			$return['manual_collection']	= $exporter_definition['manual_collection'];
			$return['mapping_accept']		= $this->mapping_accept;
			$return['mapping_list'] 		= $this->mapping_list;
			$return['accept_no_mapping'] 	= $this->accept_no_mapping;
			$return['options_form'] 		= $this->options_form();
		}

		return $return;
	}

	public function add_collection(array $collection) {
		// if (!isset($collection['total_items'])) {
		// 	$collection['total_items'] = 10;
		// }
		parent::add_collection($collection); 
	}
	
	/**
	* Gets the current mapper object, if one was chosen by the user, false Otherwise
	*/
	public function get_current_mapper() {
		return \Tainacan\Mappers_Handler::get_instance()->get_mapper($this->get_mapping_selected());
	}
	
	
	/**
	 * Method implemented by child importer/exporter to return the HTML of the Options Form to be rendered in the Importer page
	 */
	public function options_form() {}

	/**
	 * Default methods to Export
	 * process an item from the collections queue
	 */
	public function process_collections() {
		$current_collection = $this->get_current_collection();
		$collections = $this->get_collections();
		$collection_definition = isset($collections[$current_collection]) ? $collections[$current_collection] : false;
		$current_collection_item = $this->get_current_collection_item();

		if ( !$collection_definition || !is_array($collection_definition) || !isset($collection_definition['id']) ) {
			$this->add_error_log('Collection misconfigured');
			return false;
		}
		$this->add_log('Processing item ' . $current_collection_item);

		$this->process_header($current_collection_item, $collection_definition);

		$processed_items = $this->get_items($current_collection_item, $collection_definition);
		foreach ($processed_items as $processed_item) {
			$this->process_item( $processed_item['item'], $processed_item['metadata'] );
		}

		$this->process_footer($current_collection_item, $collection_definition);
		return $this->next_item();
	}

	public function process_item( $item, $metadata ) {
		
	}

	private function process_header($current_collection_item, $collection_definition) {
		if ($current_collection_item == 0) {
			$this->output_header();
		}
	}

	public function output_header() {
		$this->append_to_file('exporter', "[header] \n");
		return false;
	}

	private function process_footer($current_collection_item, $collection_definition) {
		if ($current_collection_item == $collection_definition['total_items']-1) {
			$this->output_footer();
		}
	}

	public function output_footer() {
		$this->append_to_file('exporter', "[footer] \n");
		return false;
	}

	private function get_items($index, $collection_definition) {
		$collection_id = $collection_definition['id'];
		$tainacan_items = \Tainacan\Repositories\Items::get_instance();
		
		$filters = [
			'posts_per_page' => 1,
			'paged'   => $index+1,
			'order'   => 'DESC',
			'orderby' => 'ID'
		];

		$this->add_log('Proccessing item index ' . $index . ' in collection ' . $collection_definition['id'] );
		$items = $tainacan_items->fetch($filters, $collection_id, 'WP_Query');
		
		if ( !isset($collection_definition['total_items']) ) {
			$collection_definition['total_items'] = $items->found_posts;
			$this->update_current_collection($collection_definition);
		}
		
		
		$data = [];
		while ($items->have_posts()) {
			$items->the_post();
			$item = new Entities\Item($items->post);
			
			if ($item instanceof \Tainacan\Entities\Item ) {
				$data[] = [
					'metadata' =>$this->map_item_metadata($item),
					'item' => $item
				];
			} else {
				$this->add_error_log( __('Error processing item', 'tainacan') );
			}
						
		}
		wp_reset_postdata();
		return $data;
	}
	
	/**
	* Gets an Item as input and return an array of ItemMetadataObjects
	* If a mapper is selected, the array keys will be the slugs of the metadata 
	* declared by the mapper, in the same order. 
	* Note that if one of the metadata is not mapped, this array item will be null 
	*/
	private function map_item_metadata(\Tainacan\Entities\Item $item) {
		
		$mapper = $this->get_current_mapper();
		$metadata = $item->get_metadata();
		if (!$mapper) {
			return $metadata;
		}
		$pre = [];
		foreach ($metadata as $item_metadata) {
			$metadatum = $item_metadata->get_metadatum();
			$meta_mappings = $metadatum->get_exposer_mapping();
			if ( array_key_exists($this->get_mapping_selected(), $meta_mappings) ) {
				
				$pre[ $meta_mappings[$this->get_mapping_selected()] ] = $item_metadata;
			}
		}
		
		// reorder
		$return = [];
		foreach ( $mapper->metadata as $meta_slug => $meta ) {
			if ( array_key_exists($meta_slug, $pre) ) {
				$return[$meta_slug] = $pre[$meta_slug];
			} else {
				$return[$meta_slug] = null;
			}
		}
		
		return $return;
		
	}

	public function add_new_file($key) {
		$upload_dir_info = wp_upload_dir();
		$prefix = $this->get_id();
		$upload_dir = trailingslashit( $upload_dir_info['basedir'] );
		$upload_url = trailingslashit( $upload_dir_info['baseurl'] );
		$exporter_folder = 'tainacan/exporter';

		if (!is_dir($upload_dir . $exporter_folder)) {
			if (!mkdir($upload_dir . $exporter_folder)) {
				return false;
			}
		}
		$file_name = "$upload_dir$exporter_folder/$prefix$key";
		$file_url = "$upload_url$exporter_folder/$prefix$key";
		$this->output_files[$key] = [
			'filename' => $file_name,
			'url' => $file_url
		];
	}

	/**
	* Append content to a file. Create the file if it does not exist 
	* 
	* @param string $key The file identifier. (it is the name of the file, with extension, and will be prefixed with the process ID)
	* @param string $data The content to be appended to the file
	*/
	public function	append_to_file($key, $data) {
		if ( array_key_exists ( $key , $this->output_files ) ) {
			$fp = fopen($this->output_files[$key]['filename'], 'a');
			fwrite($fp, $data);
			fclose($fp);
		} else { // será?
			$this->add_new_file($key);
			$this->append_to_file($key, $data);
		}
	}
	
	/**
	* Method called by Exporters classes to set accepted mapping method
	* 
	* @param string $method THe accepted methods. any or list. If list, Exporter must also inform 
	* default mapper and the list of accepted mappers 
	* @param string $default_mapping The default mapping method. Required if list is chosen 
	* @param array $list List of accepted mapping methods 
	*/
	public function set_accepted_mapping_methods($method, $default_mapping = '', $list = []) {
		if ( array_key_exists($method, $this->mapping_accept) ) {
			foreach ($this->mapping_accept as &$value) {
				$value = false;
			}
			$this->mapping_accept[$method] = true;
			if($method == 'any') {
				$Tainacan_Exposers = \Tainacan\Mappers_Handler::get_instance();
				$metadatum_mappers = $Tainacan_Exposers->get_mappers();
				$this->mapping_list = array_keys($metadatum_mappers);
			} else if(!empty($list)) {
				$this->mapping_list = $list;
			}
			return true;
		}
		return false;
	}

	public function set_mapping_selected($mapping_selected) {
		$this->mapping_selected = $mapping_selected;
	}
	
	public function get_mapping_selected() {
		return $this->mapping_selected;
	}

	public function set_send_email($email) {
		$this->send_email = $email;
	}
	public function get_send_email() {
		return $this->send_email;
	}
	
	// Exporters should override
	public function get_output() {
		return '';
	}

	public function finished() {
		if($this->get_send_email() == 1) {
			$author = $this->get_transient('author');
			$user = get_userdata( (int) $author );
			if ($user instanceof \WP_User) {
				$msg = $this->get_output();
				$this->add_log('Sending email to ' . $user->user_email);
				wp_mail($user->user_email, __('Finished export.', 'tainacan'), $msg);
			}
			
		}
	}

	public function begin_exporter() {
		return false;
	}

	public function end_exporter() {
		return false;
	}

	private function set_output_files($output_files) {
		$this->output_files = $output_files;
	}
	protected function get_output_files() {
		return $this->output_files;
	}
	/**
	 * runs one iteration
	 */
	public function run() {
		if ($this->is_finished()) {
			$this->finished();
			return false;
		}
		$steps = $this->get_steps();
		$current_step = $this->get_current_step();
		$method_name = $steps[$current_step]['callback'];

		if (method_exists($this, $method_name)) {
			$author = $this->get_transient('author');
			$this->add_log('User in process: ' . $author);
			wp_set_current_user($author);
			$result = $this->$method_name();
		} else {
			$this->add_error_log( 'Callback not found for step ' . $steps[$current_step]['name']);
			$result = false;
		}
		if($result === false || (!is_numeric($result) || $result < 0)) {
			//Move on to the next step
			$this->set_in_step_count(0);
			$return = $this->next_step();
		} else if(is_numeric($result) && $result > 0) {
			$this->set_in_step_count($result);
			$return = $result;
		}
		
		if (false === $return) {
			$this->finished();
		}
		
		return $return;
	}
}