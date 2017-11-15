<?php

namespace Tainacan\Filter_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Filter_Type extends \Tainacan\Entity  {

    var $supported_types = [];

    abstract function render( $metadata );

    function get_supported_types(){
        return $this->supported_types;
    }
}