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
        $this->set_primitive_type('string');
        $this->set_component('tainacan-text');
        $this->set_name( __('Control Type', 'tainacan') );
        $this->set_description( __('A special metadata type, used to map certain item properties such as collection id and document type to metadata in order to easily create filters.', 'tainacan') );
        $this->set_default_options([
            'control_metadatum_options' => ['document_type', 'collection_id'],
            'control_metadatum' => 'document_type'
        ]);
        add_action( 'tainacan-api-item-updated', [&$this, 'update_control_metadatum'], 10, 2 );
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
    
}