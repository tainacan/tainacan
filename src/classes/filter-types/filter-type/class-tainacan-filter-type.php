<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Filter_Type {

    private $supported_types = [];
    public $options;
    public $component;

    public function __construct(){
        add_action('register_filter_types', array(&$this, 'register_filter_type'));
    }

    abstract function render( $field );

    /**
     * generate the fields for this field type
     */
    public function form(){

    }

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
     * @return mixed
     */
    public function get_component() {
        return $this->component;
    }
    /**
     * @return array
     */
    public function __toArray(){
        $attributes = [];

        $attributes['className'] = get_class($this);
        $attributes['component'] = $this->get_component();
        $attributes['supported_types'] = $this->get_supported_types();

        return $attributes;
    }

    /**
     * @param $options
     */
    public function set_options( $options ){
        $this->options = ( is_array( $options ) ) ? $options : unserialize( $options );
    }
}