<?php

namespace Tainacan\Field_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Text extends Field_Type {

    function __construct(){
        parent::set_primitive_type('string');
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $metadata ){
        return '<tainacan-text name="'.$metadata->get_name().'"></tainacan-text>';
    }
}