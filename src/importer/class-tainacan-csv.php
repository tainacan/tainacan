<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

    public $delimiter = ',';
    private $file;

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
        $this->file = ( !isset( $this->file ) ) ?  new \SplFileObject( $this->tmp_file, 'r' ) : $this->file;
        $this->file->seek(0 );
        return $this->file->fgetcsv( $this->get_delimiter() );
    }

    /**
     * @inheritdoc
     */
    public function process( $start, $end ){
        $this->file = ( !isset( $this->file ) ) ?  new \SplFileObject( $this->tmp_file, 'r' ) : $this->file;

        while ( $start <  $end ){
            if( $start === 0 ){
                $start++;
                continue;
            }

            $processed_item = $this->process_item( $start );
            $this->insert( $start, $processed_item );
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
        $this->file->seek( $index );
        $values = $this->file->fgetcsv( $this->get_delimiter() );

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