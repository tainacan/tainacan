<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Tainacan_Filter_Type extends Tainacan_Entity  {

    var $supported_types = [];

    abstract function render( $metadata );

    function get_supported_types(){
        return $this->supported_types;
    }
}