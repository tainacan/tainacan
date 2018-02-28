<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Range extends Filter_Type {

    function __construct(){
        parent::set_supported_types(['float','date']);
        $this->component = 'tainacan-filter-range';
    }

    /**
     * @param $field
     * @return string
     */
    public function render( $filter ){
        return '<tainacan-filter-range 
                                        name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        field_id="'.$filter->get_field()->get_id().'"></tainacan-filter-range>';
    }
}