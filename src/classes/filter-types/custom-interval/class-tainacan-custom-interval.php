<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Custom_Interval extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['float','date']);
        $this->set_component('tainacan-filter-custom-interval');
    }

    /**
     * @param $filter
     * @return string
     * @internal param $metadatum
     */
    public function render( $filter ){
         $type = ( $filter->get_metadatum()->get_metadatum_type() === 'Tainacan\Metadatum_Types\Date' ) ? 'date' : 'numeric';
         return '<tainacan-filter-custom-interval 
                                        name="'.$filter->get_name().'"
                                        typeRange="'.$type.'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-custom-interval>';
    }
}