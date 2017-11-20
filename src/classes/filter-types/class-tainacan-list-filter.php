<?php
namespace Tainacan\Filter_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 * List_Filter because List is reseved
 */
class List_Filter extends Filter_Type {

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