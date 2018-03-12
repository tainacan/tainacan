<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Numeric extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('float');
        $this->component = 'tainacan-numeric';
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-numeric  
                                   field_id ="'.$itemMetadata->get_field()->get_id().'" 
                                   item_id="'.$itemMetadata->get_item()->get_id().'"    
                                   value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                                   name="'.$itemMetadata->get_field()->get_name().'"></tainacan-numeric>';
    }
}