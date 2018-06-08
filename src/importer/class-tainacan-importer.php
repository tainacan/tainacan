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
	 * Wether Tainacan must present the user with an interface to manually map 
	 * the metadata from the source to the target collection.
	 *
	 * If set to true in the child class, it must implement the method 
	 * get_source_fields() to return the field found in the source.
	 *
	 * Note that this will only work when importing items to one single collection.
	 * @var bool
	 */
	protected $manual_mapping = false;
	
	/**
	 * Wether Tainacan will let the user choose a destination collection.
	 *
	 * If set to true, the API endpoints will handle Collection creation and will assign it to 
	 * the importer object using add_collection() method.
	 *
	 * Otherwise, the child importer class must create the collections and add them to the collections property also 
	 * using add_collection()
	 * 
	 * @var bool
	 */
	protected $manual_collection = true;
	
    
	/**
	 * The total number of iterations to be imported.
	 *
	 * if not possible to calculate, inform 0 (zero) and no progress bar will be displayed.
	 * 
	 * @var int
	 */
	protected $progress_total;
	
	protected $progress_current;
	
	/**
	 * This array holds the structure that the default step 'process_collections' will handle.
	 *
	 * Its an array of the target collections, with their IDs, an identifier from the source, the total number of items to be imported, the mapping array 
	 * from the source structure to the ID of the metadata fields in tainacan
	 *
	 * The format of the map is an array where the keys are the metadata IDs of the destination collection and the 
	 * values are the identifier from the source. This could be an ID or a string or whatever the importer finds appropriate to handle
	 *
	 * The source_id can be anyhting you like, that helps you relate this collection to your source.
	 * 
	 * Example of the structure of this propery for one collection:
	 * 0 => [
	 * 	'id' => 12,
	 * 	'map' => [
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
	
	private $accpets = [
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
	
	public function _to_Array() {
		$return = [];
		foreach ($this->array_attributes as $attr) {
			$method = 'get_' . $attr;
			$return[$attr] = $this->$method();
		}
		$return['class_name'] = get_class($this);
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
	
	public function get_progress_current() {
		return $this->progress_current;
	}
	
	public function set_progress_current($value) {
		$this->progress_current = $value;
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
	
	/**
     * return the total progress number to calculate progress
     *
     * @return int Total of items
     */
    public function get_progress_total() {
		if ( !isset( $this->progress_total ) ) {
            if ( method_exists($this, 'get_progress_total_from_source') ) {
				$this->progress_total = $this->get_progress_total_from_source();
			} else {
				$this->progress_total = 0;
			}
			
		}
		return $this->progress_total;
	}
	
	private function get_transients() {
		return $this->transients;
	}
	
	private function set_transients(array $data) {
		$this->transients = $data;
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
        } else {
            return false;
        }
    }
	
	
    /**
     * log the actions from importer
     *
     * @param $type
     * @param $message
     */
    public function add_log($type, $message ){
        $this->logs[] = [ 'type' => $type, 'message' => $message ];
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
    private function upload_file( $path_file ){
        $name = basename( $path_file );
        $file_array['name'] = $name;
        $file_array['tmp_name'] = $path_file;
        $file_array['size'] = filesize( $path_file );
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
		if ( array_key_exists($method, $this->accpets) ) {
			$this->acceps[$method] = true;
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
		if ( array_key_exists($method, $this->accpets) ) {
			$this->acceps[$method] = false;
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


	///////////////////////////////
	// Abstract methods
	

    /**
     * get the fields of file/url to allow mapping
     * should return an array
     *
     * Used when $manual_mapping is set to true, to build the mapping interface
     *
     * @return array $fields_source the fields from the source
     */
    public function get_source_fields() {}
		
    /**
     * get values for a single item
     *
     * @param  $index
     * @return array with field_source's as the index and values for the
     * item
     *
     * Ex: [ 'Field1' => 'value1', 'Field2' => [ 'value2','value3' ]
     */
    abstract public function process_item( $index, $collection_id );

    
	
	/**
	 * Method implemented by the child importer class to return the total number of interations the importer must run
	 *
	 * Used to build the progress bar
	 * 
	 * @return int
	 */
	public function get_progress_total_from_source() {}
	
	
	
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
		
		$processed_item = $this->process_item( $current_collection_item, $collection_definition );
		if( $processed_item) {
			$this->insert( $processed_item, $current_collection );
		} else {
			$this->add_log('error', 'failed on item '. $start );
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
     * @param array $processed_item Associative array with field source's as index with
     *                              its value or values
     * @return Tainacan\Entities\Item Item inserted
     */
    public function insert( $processed_item, $collection_index ) {
		
        $collections = $this->get_collections();
		$collection_definition = isset($collections[$collection_index]) ? $collections[$collection_index] : false;
		if ( !$collection_definition || !is_array($collection_definition) || !isset($collection_definition['id']) || !isset($collection_definition['map']) ) {
			$this->add_log('error','Collection misconfigured');
            return false;
		}
		
		$collection = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_definition['id']);
		
		$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $item = new Entities\Item();
        $itemMetadataArray = [];

        if( is_array( $processed_item ) ){
            foreach ( $processed_item as $field_source => $values ){
                $tainacan_field_id = array_search( $field_source, $collection_definition['map'] );
                $field = $Tainacan_Fields->fetch( $tainacan_field_id );

                if( $field instanceof Entities\Field ){
                    $singleItemMetadata = new Entities\Item_Metadata_Entity( $item, $field); // *empty item will be replaced by inserted in the next foreach
                    $singleItemMetadata->set_value( $values );
                    $itemMetadataArray[] = $singleItemMetadata;
                }

            }
        }

        if( !empty( $itemMetadataArray ) && $collection instanceof Entities\Collection ){
            $item->set_collection( $collection );

            if( $item->validate() ){
                $insertedItem = $Tainacan_Items->insert( $item );
            } else {
                $this->add_log( 'error', 'Item ' . $index . ': ' ); // TODO add the  $item->get_errors() array
                return false;
            }

            foreach ( $itemMetadataArray as $itemMetadata ) {
                $itemMetadata->set_item( $insertedItem );  // *I told you

                if( $itemMetadata->validate() ){
                    $result = $Tainacan_Item_Metadata->insert( $itemMetadata );
                } else {
                    $this->add_log( 'error', 'Item ' . $insertedItem->get_id() . ' on field '. $itemMetadata->get_field()->get_name()
                        .' has error ' . $itemMetadata->get_errors() );
                    continue;
                }

                if( $result ){
                	$values = ( is_array( $itemMetadata->get_value() ) ) ? implode( PHP_EOL, $itemMetadata->get_value() ) : $itemMetadata->get_value();
                    $this->add_log( 'success', 'Item ' . $insertedItem->get_id() .
                        ' has inserted the values: ' . $values . ' on field: ' . $itemMetadata->get_field()->get_name() );
                } else {
                    $this->add_log( 'error', 'Item ' . $insertedItem->get_id() . ' has an error' );
                }
            }

            $insertedItem->set_status('publish' );

            if($insertedItem->validate()) {
	            $insertedItem = $Tainacan_Items->update( $insertedItem );
            } else {
				//error_log(print_r($insertedItem->get_errors(), true));
	            //$this->add_log( 'error', 'Item ' . $index . ': ' . $insertedItem->get_errors()[0]['title'] ); // TODO add the  $item->get_errors() array
	            return false;
            }

            return $insertedItem;
			
        } else {
            $this->add_log( 'error', 'Collection not set');
            return false;
        }

    }

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
			$result = $this->$method_name();
		} else {
			$this->add_log( 'error', 'Callback not found for step ' . $steps[$current_step]['name']);
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
}