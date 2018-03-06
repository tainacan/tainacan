<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
abstract class Field_Type  {

    
    /**
     * Indicates the type of variable that this field type handles.
     *
     * This is used to relate Field types and filter types, so we know which filter types
     * will be available to be used for each field based on its Field Type
     *
     * For instance, the Filter Type "input text" may be used to search in any field that has
     * a Field Type with a string primitive type.
     * 
     * @var string
     */
    private $primitive_type;
    
    /**
     * Array of options spececific to this field type. Stored in field_type_options property of the Field object
     * @var Array
     */
    public $options = [];
    
    /**
     * The default values for the field type options array
     * @var Array
     */
    public $default_options = [];
    
    public $errors;
    
    /**
     * Indicates wether this is a core Field Type or not
     *
     * Core field types are used by Title and description fields. These fields:
     * * Can only be used once, they belong to the repository and can not be deleted
     * * Its values are saved in th wp_post table, and not as post_meta 
     * 
     */
    public $core = false;
    
    /**
     * Used by core field types to indicate where it should be saved
     */
    public $related_mapped_prop = false;
    
    /**
     * The name of the web component used by this field type
     * @var string
     */
    public $component;

    /**
     * The name of the web component used by the Form
     * @var bool | string
     */
    public $form_component = false;
    
    abstract function render( $itemMetadata );

    public function __construct(){
        
    }

    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        return true;
    }
    
    public function get_validation_errors() {
        return [];
    }

    public function get_primitive_type(){
        return $this->primitive_type;
    }

    public function set_primitive_type($primitive_type){
        $this->primitive_type = $primitive_type;
    }

    public function get_errors() {
        return $this->errors;
    }
    
    public function get_component() {
        return $this->component;
    }

    public function get_form_component() {
        return $this->form_component;
    }

    /**
     * @param $options
     */
    public function set_options( $options ){
        $this->options = ( is_array( $options ) ) ? $options : unserialize( $options );
    }
    
    public function set_default_options(Array $options) {
        $this->default_options = $options;
    }
    
    /**
     * Gets the options for this field types, including default values for options
     * that were not set yet.
     * @return Array Fielt type options
     */
    public function get_options() {
        return array_merge($this->default_options, $this->options);
    }
    
    /**
     * Gets one option from the options array.
     *
     * Checks if option exist or if it have a default value. Otherwise return an empty string
     * 
     * @param  string $key the desired option
     * @return mixed the option value, the default value or an empty string
     */
    public function get_option($key) {
        $options = $this->get_options();
        return isset($options[$key]) ? $options[$key] : '';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){

    }
    
    public function __toArray(){
	    $attributes = [];
        
        $attributes['className'] = get_class($this);
        $attributes['core'] = $this->core;
        $attributes['component'] = $this->get_component();
        $attributes['primitive_type'] = $this->get_primitive_type();
        $attributes['form_component'] = $this->get_form_component();

        return $attributes;
        
    }
    
    /**
     * Validates the options Array
     *
     * This method should be declared by each field type sub classes
     * 
     * @param  \Tainacan\Entities\Field  $field The field object that is beeing validated
     * @return true|Array True if optinos are valid. If invalid, returns an array where keys are the field keys and values are error messages.
     */
    public function validate_options(\Tainacan\Entities\Field $field) {
        return true;
    }

}