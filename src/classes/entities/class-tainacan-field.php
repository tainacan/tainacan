<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Field
 */
class Field extends Entity {
	
    // Collection getter and setter declared here
    use \Tainacan\Traits\Entity_Collection_Relation;
	
	public $enabled_for_collection = true;
	
	protected static $post_type = 'tainacan-field';
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Tainacan_Fields';
	
	public function  __toString(){
		return 'Hello, my name is '. $this->get_name();
	}

    /**
     * Return the field name
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }
    
    /**
     * Get field slug
     *
     * @return string
     */
    function get_slug() {
        return $this->get_mapped_property('slug');
    }

    /**
     * Return the field order type
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Return the parent ID
     *
     * @return string
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Return the field description
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }

    /**
     * Return if is a required field
     *
     * @return boolean
     */
    function get_required(){
        return $this->get_mapped_property('required');
    }
    
    /**
     * Return if is a multiple field
     *
     * @return boolean
     */
    function get_multiple(){
        return $this->get_mapped_property('multiple');
    }
    
    /**
     * Return the cardinality
     *
     * @return string
     */
    function get_cardinality(){
        return $this->get_mapped_property('cardinality');
    }
    
    /**
     * Return if field is key
     *
     * @return boolean
     */
    function get_collection_key(){
        return $this->get_mapped_property('collection_key');
    }

    /**
     * Return the mask
     *
     * @return string
     */
    function get_mask(){
        return $this->get_mapped_property('mask');
    }

    /**
     * Return the privacy type
     *
     * @return string
     */
    function get_privacy(){
        return $this->get_mapped_property('privacy');
    }

    /**
     * Return the field default value
     *
     * @return string || integer
     */
    function get_default_value(){
        return $this->get_mapped_property('default_value');
    }

    /**
     * Return the an object child of \Tainacan\Field_Types\Field_Type with options
     *
     * @return \Tainacan\Field_Types\Field_Type The field type class with filled options
     */
    function get_field_type_object(){
        $class_name = $this->get_field_type();
        $object_type = new $class_name();
        $object_type->set_options(  $this->get_field_type_options() );
    	return $object_type;
    }

    /**
     * Return the class name for the field type
     *
     * @return string The
     */
    function get_field_type(){
    	return $this->get_mapped_property('field_type');
    }

    /**
     * Return the actual options for the current field type
     *
     * @return array Configurations for the field type object
     */
    function get_field_type_options(){
        return $this->get_mapped_property('field_type_options');
    }


    /**
     * Set the field name
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Return true if this field allow community suggestions, false otherwise
     * @return bool
     */
    function get_accept_suggestion() {
    	return $this->get_mapped_property('accept_suggestion');
    }
    
    /**
     * Set the field slug
     *
     * If you dont set the field slug, it will be set automatically based on the name and
     * following WordPress default behavior of creating slugs for posts.
     *
     * If you set the slug for an existing one, WordPress will append a number at the end of in order
     * to make it unique (e.g slug-1, slug-2)
     *
     * @param [string] $value
     * @return void
     */
    function set_slug($value) {
        $this->set_mapped_property('slug', $value);
    }

    /**
     * Set manually the order of the field
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Set the field parent ID
     *
     * @param [integer] $value The ID from parent
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Set field description
     *
     * @param [string] $value The text description
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Allow the field be required
     *
     * @param [boolean] $value
     * @return void
     */
    function set_required( $value ){
        $this->set_mapped_property('required', $value);
    }
    
    /**
     * Allow multiple fields
     *
     * @param [boolean] $value
     * @return void
     */
    function set_multiple( $value ){
        $this->set_mapped_property('multiple', $value);
    }
    
    /**
     * The number of  possible fields
     *
     * @param [string] $value
     * @return void
     */
    function set_cardinality( $value ){
        $this->set_mapped_property('cardinality', $value);
    }
    
    /**
     * Define if the value is key on the collection
     *
     * @param [string] $value
     * @return void
     */
    function set_collection_key( $value ){
        $this->set_mapped_property('collection_key', $value);
    }

    /**
     * Set mask for the field
     *
     * @param [string] $value
     * @return void
     */
    function set_mask( $value ){
        $this->set_mapped_property('mask', $value);
    }

    /**
     * Set privacy
     *
     * @param [string] $value
     * @return void
     */
    function set_privacy( $value ){
        $this->set_mapped_property('privacy', $value);
    }

    /**
     * Set default value
     *
     * @param [string || integer] $value
     * @return void
     */
    function set_default_value( $value ){
        $this->set_mapped_property('default_property', $value);
    }

    /**
     * set the field type class name
     *
     * @param string | \Tainacan\Field_Types\Field_Type $value The name of the class or the instance
     */
    public function set_field_type( $value ){
    	$this->set_mapped_property('field_type', ( is_object( $value ) ) ?  get_class( $value ) : $value ) ; // Encode to avoid backslaches removal
    }
    
    /**
     * Set if this field allow community suggestions
     * @param bool $value
     */
    function set_accept_suggestion( $value ) {
    	return $this->set_mapped_property('accept_suggestion', $value);
    }
    
    /**
     * Set Field type options
     *
     * @param [string || integer] $value
     * @return void
     */
    function set_field_type_options( $value ){
        $this->set_mapped_property('field_type_options', $value);
    }
	
	
	/**
	 * Transient property used to store the status of the field for a particular collection
	 *
	 * Used by the API to tell front end when a field is disabled
	 * 
	 */
	public function get_enabled_for_collection() {
		return $this->enabled_for_collection;
	}
	public function set_enabled_for_collection($value) {
		$this->enabled_for_collection = $value;
	}
	

    // helpers

	/**
	 * Return true if is multiple, else return false
	 *
	 * @return boolean
	 */
	function is_multiple() {
        return $this->get_multiple() === 'yes';
    }

	/**
	 * Return true if is collection key, else return false
	 *
	 * @return boolean
	 */
	function is_collection_key() {
        return $this->get_collection_key() === 'yes';
    }

	/**
	 * Return true if is required, else return false
	 * 
	 * @return boolean
	 */
	function is_required() {
        return $this->get_required() === 'yes';
    }
    
    /**
     * {@inheritdoc }  
     * 
     * Also validates the field, calling the validate_options callback of the Field Type
     * 
     * @return bool valid or not
     */
    public function validate() {
        
        $is_valid = parent::validate();
        
        if (false === $is_valid)
            return false;
        
        $fto = $this->get_field_type_object();

        if (is_object($fto)) {
            $is_valid = $fto->validate_options($this);
        }
        
        if (true === $is_valid)
            return true;
            
        if (!is_array($is_valid))
            throw new \Exception("Return of validate_options field type method should be an Array in case of error");

	    $this->add_error('field_type_options', $is_valid);
        
        return false;
            
        
    }
}
