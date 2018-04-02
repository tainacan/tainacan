<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

    public function __construct() {
        parent::__construct();
		
		$this->set_default_options([
			'delimiter' => ','
		]);
		
    }

    /**
     * @inheritdoc
     */
    public function get_fields(){
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(0 );
        return $file->fgetcsv( $this->get_option('delimiter') );
    }


    /**
     * @inheritdoc
     */
    public function process_item( $index ){
        $processedItem = [];
        $headers = $this->get_fields();
        
        // search the index in the file and get values
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek( $index );

        if( $index === 0 ){
            $file->current();
            $file->next();
            $values = $file->fgetcsv( $this->get_option('delimiter') );
        }else{
            $values = $file->fgetcsv( $this->get_option('delimiter') );
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
     * @inheritdoc
     */
    public function get_total_items_from_source(){
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(PHP_INT_MAX);
        // -1 removing header
        return $this->total_items = $file->key() - 1;
    }
}