<?php

namespace Tainacan\Filter_Types;
use Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Filter_Type {

    private $supported_types = [];
    private $options;
    private $component;

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

	/**
	 * Validates the options Array
	 *
	 * This method should be declared by each filter type sub classes
	 *
	 * @param  \Tainacan\Entities\Filter $filter The field object that is beeing validated
	 *
	 * @return true|Array True if options are valid. If invalid, returns an array where keys are the field keys and values are error messages.
	 * @throws \Exception
	 */
    public function validate_options(\Tainacan\Entities\Filter $filter) {
        $field_type = $filter->get_field()->get_field_type();
        //if there is no field to validate
        if( !$field_type ){
            return true;
        }

        $class = ( is_object( $field_type ) ) ? $field_type : new $field_type();

        if(in_array( $class->get_primitive_type(), $this->supported_types  )){
            return true;
        } else {
            return ['unsupported_type' => __('The field primitive type is not supported by this filter', 'tainacan')];
        }
    }

	/**
	 * @param mixed $component
	 */
	public function set_component( $component ) {
		$this->component = $component;
	}

	/**
	 * @return mixed
	 */
	public function get_options() {
		return $this->options;
	}
}