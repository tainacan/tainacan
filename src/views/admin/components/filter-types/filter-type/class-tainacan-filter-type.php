<?php

namespace Tainacan\Filter_Types;
use Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
abstract class Filter_Type {

    private $supported_types = [];

    /**
     * Array of options specific to this filter type. Stored in filter_type_options property of the Filter object
     * @var array
     */
    private $options = [];

    /**
     * The default values for the filter type options array
     * @var array
     */
    private $default_options = [];

    /**
     * The name of the filter type that will be rendered on labels
     * @var string
     */
    private $name;

    /**
     * The name of the web component used by this filter type
     * @var string
     */
    private $component;

    /**
     * The content of the js script associated to the vue component for extra filters
     * @var string
     */
    private $script = '';

    /**
     * The name of the web component used by the Form
     * @var bool | string
     */
    private $form_component = false;

    /**
     * The html template featuring a preview of how this metadata type componenet
     * @var string
     */
    private $preview_template = '';

    /**
     * Defines if the filter type should use the max options
     */
    protected $use_max_options = true;

    /**
     * Defines if the filter type should provide a placeholder for the input field
     */
    protected $use_input_placeholder = true;

    public function __construct(){
        add_action('register_filter_types', array(&$this, 'register_filter_type'));
    }

    /**
     * generate the metadata for this metadatum type
     */
    public function form(){

    }

    /**
     * @param string $name for the filter type
     */
    public function set_name($name){
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
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
     * specifies the preview template for the filter type
     *
     * @param string $preview_template for the filter type
     */
    public function set_preview_template($preview_template){
        $this->preview_template = $preview_template;
    }

    /**
     * @return string
     */
    public function get_preview_template() {
        return $this->preview_template;
    }


    /**
     * @return array
     */
    public function _toArray(){
        $attributes = [];

        $attributes['className']                = get_class($this);
        $attributes['name']                     = $this->get_name();
        $attributes['component']                = $this->get_component();
        $attributes['script']                   = $this->get_script();
        $attributes['options']                  = $this->get_options();
        $attributes['supported_types']          = $this->get_supported_types();
        $attributes['preview_template']         = $this->get_preview_template();
        $attributes['use_max_options']          = $this->get_use_max_options();
        $attributes['use_input_placeholder']    = $this->get_use_input_placeholder();
        $attributes['form_component']           = $this->get_form_component();

        return $attributes;
    }

    /**
     * @param $options
     */
    public function set_options( $options ) {
        $options = $options ?? '';
	    $this->options = ( is_array( $options ) ) ? $options : (!is_array(unserialize( $options )) ? [] : unserialize( $options ));
    }

    public function set_default_options(Array $options) {
        $this->default_options = $options;
    }

	/**
	 * Validates the options Array
	 *
	 * This method should be declared by each filter type sub classes
	 *
	 * @param  \Tainacan\Entities\Filter $filter The metadatum object that is beeing validated
	 *
	 * @return true|array True if options are valid. If invalid, returns an array where keys are the metadatum keys and values are error messages.
	 * @throws \Exception
	 */
    public function validate_options(\Tainacan\Entities\Filter $filter) {
        $metadata_type = $filter->get_metadatum()->get_metadata_type();
        //if there is no metadatum to validate
        if( !$metadata_type ){
            return true;
        }

        $class = ( is_object( $metadata_type ) ) ? $metadata_type : new $metadata_type();

        if(in_array( $class->get_primitive_type(), $this->supported_types  )){
            return true;
        } else {
            return ['unsupported_type' => __('The metadata primitive type is not supported by this filter', 'tainacan')];
        }
    }

	/**
	 * @param mixed $component
	 */
	public function set_component( $component ) {
		$this->component = $component;
    }
   
    /**
	 * @param string $script
	 */
	public function set_script( $script ) {
		$this->script= $script;
	}

    /**
     * Gets the options for this filter types including default values for options
     * that were not set yet.
     * @return array Filter type options
     */
    public function get_options() {
        return array_merge($this->default_options, $this->options);
    }

    public function set_use_max_options($use_max_options) {
        $this->use_max_options = $use_max_options;
    }

    public function get_use_max_options() {
        return $this->use_max_options;
    }

    public function set_use_input_placeholder($use_input_placeholder) {
        $this->use_input_placeholder = $use_input_placeholder;
    }

    public function get_use_input_placeholder() {
        return $this->use_input_placeholder;
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
     * allow i18n from messages
     */
    public function get_form_labels(){
        return [];
    }

    /**
     * @return string 
     */
    public function get_form_component() {
        return $this->form_component;
    }

    /**
     * @return string 
     */
    public function get_script() {
        return $this->script;
    }

    /**
     * @param $form_component The web component that will render the filter options form
     */
    public function set_form_component($form_component){
    	$this->form_component = $form_component;
    }

    public function get_filter_type() {
        return str_replace( 'Tainacan\Filter_Types\\', '', get_class($this) );
    }
}