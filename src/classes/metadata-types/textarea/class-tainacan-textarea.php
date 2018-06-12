<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Textarea extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('long_string');
        $this->set_component('tainacan-textarea');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-textarea 
                        metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                        item_id="'.$itemMetadata->get_item()->get_id().'"    
                        value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                        name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-textarea>';
    }
}