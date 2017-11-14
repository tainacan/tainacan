<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Tainacan_Filter_Type extends Entity  {

    var $supported_types = [];

    abstract function render( $metadata );
}