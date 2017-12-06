<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Metadata
 */
class Metadata extends Entity {
	
    // Collection getter and setter declared here
    use \Tainacan\Traits\Entity_Collection_Relation;

	protected static $post_type = 'tainacan-metadata';
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Tainacan_Metadatas';
	
	public function  __toString(){
		return 'Hello, my name is '. $this->get_name();
	}

    /**
     * Return the metadata ID
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Return the metadata name
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Return the metadata order type
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
     * Return the metadata description
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }

    /**
     * Return if is a required metadata
     *
     * @return boolean
     */
    function get_required(){
        return $this->get_mapped_property('required');
    }
    
    /**
     * Return if is a multiple metadata
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
     * Return if metadata is key
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
     * Return the metadata default value
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
        $object_type->set_options(  $this->get_field_options() );
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
    function get_field_options(){
        return $this->get_mapped_property('field_type_options');
    }

    /**
     * Set the metadata name
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Set manually the order of the metadata
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Set the metadata parent ID
     *
     * @param [integer] $value The ID from parent
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Set metadata description
     *
     * @param [string] $value The text description
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Allow the metadata be required
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
     * Set mask for the metadata
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
     * save the field type class name
     *
     * @param string | \Tainacan\Field_Types\Field_Type $value The name of the class or the instance
     */
    public function set_field_type( $value ){
    	$this->set_mapped_property('field_type', ( is_object( $value ) ) ?  get_class( $value ) : $value ) ; // Encode to avoid backslaches removal
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
}