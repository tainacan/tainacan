<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Core_Description extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('string');
        $this->core = true;
        $this->related_mapped_prop = 'description';
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

     public function render( $itemMetadata ){
         return '<tainacan-textarea 
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
    
}