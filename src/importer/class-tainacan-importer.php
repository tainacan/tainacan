<?php
namespace Tainacan\Importer;
use Tainacan;

abstract class Importer {

    private $id;
    private $processed_items;
    private $last_index;

    public $collection;
    public $mapping;
    public $tmp_file;
    public $total_items;
    public $limit_query;
    public $start;
    public $end;
    public $logs = [];

    public function __construct() {
        if (!session_id()) {
            @session_start();
        }

        $this->id = uniqid();
        $this->limit_query = 100;
        $this->start = 0;
        $this->end = $this->start + $this->limit_query;
        $this->processed_items = [];
        $_SESSION['tainacan_importer'][$this->id] = $this;
    }

    /**
     * @return string
     */
    public function get_id(){
        return $this->id;
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
     * @return mixed the last index from source
     */
    public function get_last_index(){
        return $this->last_index;
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
    public function set_collection( Tainacan\Entities\Collection $collection ){
        $this->collection = $collection;
    }

    /**
     * save an associative array with tainacan field id as index and field from source as value
     *
     * @param array $mapping Mapping importer-fields
     */
    public function set_mapping( $mapping ){
        $this->mapping = $mapping;
    }

    /**
     * set the limit of query to be processed
     *
     * @param $size The total of items
     */
    public function set_limit_query( $size ){
        $this->limit_query = $size;
    }

    /**
     * @param int $start the first index to init the process
     */
    public function set_start( $start ){
        $this->start = $start;
    }

    /**
     * @param mixed $end the last index in process
     */
    public function set_end( $end ){
        $this->end = $end;
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
     * @return mixed
     */
    public function get_collection_fields(){
        return $this->collection;
    }

    /**
     * get the fields of file/url to allow mapping
     * should returns an array
     *
     * @return array $fields_source the fields from the source
     */
    abstract public function get_fields_source();

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
     * @return mixed
     */
    abstract public function get_options();

    /**
     * return the all items found
     *
     * @return int Total of items
     */
    abstract public function get_total_items();

    /**
     * process a limited size of items
     *
     * @param $start init index
     * @param $end last index
     */
    public function process( $start, $end ){
        while ( $start <  $end && count( $this->get_processed_items() ) <= $this->get_total_items() ){
            $processed_item = $this->process_item( $start );
            if( $processed_item) {
                $this->insert( $start, $processed_item );
            } else {
                $this->add_log('error', 'failed on item '.$start );
                break;
            }
            $start++;
        }
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
        $Tainacan_Fields = \Tainacan\Repositories\Fields::getInstance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::getInstance();
        $Tainacan_Items = \Tainacan\Repositories\Items::getInstance();

        $isUpdate = ( is_array( $this->processed_items ) && isset( $this->processed_items[ $index ] ) )
            ? $this->processed_items[ $index ] : 0;
        $item = new Tainacan\Entities\Item( $isUpdate );
        $itemMetadataArray = [];

        if( !isset( $this->mapping ) ){
            $this->add_log('error','Mapping is not set');
            return false;
        }

        if( is_array( $processed_item ) ){
            foreach ( $processed_item as $field_source => $values ){
                $tainacan_field_id = array_search( $field_source, $this->mapping );
                $field = $Tainacan_Fields->fetch( $tainacan_field_id );

                if( $field instanceof Tainacan\Entities\Field ){
                    $singleItemMetadata = new Tainacan\Entities\Item_Metadata_Entity( $item, $field);
                    $singleItemMetadata->set_value( $values );
                    $itemMetadataArray[] = $singleItemMetadata;
                }

            }
        }

        if( !empty( $itemMetadataArray ) && $this->collection instanceof Tainacan\Entities\Collection ){
            $item->set_collection( $this->collection );

            if( $item->validate() ){
                $insertedItem = $Tainacan_Items->insert( $item );
            } else {
                $this->add_log( 'error', 'Item ' . $index . ': '. $item->get_errors() );
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

            $item->set_status('publish' );

            // inserted the id on processed item with its index as array index
            $this->processed_items[ $index ] = $item->get_id();

            // set the last index
            $this->last_index = $index;

            $Tainacan_Items->update( $item );
            return $item;
        } else {
            $this->add_log( 'error', 'Collection not set');
            return false;
        }

    }

    /**
     * run the process
     */
    public function run(){
        $this->process( $this->start, $this->end );
    }
}