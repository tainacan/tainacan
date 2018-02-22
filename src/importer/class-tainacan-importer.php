<?php
namespace Tainacan\Importer;
use Tainacan;

abstract class Importer {

    private $id;

    public $collection;
    public $mapping;
    public $tmp_file;
    public $total_items;
    public $limit_query;
    public $logs = [];

    public function __construct() {
        if (!session_id()) {
            @session_start();
        }

        $this->id = uniqid();
        $_SESSION['tainacan_importer'][$this->id] = $this;
    }

    /**
     * @return string
     */
    public function get_id(){
        return $this->id;
    }


    /**
     * @return array
     */
    public function get_mapping(){
        return $this->mapping;
    }

    /**
     * @param Tainacan\Entities\Collection $collection
     */
    public function set_collection( Tainacan\Entities\Collection $collection ){
        $this->collection = $collection;
    }

    /**
     * save an associative array with tainacan field id and field from source
     *
     * @param array $mapping Mapping importer-fields
     */
    public function set_mapping( $mapping ){
        $this->mapping = $mapping;
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
     * @return array with field_source's as the index and values for the
     * item Ex: [ 'Field1' => 'value1', 'Field2' => [ 'value2','value3' ]
     */
    abstract public function process_item();

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
     * @param $start
     * @param $end
     */
    public function process( $start, $end ){

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
        global $Tainacan_Items, $Tainacan_Item_Metadata, $Tainacan_Fields;
        $item = new Tainacan\Entities\Item();
        $itemMetadataArray = [];

        if( !isset( $this->mapping ) ){
            $this->set_log('error','Mapping is not set');
            return false;
        }

        if( is_array( $processed_item ) ){
            foreach ( $processed_item as $field_source => $values ){
                $tainacan_field_id = array_search( $field_source, $this->mapping );
                $field = $Tainacan_Fields->fetch( $tainacan_field_id );

                if( $field instanceof Tainacan\Entities\Field ){
                    $singleItemMetadata = new Tainacan\Entities\Item_Metadata_Entity();
                    $singleItemMetadata->set_field( $field );
                    $singleItemMetadata->set_value( $values );
                    $itemMetadataArray[] = $singleItemMetadata;
                }

            }
        }

        if( !empty( $itemMetadata ) && $this->collection instanceof Tainacan\Entities\Collection ){
            $item->set_title( time() );
            $item->set_collection( $this->collection );
            $insertedItem = $Tainacan_Items->insert( $item );

            foreach ( $itemMetadataArray as $itemMetadata ) {
                $itemMetadata->set_item( $insertedItem );
                $result = $Tainacan_Item_Metadata->insert( $itemMetadata );

                if( $result ){
                	$values = ( is_array( $itemMetadata->get_value() ) ) ? implode( PHP_EOL, $itemMetadata->get_value() ) : $itemMetadata->get_value();
                    $this->set_log( 'success', 'Item ' . $index .
                        ' has inserted the values: ' . $values . ' on field: ' . $itemMetadata->get_field()->get_name() );
                } else {
                    $this->set_log( 'error', 'Item ' . $index . ' has an error' );
                }
            }

            $item->set_status('publish' );
            $Tainacan_Items->update( $item );
            return $item;
        } else {
            $this->set_log( 'error', 'Collection not set');
            return false;
        }

    }

    /**
     * @param $type
     * @param $message
     */
    public function set_log ( $type, $message ){
        $this->logs[] = [ 'type' => $type, 'message' => $message ];
    }

    /**
     *
     */
    public function run(){

    }
}