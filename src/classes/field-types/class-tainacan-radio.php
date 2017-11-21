<?php

namespace Tainacan\Field_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Radio extends Field_Type {

    function __construct(){
        parent::set_primitive_type('');
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $metadata ){
        return '<tainacan-radio name="'.$metadata->get_name().'"></tainacan-radio>';
    }
}