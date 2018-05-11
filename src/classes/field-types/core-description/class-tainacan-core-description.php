<?php

namespace Tainacan\Field_Types;

use Tainacan\Entities\Field;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Core_Description extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        $this->set_primitive_type('string');
        $this->set_core(true);
        $this->set_related_mapped_prop('description');
        $this->set_component('tainacan-textarea');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

     public function render( $itemMetadata ){
         return '<tainacan-textarea 
                         id="tainacan-textarea-' . $itemMetadata->get_item()->WP_Post->post_name . '"
                         field_id ="'.$itemMetadata->get_field()->get_id().'" 
                         item_id="'.$itemMetadata->get_item()->get_id().'"    
                         value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                         name="'.$itemMetadata->get_field()->get_name().'"></tainacan-textarea>';
     }

    /**
     * generate the fields for this field type
     */
    public function form(){

    }
    
    /**
     * Description core Field type is stored as the item content (description)
     *
     * Lets validate it as the item description
     *
     * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
     * @return bool Valid or not
     */
    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();
        
        if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
        
        $item->set_description($item_metadata->get_value());
        
        return $item->validate_prop('description');
        
    }

    public function validate_options( Field $field ) {
		
		if ( !in_array($field->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
        if ( $field->get_multiple() != 'no') {
            return ['multiple' => __('Core Metadata can not accept multiple values', 'tainacan')];
        }
		
		return true;
		
	}
    
}