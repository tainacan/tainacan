<?php

namespace Tainacan\Field_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Numeric extends Field_Type {

    function __construct(){
        $this->primitive_type = 'float';
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $metadata ){
        return '<tainacan-numeric name="'.$metadata->get_name().'"></tainacan-numeric>';
    }
}