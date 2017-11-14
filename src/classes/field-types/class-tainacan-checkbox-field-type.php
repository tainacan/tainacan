<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
class Tainacan_Checkbox_Field_Type extends Tainacan_Field_Type {

    function __construct(){
        $this->primitive_type = 'date';
    }

    /**
     * @param $metadata
     * @return string
     */

    function render( $metadata ){
        return '<tainacan-checkbox name="'.$metadata->get_name().'"></tainacan-checkbox>';
    }
}