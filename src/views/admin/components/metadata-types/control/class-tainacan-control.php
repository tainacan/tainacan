<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Control extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('control');
        $this->set_component('tainacan-text');
        $this->set_name( __('Control Type', 'tainacan') );
        $this->set_description( __('A special metadata type, used to map certain item properties such as collection id and document type to metadata in order to easily create filters.', 'tainacan') );
        $this->set_default_options([
            'control_metadatum_options' => ['document_type', 'collection_id'],
            'control_metadatum' => 'document_type'
        ]);
        add_action( 'tainacan-api-item-updated', [&$this, 'update_control_metadatum'], 10, 2 );
        add_filter( 'tainacan-item-get-control-metadatum', [&$this, 'get_control_metadatum_value'], 10, 2 );
    }

    public function update_control_metadatum( \Tainacan\Entities\Item $item, $attributes) {

        if ( $item instanceof \Tainacan\Entities\Item ) {

            $metadata = $item->get_metadata();
            foreach ($metadata as $item_metadatum) {

                if ( $item_metadatum->get_metadatum()->get_metadata_type_object() instanceof \Tainacan\Metadata_Types\Control && $item_metadatum->get_metadatum()->get_metadata_type_options()['control_metadatum'] == $this->get_option('control_metadatum')) {
                    
                    $update_item_metadatum = new \Tainacan\Entities\Item_Metadata_Entity( $item, $item_metadatum->get_metadatum() );
                    switch ( $this->get_option('control_metadatum') ) {
                        case 'document_type':
                            $update_item_metadatum->set_value( $item->get_document_type() );
                        break;

                        case 'collection_id':
                            $update_item_metadatum->set_value( $item->get_collection_id() );
                        break;
                        
                        default:
                            // What the hell am I doing here?
                        break;
                    }
                            
                    if ( $update_item_metadatum->validate() )
                        \Tainacan\Repositories\Item_Metadata::get_instance()->insert( $update_item_metadatum );
                    else
                        $errors[] = $update_item_metadatum->get_errors();

                    break; // Ends foreach in case we already found the related metadata
                }
            }
        }
    }

    public function validate_options( Metadatum $metadatum ) {
		
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
        if ( empty($this->get_option('control_metadatum')) ) {
            return [
                'control_metadatum' => __('Required control metadatum.','tainacan')
            ];
        }
		
		return true;
		
    }
    
    	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, containing one or multiple items names, linked to the item page
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		
		return $this->get_control_metadatum_value($item_metadata->get_value(), $this->get_option('control_metadatum') );
    }

    public function get_control_metadatum_value($value, $control_metadatum) {

        $return = '';
        
        switch ( $control_metadatum ) {
            case 'document_type':
                $return = $this->get_document_as_html( $value );
            break;

            case 'collection_id':
                $return = $this->get_collection_as_html( $value );
            break;
            
            default:
                // What the hell am I doing here?
            break;
        }
		
		return $return;
		
    }

    private function get_document_as_html( $value ) {

        switch ($value) {
            case 'attachment':
                return __( 'File', 'tainacan' );
            break;
            
            case 'text':
                return __( 'Text', 'tainacan' );
            break;
            
            case 'url':
                return __( 'URL', 'tainacan' );
            break;

            default: 
                return $value;
        }
    }
    
    private function get_collection_as_html( $value ) { 	

        
        $collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $value );
        if ( $collection instanceof \Tainacan\Entities\Collection ) {
            $label = $collection->get_name();
            $link = $collection->get_url();
            error_log($label);
            $return = "<a data-linkto='collection' data-id='$value' href='$link'>";
            $return.= $label;
            $return .= "</a>";
            
            return $return;
        }
		
    }
    
}