<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

	protected $manual_mapping = true;
	
	protected $manual_collection = true;
	
	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		
		$this->set_default_options([
            'delimiter' => ',',
            'multivalued_delimiter' => '||',
            'encode' => 'UTF-8',
            'cell_encapsulate' => '"'
		]);
		
    }

    /**
     * @inheritdoc
     */
    public function get_source_metadata(){
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(0 );
        return $file->fgetcsv( $this->get_option('delimiter') );
    }


    /**
     * @inheritdoc
     */
    public function process_item( $index, $collection_definition ){
        $processedItem = [];
        $headers = $this->get_source_metadata();
        
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
        
        $cont = 0;
        foreach ( $collection_definition['mapping'] as $metadatum_id => $header) {
            $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);

            $processedItem[ $header ] = ( $metadatum->get_multiple() ) ? 
                explode( $this->get_option('multivalued_delimiter'), $values[ $cont ]) : $values[ $cont ];

            $cont++;
        }
        
        return $processedItem;
    }

    /**
     * @inheritdoc
     */
    public function get_source_number_of_items(){
        if (isset($this->tmp_file) && file_exists($this->tmp_file)) {
            $file = new \SplFileObject( $this->tmp_file, 'r' );
            $file->seek(PHP_INT_MAX);
            // -1 removing header
            return $this->total_items = $file->key() - 1;
        }
        return false;
        
    }

    public function options_form() {

        $form = '<label class="label">' . __('Delimiter', 'tainacan') . '</label>';
        $form .= '<input type="text" class="input" name="delimiter" value="' . $this->get_option('delimiter') . '" />';

        $form .= '<label class="label">' . __('Multivalued metadata delimiter', 'tainacan') . '</label>';
        $form .= '<input type="text" class="input" name="multivalued_delimiter" value="' . $this->get_option('multivalued_delimiter') . '" />';

        $form .= '<label class="label">' . __('Encoding', 'tainacan') . '</label>';
        $form .= '<input type="text" class="input" name="encode" value="' . $this->get_option('encode') . '" />';

        $form .= '<label class="label">' . __('Cell Encapsulate', 'tainacan') . '</label>';
        $form .= '<input type="text" class="input" name="cell_encapsulate" value="' . $this->get_option('cell_encapsulate') . '" />';

        return $form;

    }
}