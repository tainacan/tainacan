<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Helpers;
/**
 * Class TainacanMetadatumType
 */
class Date extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('date');
        $this->set_component('tainacan-date');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-date metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                               item_id="'.$itemMetadata->get_item()->get_id().'"    
                               value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                               name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-date>';
    }


    public function validate( Item_Metadata_Entity $item_metadata) {
        
        $value = $item_metadata->get_value();

        $format = 'Y-m-d';

        $d = \DateTime::createFromFormat($format, $value);

        return $d && $d->format($format) === $value;
        
    }


}