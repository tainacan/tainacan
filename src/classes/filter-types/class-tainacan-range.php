<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Range extends Filter_Type {

    function __construct(){
        parent::set_supported_types(['float','date']);
    }

    /**
     * @param $field
     * @return string
     */
    public function render( $filter ){
        return '<tainacan-filter-range name="'.$filter->get_name().'"></tainacan-filter-range>';
    }
}