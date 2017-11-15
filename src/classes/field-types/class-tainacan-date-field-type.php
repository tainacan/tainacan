<?php

namespace Tainacan\Field_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Textarea_Field_Type extends Field_Type {

    function __construct(){
        $this->primitive_type = 'date';
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $metadata ){
        return '<tainacan-date name="'.$metadata->get_name().'"></tainacan-date>';
    }
}