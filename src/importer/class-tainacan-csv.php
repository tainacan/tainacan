<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

	protected $manual_mapping = true;
	
	protected $manual_collection = true;
	
	public function __construct() {
        parent::__construct();
		
		$this->set_default_options([
			'delimiter' => ','
		]);
		
    }

    /**
     * @inheritdoc
     */
    public function get_source_fields(){
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(0 );
        return $file->fgetcsv( $this->get_option('delimiter') );
    }


    /**
     * @inheritdoc
     */
    public function process_item( $index, $collection_definition ){
        $processedItem = [];
        $headers = $this->get_source_fields();
        
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
		
		$this->set_progress_current($index+1);

        return $processedItem;
    }

    /**
     * @inheritdoc
     */
    public function get_progress_total_from_source(){
        $file = new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(PHP_INT_MAX);
        // -1 removing header
        return $this->total_items = $file->key() - 1;
    }
}