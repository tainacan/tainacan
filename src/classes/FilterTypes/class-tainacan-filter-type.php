<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}





abstract class Tainacan_Filter_Type extends Entity  {

    abstract function render( $metadata );

    function validate( $value ) {
        return true;
    }

    function get_validation_errors() {
        return [];
    }
}