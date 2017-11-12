<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TainacanFieldType
 */
abstract class Tainacan_Field_Type  {

    abstract function render( $metadata );
    
    function validate($value) {
        return true;
    }
    
    function get_validation_errors() {
        return [];
    }
}