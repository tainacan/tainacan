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
            'encode' => 'utf8',
            'enclosure' => '"'
		]);
		
    }

    /**
     * alter the default options
     */
    public function set_option($key,$value){
        $this->default_options[$key] = $value;
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
            $values = $file->fgetcsv( $this->get_option('delimiter'), $this->get_option('enclosure') );
        }else{
            $values = $file->fgetcsv( $this->get_option('delimiter'), $this->get_option('enclosure')  );
        }

        if( count( $headers ) !== count( $values ) ){
           return false;
        }
        
        $cont = 0;
        foreach ( $collection_definition['mapping'] as $metadatum_id => $header) {
            $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);

            $column = $this->handle_encoding( $values[ $cont ] );

            $processedItem[ $header ] = ( $metadatum->is_multiple() ) ? 
                explode( $this->get_option('multivalued_delimiter'), $column) : $column;

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

        $form .= '<div class="control">';
        $form .= '<label class="label">' . __('Encoding', 'tainacan') . '</label>';
        $form .= '</div>';

        $utf8 = ( !$this->get_option('encode') || $this->get_option('encode') === 'utf8' ) ? 'checked' : '';
        $iso = ( !$this->get_option('encode') && $this->get_option('encode') === 'iso88591' ) ? 'checked' : '';

        $form .= '<div class="control">';
        $form .= '<label class="radio">';
        $form .= '<input type="radio"  name="encode" value="utf8" '. $utf8 . ' />';
        $form .=  __('UTF8', 'tainacan') . '</label>';

        $form .= '<label class="radio">';
        $form .= '<input type="radio"  name="encode" value="iso88591" '. $iso . ' />';
        $form .=  __('ISO 8859-1', 'tainacan') . '</label>';

        $form .= '</div>';

        $form .= '<label class="label">' . __('Enclosure character', 'tainacan') . '</label>';
        $form .= '<input type="text" class="input" size="1" name="enclosure" value="' . $this->get_option('enclosure') . '" />';

        return $form;

    }

    /**
     * get the encode option and return as expected
     */
    private function handle_encoding($string){

        switch( $this->get_option('encode') ){

            case 'utf8':
                return $string;

            case 'iso88591':
                return utf8_encode($string);

            default:
                return $string;
        }
    }
}