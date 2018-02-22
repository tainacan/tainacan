<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

    public $delimiter = ',';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @return string $delimiter value that divides each column
     */
    public function get_delimiter(){
        return $this->delimiter;
    }


    public function set_delimiter( $delimiter ){
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritdoc
     */
    public function get_fields_source(){
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(0 );
        return $file->fgetcsv( $this->get_delimiter() );
    }

    /**
     *
     */
    public function process_item(){
        // TODO: process single item
    }

    /**
     * @return mixed
     */
    public function get_options(){
        // TODO: Implement get_options() method.
    }


    public function get_total_items(){
        if( isset( $this->total_items ) ){
            return $this->total_items;
        } else {
            $file = new \SplFileObject( $this->tmp_file, 'r' );
            $file->seek(PHP_INT_MAX);
            // -1 removing header
            return $this->total_items = $file->key() - 1;
        }
    }
}