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

    /**
     * @param $delimiter
     */
    public function set_delimiter( $delimiter ){
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritdoc
     */
    public function get_fields_source(){
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(0 );
        return $file->fgetcsv( $this->get_delimiter() );
    }

    /**
     * @inheritdoc
     */
    public function process( $start, $end ){
        while ( $start <  $end && count( $this->get_processed_items() ) <= $this->get_total_items() ){
            $processed_item = $this->process_item( $start );
            if( $processed_item) {
                $this->insert( $start, $processed_item );
            } else {
                $this->set_log('error', 'failed on item '.$start );
                break;
            }
            $start++;
        }
    }

    /**
     * @inheritdoc
     */
    public function process_item( $index ){
        $processedItem = [];
        $headers = $this->get_fields_source();
        
        // search the index in the file and get values
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek( $index );

        if( $index === 0 ){
            $file->current();
            $file->next();
            $values = $file->fgetcsv( $this->get_delimiter() );
        }else{
            $values = $file->fgetcsv( $this->get_delimiter() );
        }

        if( count( $headers ) !== count( $values ) ){
           return false;
        }

        foreach ($headers as $index => $header) {
            $processedItem[ $header ] = $values[ $index ];
        }

        return $processedItem;
    }

    /**
     * @return mixed
     */
    public function get_options(){
        // TODO: Implement get_options() method.
    }

    /**
     * @inheritdoc
     */
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