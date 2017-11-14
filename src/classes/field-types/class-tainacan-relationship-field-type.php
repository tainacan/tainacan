<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Tainacan_Relationship_Field_Type extends Tainacan_Field_Type {

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