<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Core_Title extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('string');
        $this->set_core(true);
        $this->set_related_mapped_prop('title');
        $this->set_component('tainacan-text');
        $this->set_name( __('Core Description', 'tainacan') );
        $this->set_description( __('The "Core Title" is a compulsory metadata automatically created for all collections by default. It is the main metadatum of the item and where the basic research tools will do their searches.', 'tainacan') );
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-text 
                               id="tainacan-text-' . $itemMetadata->get_item()->WP_Post->post_name . '"
                               metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                               item_id="'.$itemMetadata->get_item()->get_id().'"    
                               value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                               name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-text>';
    }

    /**
     * generate the metadata for this metadatum type
     */
    public function form(){

    }
    
    /**
     * Core title metadatum type is stored as the item title
     *
     * Lets validate it as the item title
     *
     * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
     * @return bool Valid or not
     *
     * Quarantine - Core metadata should be validated as any other metadata
     * and item title is no longer mandatory
    
    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();
        
        if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
        
        $item->set_title($item_metadata->get_value());
        
        return $item->validate_prop('title');
        
    }
	 */

    public function validate_options( Metadatum $metadatum ) {
		
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
        if ( $metadatum->get_multiple() != 'no') {
            return ['multiple' => __('Core Metadata can not accept multiple values', 'tainacan')];
        }
		
		return true;
		
	}
    
}