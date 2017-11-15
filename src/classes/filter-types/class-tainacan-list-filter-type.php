<?php
namespace Tainacan\Filter_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class List_Filter_Type extends Filter_Type {

    function __construct(){
        $this->supported_types[] = 'string';
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $filter ){
        return '<tainacan-filter-list name="'.$filter->get_name().'"></tainacan-filter-list>';
    }
}