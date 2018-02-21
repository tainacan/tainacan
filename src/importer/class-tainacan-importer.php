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
     * @param Tainacan\Entities\Collection $collection
     */
    public function set_collection( Tainacan\Entities\Collection $collection ){
        $this->collection = $collection;
    }

    /**
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
            $attach = get_post($new_file);
            $this->tmp_file = $attach->guid;
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
     * get the fields of file/url to allow mapping
     * should returns an array
     */
    abstract public function get_fields_source();

    /**
     * get values for a single item
     */
    abstract public function process_item();

    /**
     * @return mixed
     */
    abstract public function get_options();

    /**
     * @param $start
     * @param $end
     */
    public function process( $start, $end ){

    }

    /**
     *
     */
    public function run(){

    }
}