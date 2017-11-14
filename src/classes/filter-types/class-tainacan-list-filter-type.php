<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Tainacan_List_Filter_Type extends Tainacan_Filter_Type {

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