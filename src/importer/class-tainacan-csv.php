<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

	public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->items_repo = \Tainacan\Repositories\Items::get_instance();
		
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
        $file->seek(0);

        $columns = [];
        $rawColumns = $file->fgetcsv( $this->get_option('delimiter') );

        if( $rawColumns ){
            foreach( $rawColumns as $index => $rawColumn ){
              
                if( strpos($rawColumn,'special_') === 0 ){
                    
                    if( $rawColumn === 'special_document' ){
                        $this->set_option('document_index', $index);
                    } else if( $rawColumn === 'special_attachments' ){
                        $this->set_option('attachment_index', $index);    
                    }

                } else {
                    $columns[] = $rawColumn;
                }
            }

            return $columns;
        }

        return [];
    }

    /**
     * 
     * returns all header including special
     */
    public function raw_source_metadata(){
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->seek(0);
        return $file->fgetcsv( $this->get_option('delimiter') );
    }

    /**
     * @inheritdoc
     */
    public function process_item( $index, $collection_definition ){
        $processedItem = [];
        $headers = $this->raw_source_metadata();

        $this->add_log('Proccessing item index ' . $index . ' in collection ' . $collection_definition['id'] );
        // search the index in the file and get values
        $file =  new \SplFileObject( $this->tmp_file, 'r' );
        $file->setFlags(\SplFileObject::SKIP_EMPTY);
        $file->seek( $index );

        if( $index === 0 ){
            $file->current();
            $file->next();

            $this->add_log(' Delimiter to parse' . $this->get_option('delimiter') );
            $values = str_getcsv( $file->fgets(), $this->get_option('delimiter'), $this->get_option('enclosure') );
        }else{
            $this->add_log(' Delimiter to parse' . $this->get_option('delimiter') );
            $values = str_getcsv( rtrim($file->fgets()), $this->get_option('delimiter'), $this->get_option('enclosure')  );
        }

        if( count( $headers ) !== count( $values ) ){
            $string = (is_array($values)) ? implode('::', $values ) : $values;

            $this->add_log(' Mismatch count headers and row columns ');
            $this->add_log(' Headers count: ' . count( $headers ) );
            $this->add_log(' Values count: ' . count( $values ) );
            $this->add_log(' Values string: ' . $string );
            return false;
        }
        
        foreach ( $collection_definition['mapping'] as $metadatum_id => $header) {
            $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);

            foreach ( $headers as $indexRaw => $headerRaw ) {
               if( $headerRaw === $header ){
                    $index = $indexRaw;
               }
            }
            
            if(!isset($index))
                continue;

            $valueToInsert = $this->handle_encoding( $values[ $index ] );

            $processedItem[ $header ] = ( $metadatum->is_multiple() ) ? 
                explode( $this->get_option('multivalued_delimiter'), $valueToInsert) : $valueToInsert;
        }
        
        $this->add_log('Success to proccess index: ' . $index  );
        $this->add_transient('actual_index', $index); // add reference for insert
        return $processedItem;
    }

    /**
     * insert processed item from source to Tainacan, adapted to insert their attachments and document
     *
     * @param array $processed_item Associative array with metadatum source's as index with
     *                              its value or values
     * @param integer $collection_index The index in the $this->collections array of the collection the item is beeing inserted into
     * 
     * @return Tainacan\Entities\Item Item inserted
     */
    public function insert( $processed_item, $collection_index ) {
        $inserted_item = super::insert( $processed_item, $collection_index );

        $column_document = $this->get_option('document_index');
        $column_attachment = $this->get_option('attachment_index');

        if( !empty($column_document) || !empty( $column_attachment ) ){
            
            $index = $this->get_transient('actual_index');
            $file =  new \SplFileObject( $this->tmp_file, 'r' );
            $file->setFlags(\SplFileObject::SKIP_EMPTY);
            $file->seek( $index );
            $values = str_getcsv( rtrim($file->fgets()), $this->get_option('delimiter'), $this->get_option('enclosure')  );

            if( is_array($values) && !empty($column_document) ){
                $this->handle_document( $values[$column_document], $inserted_item);
            }

            if( is_array($values) && !empty($column_attachment) ){
                $this->handle_attachment( $values[$column_attachment], $inserted_item);
            }
        }

        return $inserted_item;
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

        $form = '<div class="field">';
        $form .= '<label class="label">' . __('Delimiter', 'tainacan') . '</label>';
        $form .= '<div class="control">';
        $form .= '<input type="text" class="input" name="delimiter" value="' . $this->get_option('delimiter') . '" />';
        $form .= '</div>';
        $form .= '</div>';

        $form = '<div class="field">';
        $form .= '<label class="label">' . __('Multivalued metadata delimiter', 'tainacan') . '</label>';
        $form .= '<div class="control">';
        $form .= '<input type="text" class="input" name="multivalued_delimiter" value="' . $this->get_option('multivalued_delimiter') . '" />';
        $form .= '</div>';
        $form .= '</div>';

        $form .= '<div class="field">';   
        $form .= '<label class="label">' . __('Encoding', 'tainacan') . '</label>';
  
        $utf8 = ( !$this->get_option('encode') || $this->get_option('encode') === 'utf8' ) ? 'checked' : '';
        $iso = ( !$this->get_option('encode') && $this->get_option('encode') === 'iso88591' ) ? 'checked' : '';

        $form .= '<div class="field">';
        $form .= '<label class="b-radio radio is-small">';
        $form .= '<input type="radio"  name="encode" value="utf8" '. $utf8 . ' />';
        $form .= '<span class="check"></span>';
        $form .= '<span class="control-label">';
        $form .=  __('UTF8', 'tainacan') . '</span></label>';
        $form .= '</div>';
        
        $form .= '<div class="field">';
        $form .= '<label class="b-radio radio is-small">';
        $form .= '<input type="radio"  name="encode" value="iso88591" '. $iso . ' />';
        $form .= '<span class="check"></span>';
        $form .= '<span class="control-label">';
        $form .=  __('ISO 8859-1', 'tainacan') . '</span></label>';
        $form .= '</div>';

        $form .= '</div>';

        $form .= '<div class="field">';
        $form .= '<label class="label">' . __('Enclosure character', 'tainacan') . '</label>';
        $form .= '<div class="control">';
        $form .= '<input type="text" class="input" size="1" name="enclosure" value="' . $this->get_option('enclosure') . '" />';
        $form .= '</div>';
        $form .= '</div>';

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

    /**
     * method responsible to insert the item document
     */
    private function handle_document($column_value, $item_inserted){
        $TainacanMedia = \Tainacan\Media::get_instance();

        if( strpos($column_value,'url:') === 0 ){
            $correct_value = substr($column_value, 4);
            $item_inserted->set_document( $correct_value );
            $item_inserted->set_document_type( 'url' );

            if( $item_inserted->validate() )
                $this->items_repo->update($item_inserted);

        } else if( strpos($column_value,'text:') === 0 ){
            $correct_value = substr($column_value, 5);
            $item_inserted->set_document( $correct_value );
            $item_inserted->set_document_type( 'text' );
            
            if( $item_inserted->validate() )
                $this->items_repo->update($item_inserted);

        } else if( strpos($column_value,'file:') === 0 ){
            $correct_value = substr($column_value, 5);
            
            if( filter_var($correct_value, FILTER_VALIDATE_URL) ){
                $id = $TainacanMedia->insert_attachment_from_url($correct_value, $item_inserted->get_id());

                if(!$id){
                    $this->add_log('Error in Document file imported from URL ' . $correct_value);
                    return false;
                }

                $item_inserted->set_document( $id );
                $item_inserted->set_document_type( 'attachment' );
                $this->add_log('Document file URL imported from ' . $correct_value);

                if( $item_inserted->validate() )
                    $this->items_repo->update($item_inserted);

                return true;
            } 

            $server_path_files = $this->get_option('server_path');
            $id = $TainacanMedia->insert_attachment_from_file($correct_value, $item_inserted->get_id());

            if(!$id){
                $this->add_log('Error in Document file imported from server ' . $correct_value);
                return false;
            }

            $item_inserted->set_document( $id );
            $item_inserted->set_document_type( 'attachment' );
            $this->add_log('Document file in Server imported from ' . $correct_value);

            if( $item_inserted->validate() )
                $this->items_repo->update($item_inserted);

            return true;
        }
    }

    /**
     * method responsible to insert the item document
     */
    private function handle_attachment( $column_value, $item_inserted){

    }
}