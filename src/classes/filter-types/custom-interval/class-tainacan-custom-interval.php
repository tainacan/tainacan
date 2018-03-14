<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Custom_Interval extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['float','date']);
        $this->set_component('tainacan-filter-custom-interval');
    }

    /**
     * @param $filter
     * @return string
     * @internal param $field
     */
    public function render( $filter ){
         $type = ( $filter->get_field()->get_field_type() === 'Tainacan\Field_Types\Date' ) ? 'date' : 'numeric';
         return '<tainacan-filter-custom-interval 
                                        name="'.$filter->get_name().'"
                                        typeRange="'.$type.'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        field_id="'.$filter->get_field()->get_id().'"></tainacan-filter-custom-interval>';
    }
}