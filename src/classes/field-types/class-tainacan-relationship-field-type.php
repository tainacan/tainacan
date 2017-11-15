<?php

namespace Tainacan\Field_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Relationship_Field_Type extends Field_Type {

    function __construct(){
        $this->primitive_type = '';
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $metadata ){
        return '<tainacan-relationship name="'.$metadata->get_name().'"></tainacan-relationship>';
    }
}