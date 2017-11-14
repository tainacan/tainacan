<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Tainacan_Radio_Field_Type extends Tainacan_Field_Type {

    function __construct(){
        $this->primitive_type = '';
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $metadata ){
        return '<tainacan-radio name="'.$metadata->get_name().'"></tainacan-radio>';
    }
}