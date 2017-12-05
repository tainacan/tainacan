<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Filter_Type {

    private $supported_types = [];
    public $options;

    abstract function render( $metadata );

    public function get_supported_types(){
        return $this->supported_types;
    }

    public function set_supported_types($supported_types){
        $this->supported_types = $supported_types;
    }

    /**
     * @param $options
     */
    public function set_options( $options ){
        $this->options = ( is_array( $options ) ) ? $options : unserialize( $options );
    }
}