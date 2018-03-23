<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Core_Title extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        $this->set_primitive_type('string');
        $this->set_core(true);
        $this->set_related_mapped_prop('title');
        $this->set_component('tainacan-text');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-text 
                               id="tainacan-text-' . $itemMetadata->get_item()->WP_Post->post_name . '"
                               field_id ="'.$itemMetadata->get_field()->get_id().'" 
                               item_id="'.$itemMetadata->get_item()->get_id().'"    
                               value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                               name="'.$itemMetadata->get_field()->get_name().'"></tainacan-text>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){

    }
    
    /**
     * Title core Field type is stored as the item title
     *
     * Lets validate it as the item title
     *
     * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
     * @return bool Valid or not
     */
    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();
        
        if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
        
        $item->set_title($item_metadata->get_value());
        
        return $item->validate_prop('title');
        
    }
    
}