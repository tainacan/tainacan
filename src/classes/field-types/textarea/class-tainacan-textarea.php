<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Textarea extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        $this->set_primitive_type('string');
        $this->set_component('tainacan-textarea');
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
}