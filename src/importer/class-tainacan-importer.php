<?php
namespace Tainacan\Importer;
use Tainacan;
use Tainacan\Entities;

abstract class Importer {

    private $id;
    private $processed_items = [];
	
	/**
	 * indicates wether this importer will create all the fields collection and set the mapping
	 * without user interaction
	 *
	 * if set to true, user will have the ability to choose to create a new collection upon importing.
	 *
	 * The importer will have to implement the create_fields_and_mapping() method.
	 * 
	 * @var bool
	 */
	protected $import_structure_and_mapping = false;
	
    /**
     * The collection the items are going to be imported to.
     * 
     * @var \Tainacan\Entities\Collection 
     */
	public $collection;
    
	/**
	 * The mapping from the source metadata structure to the Field Ids of the destination collection
	 *
	 * The format is an array where the keys are the field IDs of the destination collection and the 
	 * values are the identifier from the source. This coulb be an ID or a string or whatever the importer finds appropriate to http_persistent_handles_clean
	 * 
	 * @var array
	 */
	public $mapping;
	private $repository_mapping;
    
	/**
	 * The path to the temporary file created when user uploads a file
	 * @var string
	 */
	public $tmp_file;
    
	/**
	 * The total number of items to be imported.
	 * @var int
	 */
	protected $total_items;
    
	/**
	 * THe number of items to be processes in each step
	 * @var int
	 */
	private $items_per_step = 100;
    
	/**
	 * The index of the item to start the import in the next step.
	 *
	 * (items are imported in a series of steps, via ajax, to avoid timeout)
	 * @var int
	 */
	private $start = 0;
	private $inside_step_pointer = 0;
    
	/**
	 * The log with everything that happened during the import process. It generates a report afterwards
	 * @var array
	 */
	public $logs = [];
	
	private $options = [];
	
	private $default_options = [];
	
	private $accpets = [
		'file' => true,
		'url'  => false,
	];

	private $is_repository = false;

	private $steps = [];

	private $current_step = 0;

	private $url = '';

    public function __construct() {
        if (!session_id()) {
            @session_start();
        }

        $this->id = uniqid();
        $_SESSION['tainacan_importer'][$this->get_id()] = $this;
    }

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

    /**
     * @return array Mapping
     */
    public function get_mapping(){
        return $this->mapping;
    }

    /**
     * @return array Array with ids inserted in Tainacan
     */
    public function get_processed_items(){
        return $this->processed_items;
    }

    /**
     * @return array the last index from source
     */
    public function get_logs(){
        return $this->logs;
    }

    /**
     * @param Tainacan\Entities\Collection $collection
     */
    public function set_collection( Entities\Collection $collection ){
        $this->collection = $collection;
    }

    /**
     * save an associative array with tainacan field id as index and field from source as value
     *
     * @param array $mapping Mapping importer-fields
     */
    public function set_mapping( $mapping){
        if(!empty($mapping))
        {
            $this->mapping = $mapping;
        }
    }

    public function set_repository_mapping( $mapping, $item_id ){
        if(!empty($mapping) && !empty($item_id))
        {
            $this->repository_mapping[$item_id] = $mapping;
        }else return false;
    }

    public function get_repository_mapping($item_id)
    {
        if(!empty($item_id))
        {
            return $this->repository_mapping[$item_id];
        }else return false;
    }

    /**
     * set how many items should be processes in each step
     *
     * @param $size The total of items
     */
    public function set_items_per_step( $size ){
        $this->items_per_step = $size;
    }

    /**
     * @param int $start the first index to init the process
     */
    public function set_start( $start ){
        $this->start = $start;
    }

    /**
     * @param $file File to be managed by importer
     * @return bool
     */
    public function set_file( $file ){
        $new_file = $this->upload_file( $file );
        if ( is_numeric( $new_file ) ) {
            $this->tmp_file = get_attached_file( $new_file );
        } else {
            return false;
        }
    }

    public function set_inside_step_pointer($step_pointer)
    {
        if(is_numeric($step_pointer) && $step_pointer >= 0)
        {
            $this->inside_step_pointer = $step_pointer;
        }else
        {
            $this->inside_step_pointer = 0;
        }
    }

    public function get_inside_step_pointer()
    {
        return $this->inside_step_pointer;
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
        if( isset( $tmp['body'] ) ){
            $file = fopen( $this->get_id().'.txt', 'w' );
            fwrite( $file, $tmp['body'] );
            fclose( $file );
            return $this->set_file( $this->get_id().'.txt' );
        }
    }

    /**
     * get the fields of file/url to allow mapping
     * should return an array
     *
     * @return array $fields_source the fields from the source
     */
    abstract public function get_fields();

    /**
     * get values for a single item
     *
     * @param  $index
     * @return array with field_source's as the index and values for the
     * item
     *
     * Ex: [ 'Field1' => 'value1', 'Field2' => [ 'value2','value3' ]
     */
    abstract public function process_item( $index );

    /**
     * return the all items found
     *
     * @return int Total of items
     */
    public function get_total_items() {
		if ( !isset( $this->total_items ) ) {
            $this->total_items = $this->get_total_items_from_source();
		}
		return $this->total_items;
	}
	
	/**
	 * Method implemented by the child importer class to return the number of items to be imported
	 * @return int
	 */
	abstract public function get_total_items_from_source();
	
	/**
     * Gets the options for this importer, including default values for options
     * that were not set yet.
     * @return array Importer options
     */
    public function get_options() {
        return array_merge($this->default_options, $this->options);
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
	 * Must be called from the __construct method of the child importer class to set default values.
	 * 
	 * @param array $options 
	 */
	protected function set_default_options($options) {
		$this->default_options = $options;
	}
	
	/**
	 * Set the options array
	 * @param array $options 
	 */
	public function set_options($options) {
		$this->options = $options;
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

	protected function get_start()
    {
        return $this->start;
    }

    protected function get_items_per_step()
    {
        return $this->items_per_step;
    }

    /**
     * Sets importer as repository importer
     */
	public function set_repository()
    {
        $this->is_repository = true;
    }

    public function set_steps($steps)
    {
        $this->steps =$steps;
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
	
    /**
     * process a limited size of items
     *
     * @param int $start the index of the item to start processing from
     */
    public function process( $start ){
        
		$end = $start + $this->items_per_step;

		while ( $start <  $end && count( $this->get_processed_items() ) < $this->get_total_items() ) {
            $processed_item = $this->process_item( $start );
            if( $processed_item) {
                $this->insert( $start, $processed_item );
            } else {
                $this->add_log('error', 'failed on item '.$start );
                break;
            }
            $start++;
        }
		
		$this->set_start($start);
    }

    /**
     * insert processed item from source to Tainacan
     *
     * @param int $index the source id unique for the item
     * @param array $processed_item Associative array with field source's as index with
     *                              its value or values
     * @return Tainacan\Entities\Item Item inserted
     */
    public function insert( $index, $processed_item ){
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $isUpdate = ( is_array( $this->processed_items ) && isset( $this->processed_items[ $index ] ) )
            ? $this->processed_items[ $index ] : 0;
        $item = new Entities\Item( $isUpdate );
        $itemMetadataArray = [];

        if( !isset( $this->mapping ) ){
            $this->add_log('error','Mapping is not set');
            return false;
        }

        if( is_array( $processed_item ) ){
            foreach ( $processed_item as $field_source => $values ){
                $tainacan_field_id = array_search( $field_source, $this->mapping );
                $field = $Tainacan_Fields->fetch( $tainacan_field_id );

                if( $field instanceof Entities\Field ){
                    $singleItemMetadata = new Entities\Item_Metadata_Entity( $item, $field);
                    $singleItemMetadata->set_value( $values );
                    $itemMetadataArray[] = $singleItemMetadata;
                }

            }
        }

        if( !empty( $itemMetadataArray ) && $this->collection instanceof Entities\Collection ){
            $item->set_collection( $this->collection );

            if( $item->validate() ){
                $insertedItem = $Tainacan_Items->insert( $item );
            } else {
                $this->add_log( 'error', 'Item ' . $index . ': ' ); // TODO add the  $item->get_errors() array
                return false;
            }

            foreach ( $itemMetadataArray as $itemMetadata ) {
                $itemMetadata->set_item( $insertedItem );

                if( $itemMetadata->validate() ){
                    $result = $Tainacan_Item_Metadata->insert( $itemMetadata );
                } else {
                    $this->add_log( 'error', 'Item ' . $index . ' on field '. $itemMetadata->get_field()->get_name()
                        .' has error ' . $itemMetadata->get_errors() );
                    continue;
                }

                if( $result ){
                	$values = ( is_array( $itemMetadata->get_value() ) ) ? implode( PHP_EOL, $itemMetadata->get_value() ) : $itemMetadata->get_value();
                    $this->add_log( 'success', 'Item ' . $index .
                        ' has inserted the values: ' . $values . ' on field: ' . $itemMetadata->get_field()->get_name() );
                } else {
                    $this->add_log( 'error', 'Item ' . $index . ' has an error' );
                }
            }

            $insertedItem->set_status('publish' );

            // inserted the id on processed item with its index as array index
            $this->processed_items[ $index ] = $insertedItem->get_id();
            if($insertedItem->validate()) {
	            $insertedItem = $Tainacan_Items->update( $insertedItem );
            } else {
	            $this->add_log( 'error', 'Item ' . $index . ': ' . $insertedItem->get_errors()[0]['title'] ); // TODO add the  $item->get_errors() array
	            return false;
            }

            return $insertedItem;
        } else {
            $this->add_log( 'error', 'Collection not set');
            return false;
        }

    }

    /**
     * run the process
     */
    public function run(){
        if($this->is_repository && $this->current_step < count($this->steps))
        {
            //$process_name = key($this->steps);
            $function_name = current($this->steps);
            $inside_step_pointer = $this->{$function_name}();//If unlike numeric this means that still there is stuff to process

            if($inside_step_pointer === false || (!is_numeric($inside_step_pointer) || $inside_step_pointer < 0))
            {
                //Move on to the next step
                next($this->steps);
                $this->current_step++;
                $this->set_inside_step_pointer(0);
            }else if(is_numeric($inside_step_pointer) && $inside_step_pointer > 0)
            {
                $this->set_inside_step_pointer($inside_step_pointer);
            }
        }
        else
        {
            if ( ( !isset($this->collection) || ! $this->collection instanceof Entities\Collection ) && $this->import_structure_and_mapping ) {
                $new_collection = new Entities\Collection();
                $new_collection->set_name('New Imported Collection');
                $new_collection->set_status('publish');
                $new_collection->validate();
                $new_collection = Tainacan\Repositories\Collections::get_instance()->insert($new_collection);

                $this->set_collection($new_collection);

                if (!method_exists($this, 'create_fields_and_mapping')) {
                    throw new Exception('Importers with import_structure_and_mapping true must implement create_fields_and_mapping method');
                }

                $this->create_fields_and_mapping();

            }

            $this->process( $this->start );
            return sizeof($this->get_processed_items());
        }
    }
}