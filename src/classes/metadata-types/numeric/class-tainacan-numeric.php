<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Numeric extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('float');
        $this->set_component('tainacan-numeric');
        $this->set_name( __('Numeric', 'tainacan') );
        $this->set_description( __('A numeric value, integer or float', 'tainacan') );
        $this->set_preview_template('
            <div>
                <div class="control is-clearfix">
                    <input type="number" placeholder="3,1415" class="input"> 
                </div>
            </div>
        ');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-numeric  
                                   metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                                   item_id="'.$itemMetadata->get_item()->get_id().'"    
                                   value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                                   name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-numeric>';
    }
}