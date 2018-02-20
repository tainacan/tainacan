<?php
namespace Tainacan\Importer;
use Tainacan;

abstract class Importer {

    public $collection;
    public $mapping;
    public $tmp_file;

    public function __construct() {
        if (!session_id()) {
            @session_start();
        }
    }

    /**
     * @param Tainacan\Entities\Collection $collection
     */
    public function set_collection( Tainacan\Entities\Collection $collection ){
        $this->collection = $collection;
        $_SESSION['tainacan_importer'] = $this;
    }

    /**
     * @param array the mapping
     */
    public function set_mapping( $mapping ){
        $this->mapping = $mapping;
        $_SESSION['tainacan_importer'] = $this;
    }

    /**
     * @param $file File to be managed by importer
     */
    public function set_file( $file ){
        $new_file = wp_handle_upload( $file );

        if ( $new_file && ! isset( $new_file['error'] ) ) {
            $this->tmp_file = $new_file['file'];
        } else {
            echo $new_file['error'];
        }
    }

    /**
     * get the fields of file/url to allow mapping
     * should returns an array
     */
    abstract public function get_fields_source();

    /**
     * @param $start
     * @param $end
     */
    public function process( $start, $end ){

    }

    public function run(){

    }
}