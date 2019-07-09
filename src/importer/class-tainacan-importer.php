<?php
namespace Tainacan\Importer;
use Tainacan;
use Tainacan\Entities;

abstract class Importer {

    /**
     * The ID for this importer session
     *
     * When creating a new importer session via API, an id is returned and used to access this
     * importer instance in the SESSION array
     * 
     * @var identifier
     */
	private $id;
    
	/**
	 * The path to the temporary file created when user uploads a file
	 * @var string
	 */
	protected $tmp_file;
	
    
	/**
	 * This array holds the structure that the default step 'process_collections' will handle.
	 *
	 * Its an array of the target collections, with their IDs, an identifier from the source, the total number of items to be imported, the mapping array 
	 * from the source structure to the ID of the metadata metadata in tainacan
	 *
	 * The format of the map is an array where the keys are the metadata IDs of the destination collection and the 
	 * values are the identifier from the source. This could be an ID or a string or whatever the importer finds appropriate to handle
	 *
	 * The source_id can be anyhting you like, that helps you relate this collection to your source.
	 * 
	 * Example of the structure of this propery for one collection:
	 * 0 => [
	 * 	'id' => 12,
	 * 	'mapping' => [
	 * 	  30 => 'column1'
	 * 	  31 => 'column2'
	 * 	],
	 * 	'total_items' => 1234,
	 * 	'source_id' => 55
	 * ],
	 *
	 * use add_collection() and remove_collection() to interact with thiis array.
	 *
	 * 
	 * @var array
	 */
	protected $collections = [];
    
	/**
	 * Stores the options for the importer. Each importer might use this property to save
	 * their own specific option
	 * @var array
	 */
	private $options = [];
	
	/**
	 * Stores the default options for the importer options
	 * @var array
	 */
	protected $default_options = [];
	
	private $accepts = [
		'file' => true,
		'url'  => false,
	];
	
	/**
	 * Declares what are the steps the importer will run, in the right order.
	 *
	 * By default, there is only one step, and the callback is the process_collections method 
	 * that process items for the collections in the collections array.
	 *
	 * Child classes may declare as many steps as they want and can keep this default step to use 
	 * this method for import the items. But it is optional.
	 * 
	 * @var array
	 */
	protected $steps = [
		[
			'name' => 'Import Items',
			'progress_label' => 'Importing Items',
			'callback' => 'process_collections'
		]
	];
	
	/**
	 * Transients is used to store temporary data to be used accross multiple requests
	 *
	 * Add and remove transient data using add_transient() and delete_transient() methods
	 *
	 * Transitens can be strings, numbers or arrays. Avoid storing objects.
	 * 
	 * @var array
	 */
	private $transients = [];

	private $current_step = 0;
	
	private $in_step_count = 0;
	
	private $current_collection = 0;
	
	private $current_collection_item = 0;

	private $url = '';
	
	private $log = [];
	
	private $error_log = [];
	
	/**
	 * Wether to abort importer execution.
	 * @var bool
	 */
	private $abort = false;
	
	/**
	 * List of attributes that are saved in DB and that are used to 
	 * reconstruct the object 
	 * @var array
	 */
	private $array_attributes = [
		'url',
		'current_collection_item',
		'current_collection',
		'in_step_count',
		'current_step',
		'transients',
		'options',
		'collections',
		'tmp_file'
	];

    public function __construct($attributess = array()) {
        if (!session_id()) {
            @session_start();
        }

		$this->id = uniqid();

		$author = get_current_user_id();
		if($author){
			$this->add_transient('author', $author);
		}

        $_SESSION['tainacan_importer'][$this->get_id()] = $this;
		
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

		global $Tainacan_Importer_Handler;
		$importer_definition = $Tainacan_Importer_Handler->get_importer_by_object($this);

		if ($short === false) {
			$return['manual_collection'] = $importer_definition['manual_collection'];
			$return['manual_mapping'] = $importer_definition['manual_mapping'];
			$return['accepts'] = $this->accepts;
			$return['options_form'] = $this->options_form();
		}

		return $return;
	}
	
	/////////////////////
	// Getters and setters
	
    /**
     * @return string
     */
    public function get_id(){
        return $this->id;
    }

    /**
     * Set URL
     * @param $url string
     * @return bool
     */
    public function set_url($url)
    {
        if(!empty($url) && !is_array($url))
        {
            $this->url = rtrim(trim($url), "/");
            return true;
        }

        return false;
    }

    /**
     * @return string  or bool
     */
    public function get_url()
    {
        if(!empty($this->url))
        {
            return $this->url;
        }

        return false;
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
	
	public function get_tmp_file(){
        return $this->tmp_file;
    }
	
	public function set_tmp_file($filepath){
        $this->tmp_file = $filepath;
    }
	
	public function get_collections() {
		return $this->collections;
	}
	
	public function set_collections($value) {
		$this->collections = $value;
	}
	
	/**
     * Gets the options for this importer, including default values for options
     * that were not set yet.
     * @return array Importer options
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
	 * Set the default options values.
	 *
	 * Must be called from the __construct method of the child importer class to set default values.
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
	
	
	private function get_transients() {
		return $this->transients;
	}
	
	private function set_transients(array $data) {
		$this->transients = $data;
	}
	
	public function get_log() {
		return $this->log;
	}
	
	public function get_error_log() {
		return $this->error_log;
	}
	
	////////////////////////////////////
	// Utilities
	

    /**
     * @param $file File to be managed by importer
     * @return bool
     */
    public function add_file( $file ){
        $new_file = $this->upload_file( $file );
        if ( is_numeric( $new_file ) ) {
			$this->tmp_file = get_attached_file( $new_file );
			return true;
        } else {
            return false;
        }
    }
	
	
    /**
     * log the actions from importer
     *
     * @param $type
     * @param $messagelog
     */
    public function add_log($message ){
        $this->log[] = $message;
    }
	public function add_error_log($message ){
        $this->error_log[] = $message;
    }
	
	public function add_collection(array $collection) {
		if (isset($collection['id'])) {
			$this->remove_collection($collection['id']);
			$this->collections[] = $collection;
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

    /**
     * internal function to upload the file
     *
     * @param $path_file
     * @return array $response
     */
    private function upload_file( $file_array ){
        //$name = basename( $path_file );
        //$file_array['name'] = $name;
        //$file_array['tmp_name'] = $path_file;
        //$file_array['size'] = filesize( $path_file );
		
		if ( !function_exists('media_handle_upload') ) {
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		}
		//var_dump(media_handle_sideload( $file_array, 0 )); die;
		return media_handle_sideload( $file_array, 0 );
    }

    /**
     * get the content form url and creates a file
     *
     * @param $url
     * @return array
     */
    public function fetch_from_remote( $url ){
        $tmp = wp_remote_get( $url );
        if( !is_wp_error($tmp) && isset( $tmp['body'] ) ){
            $file = fopen( $this->get_id().'.txt', 'w' );
            fwrite( $file, $tmp['body'] );
            fclose( $file );
            return $this->add_file( $this->get_id().'.txt' );
        }
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
	 * Adds a new method accepeted by the importer
	 *
	 * Current possible methods are file and url
	 * 
	 * @param string $method file or url
	 * @return bool true for success, false if method does not exist
	 */
	public function add_import_method($method) {
		if ( array_key_exists($method, $this->accepts) ) {
			$this->accepts[$method] = true;
			return true;
		}
		return false;
	}

	/**
	 * Removes method accepeted by the importer
	 *
	 * Current possible methods are file and url
	 * 
	 * @param string $method file or url
	 * @return bool true for success, false if method does not exist
	 */
	public function remove_import_method($method) {
		if ( array_key_exists($method, $this->accepts) ) {
			$this->accepts[$method] = false;
			return true;
		}
		return false;
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

    public function is_finished()
    {
        if($this->current_step >= count($this->steps))
        {
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
	 * Importers may change this label whenever they want
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
					if ( isset($col['total_items']) && is_numeric($col['total_items']) ) {
						$totalItems += intval($col['total_items']);
						if ($i < $current_collection) {
							$currentItem += $col['total_items'];
						}
					}
				}

				if ($totalItems > 0) {
					$value = round( ($currentItem/$totalItems) * 100 );
				}


			} else {

				if ( isset($step['total']) && is_numeric($step['total']) && $step['total'] > 0 ) {
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


	///////////////////////////////
	// Abstract methods
	

    /**
     * get the metadata of file/url to allow mapping
     * should return an array
     *
     * Used when $manual_mapping is set to true, to build the mapping interface
     *
     * @return array $metadata_source the metadata from the source
     */
    public function get_source_metadata() {}
		
    /**
     * get values for a single item
     *
     * @param  $index
     * @return array with metadatum_source's as the index and values for the
     * item
     *
     * Ex: [ 'Metadatum1' => 'value1', 'Metadatum2' => [ 'value2','value3' ]
     */
    abstract public function process_item( $index, $collection_id );

    
	
	/**
	 * Method implemented by the child importer class to return the total number of items that will be imported
	 *
	 * Used to build the progress bar
	 * 
	 * @return int
	 */
	public function get_source_number_of_items() {}


	/**
	 * Method implemented by child importer to return the HTML of the Options Form to be rendered in the Importer page
	 */
	public function options_form() {}
	
	/**
	* Called when the process is finished. returns the final message to the user with a 
	* short description of what happened. May contain HTML code and links
	*
	* @return string 
	*/
	public function get_output() {
		return '';
	}
	
	////////////////////////////////////////
	// Core methods
	
    /**
     * process an item from the collections queue
     *
     */
    public function process_collections() {
        
		$current_collection = $this->get_current_collection();
		$collections = $this->get_collections();
		$collection_definition = isset($collections[$current_collection]) ? $collections[$current_collection] : false;
		$current_collection_item = $this->get_current_collection_item();
		
		$this->add_log('Processing item ' . $current_collection_item);
		$processed_item = $this->process_item( $current_collection_item, $collection_definition );
		if( $processed_item ) {

			if( is_bool($processed_item) ){
				return $this->next_item();	
			}

			$this->add_log('Inserting item ' . $current_collection_item);
			$this->insert( $processed_item, $current_collection );
		} else {
			$this->add_error_log('failed on item '. $current_collection_item );
		}
		
		return $this->next_item();
    }
	
	protected function next_item() {
		
		$current_collection = $this->get_current_collection();
		$current_collection_item = $this->get_current_collection_item();
		$collections = $this->get_collections();
		$collection = $collections[$current_collection];
		
		$current_collection_item ++;
		$this->set_current_collection_item($current_collection_item);

		if( $this->get_transient('change_total') ){
            $collection['total_items'] = $this->get_transient('change_total');
        }
		
		if ($current_collection_item >= $collection['total_items']) {
			return $this->next_collection();
		}
		
		return $current_collection_item;
		
	}
	
	protected function next_collection() {
		
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

    /**
     * insert processed item from source to Tainacan
     *
     * @param array $processed_item Associative array with metadatum source's as index with
     *                              its value or values
     * @param integet $collection_index The index in the $this->collections array of the collection the item is beeing inserted into
     * 
     * @return Tainacan\Entities\Item Item inserted
     */
    public function insert( $processed_item, $collection_index ) {
		
		remove_action( 'post_updated', 'wp_save_post_revision' );
		$collections = $this->get_collections();
		$collection_definition = isset($collections[$collection_index]) ? $collections[$collection_index] : false;
		if ( !$collection_definition || !is_array($collection_definition) || !isset($collection_definition['id']) || !isset($collection_definition['mapping']) ) {
			$this->add_error_log('Collection misconfigured');
            return false;
		}
		
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_definition['id']);
		
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		$Tainacan_Items->disable_logs();
		$Tainacan_Metadata->disable_logs();
		$Tainacan_Item_Metadata->disable_logs();

        $item = new Entities\Item( ( $this->get_transient('item_id') ) ? $this->get_transient('item_id') : 0 );
		$itemMetadataArray = [];
		
        if( is_array( $processed_item ) ){
            foreach ( $processed_item as $metadatum_source => $values ){
                $tainacan_metadatum_id = array_search( $metadatum_source, $collection_definition['mapping'] );
                $metadatum = $Tainacan_Metadata->fetch( $tainacan_metadatum_id );

                if( $metadatum instanceof Entities\Metadatum ){
                    $singleItemMetadata = new Entities\Item_Metadata_Entity( $item, $metadatum); // *empty item will be replaced by inserted in the next foreach
                    $singleItemMetadata->set_value( $values );
                    $itemMetadataArray[] = $singleItemMetadata;
                } else {
					$this->add_error_log('Metadata ' . $metadatum_source . ' not found');
				}

            }
        }
		
        if( !empty( $itemMetadataArray ) && $collection instanceof Entities\Collection ){
			$item->set_collection( $collection );

            if( $item->validate() ){
				$insertedItem = $Tainacan_Items->insert( $item );
            } else {
                $this->add_error_log( 'Error inserting item' );
                $this->add_error_log( $item->get_errors() );
                return false;
            }
			
            foreach ( $itemMetadataArray as $itemMetadata ) {
                $itemMetadata->set_item( $insertedItem );  // *I told you

                if( $itemMetadata->validate() ){
					$result = $Tainacan_Item_Metadata->insert( $itemMetadata );
                } else {
                    $this->add_error_log('Error saving value for ' . $itemMetadata->get_metadatum()->get_name());
                    $this->add_error_log($itemMetadata->get_errors());
                    continue;
                }

                //if( $result ){
                //	$values = ( is_array( $itemMetadata->get_value() ) ) ? implode( PHP_EOL, $itemMetadata->get_value() ) : $itemMetadata->get_value();
                //    $this->add_log( 'Item ' . $insertedItem->get_id() .
                //        ' has inserted the values: ' . $values . ' on metadata: ' . $itemMetadata->get_metadatum()->get_name() );
                //} else {
                //    $this->add_error_log( 'Item ' . $insertedItem->get_id() . ' has an error' );
                //}
			}
			
			$insertedItem->set_status('publish' );
			
            if($insertedItem->validate()) {
				$insertedItem = $Tainacan_Items->update( $insertedItem );

				$this->after_inserted_item(  $insertedItem, $collection_index );
            } else {
	            $this->add_error_log( 'Error publishing Item'  ); 
	            $this->add_error_log( $insertedItem->get_errors() ); 
	            return false;
			}
			
            return $insertedItem;
			
        } else {
            $this->add_error_log(  'Collection not set');
            return false;
        }

	}
	
	/**
	 * allow importers executes process after item is insertes
	 * @param array $insertedItem Associative array with inserted item
     * @param integer $collection_index The index in the $this->collections array of the collection the item is beeing inserted into
	 * 
	 */
	public function after_inserted_item($insertedItem, $collection_index){}

    /**
     * runs one iteration
     */
    public function run(){

		if ($this->is_finished()) {
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
		
		return $return;
		
		
    }

    /**
     * @param $metadata_description
     * @param $collection_id
     * @return bool
     * @throws \Exception
     */
    public function create_new_metadata( $metadata_description, $collection_id){
        $taxonomy_repo = \Tainacan\Repositories\Taxonomies::get_instance();
        $metadata_repo = \Tainacan\Repositories\Metadata::get_instance();

        $properties =  array_filter( explode('|', $metadata_description) );

        if( is_array($properties) && count($properties) < 2 ){
            $properties[1] = 'text';
        } else if( !$properties ){
            return false;
        }

        $name = $properties[0];
        $type = $properties[1];
		
		$supported_types = \Tainacan\Repositories\Metadata::get_instance()->fetch_metadata_types('NAME');
		$supported_types = array_map('strtolower', $supported_types);
		
		if ( ! \in_array($type, $supported_types) ) {
			$this->add_log( __('Unknown Metadata type "' . $type . '" for '.$name.'. Considering text type.', 'tainacan') );
			$type = 'text';
		}
		
        $newMetadatum = new Entities\Metadatum();
        $newMetadatum->set_name($name);

        $type = ucfirst($type);
        $newMetadatum->set_metadata_type('Tainacan\Metadata_Types\\'.$type);
        $newMetadatum->set_collection_id( (isset($collection_id)) ? $collection_id : 'default');
        $newMetadatum->set_status('publish');

        if( strcmp(strtolower($type), "taxonomy") === 0 ){
            $taxonomy = new Entities\Taxonomy();
            $taxonomy->set_name($name);
            $taxonomy->set_status('publish');
            $taxonomy->set_allow_insert('yes');

            if($taxonomy->validate()){
                $inserted_tax = $taxonomy_repo->insert( $taxonomy );
                $newMetadatum->set_metadata_type_options([
                    'taxonomy_id' => $inserted_tax->get_id(),
                    'allow_new_terms' => 'yes',
                    'input_type' => 'tainacan-taxonomy-checkbox'
                ]);
            }

        }

        /*Properties of metadatum*/
        if( is_array($properties) && in_array( 'required', $properties)){
            $newMetadatum->set_required('yes');
        }

        if(is_array($properties) && in_array( 'multiple', $properties) ){
            $newMetadatum->set_multiple('yes');
        }

        if( is_array($properties) && in_array( 'display_yes', $properties) ){
            $newMetadatum->set_display('yes');
        } else if(is_array($properties) && in_array( 'display_no', $properties) ){
            $newMetadatum->set_display('no');
        }  else if(is_array($properties) && in_array( 'display_never', $properties) ){
            $newMetadatum->set_display('never');
        }

        if( is_array($properties) && in_array( 'status_public', $properties) ){
            $newMetadatum->set_status('publish');
        } else if( is_array($properties) && in_array( 'status_private', $properties) ){
            $newMetadatum->set_status('private');
        }

        if( is_array($properties) && in_array( 'collection_key_yes', $properties) ){
            $newMetadatum->set_collection_key('yes');
        } else if( is_array($properties) && in_array( 'collection_key_no', $properties) ){
            $newMetadatum->set_collection_key('no');
        }

        if($newMetadatum->validate()){
            $inserted_metadata = $metadata_repo->insert( $newMetadatum );

            $this->add_log('Metadata created: ' . $inserted_metadata->get_name());
            return $inserted_metadata;
        } else{
            $this->add_log('Error creating metadata ' . $name . ' in collection ' . $collection_id);
            $this->add_log($newMetadatum->get_errors());

            return false;
        }
    }
}