<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Filter_Type {

    private $supported_types = [];
    public $options;

    abstract function render( $field );

    /**
     * @return array Supported types by the filter
     */
    public function get_supported_types(){
        return $this->supported_types;
    }

    /**
     * specifies the types supported for the filter
     *
     * @param array $supported_types the types supported
     */
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