<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Metada entity
 */
class Metadata extends \Tainacan\Entity {

	use \Tainacan\Traits\Entity_Collection_Relation;
    
    /**
     * Create an instance of Metadata
     * @param integer|\WP_Post optional $which Metadata ID or a WP_Post object for existing metadata. Leave empty to create a new metadata.
     */
    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Metadatas';

        if ( is_numeric( $which ) && $which > 0) {
            $post = get_post( $which );
            if ( $post instanceof \WP_Post) {
                $this->WP_Post = get_post( $which );
            }

        } elseif ( $which instanceof \WP_Post ) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new \StdClass();
        }

    }

    /**
     * Get metadata ID
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Get metadata name
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Get metadata order
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Get metadata parent ID
     *
     * @return string
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Get metadata description
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }

    /**
     * Get metadata required option
     *
     * @return boolean
     */
    function get_required(){
        return $this->get_mapped_property('required');
    }
    
    /**
     * Get metadata multiple option
     *
     * @return string 'yes or no'
     */
    function get_multiple(){
        return $this->get_mapped_property('multiple');
    }
    
    /**
     * Get metadata cardinality option
     *
     * @return string
     */
    function get_cardinality(){
        return $this->get_mapped_property('cardinality');
    }
    
    /**
     * Get metadata collection key option
     *
     * @return string 'yes or no'
     */
    function get_collection_key(){
        return $this->get_mapped_property('collection_key');
    }

    /**
     * Get metadata mask option
     *
     * @return string
     */
    function get_mask(){
        return $this->get_mapped_property('mask');
    }

    /**
     * Get metadata privacy option
     *
     * @return string
     */
    function get_privacy(){
        return $this->get_mapped_property('privacy');
    }

    /**
     * Get metadata default value option
     *
     * @return string || integer
     */
    function get_default_value(){
        return $this->get_mapped_property('default_value');
    }

    /**
     * Get metadata field type object with its options
     *
     * @return object
     */
    function get_field_type_object(){
    	return unserialize(base64_decode( $this->get_mapped_property('field_type_object') ) );
    }

    /**
     * Get metadata field type
     *
     * @return string The Field Type class name
     */
    function get_field_type(){
    	return base64_decode($this->get_mapped_property('field_type'));
    }

    /**
     * Set metadata name
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Set metadata order
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Set metadata parent ID
     *
     * @param [integer] $value
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Set metadata description
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Set metadata required option
     *
     * @param [string] $value yes or no
     * @return void
     */
    function set_required( $value ){
        $this->set_mapped_property('required', $value);
    }
    
    /**
     * Set metadata multiple option
     *
     * @param [string] $value yes or no
     * @return void
     */
    function set_multiple( $value ){
        $this->set_mapped_property('multiple', $value);
    }
    
    /**
     * Set metadata cardinality
     *
     * @param [int] $value
     * @return void
     */
    function set_cardinality( $value ){
        $this->set_mapped_property('cardinality', $value);
    }
    
    /**
     * Set metadata collection key option
     *
     * Set to yes if you want this metadata to have a unique value inside the collection.
     *
     * This means that whenever you create or update an item, Tainacan will check and make sure there is no
     * other item with the same value for this metadata.
     *
     * @param [string] $value yes or no
     * @return void
     */
    function set_collection_key( $value ){
        $this->set_mapped_property('collection_key', $value);
    }

    /**
     * Set metadata mask option
     *
     * @param [string] $value
     * @return void
     */
    function set_mask( $value ){
        $this->set_mapped_property('mask', $value);
    }

    /**
     * Set metadata privacy option
     *
     * @param [string] $value
     * @return void
     */
    function set_privacy( $value ){
        $this->set_mapped_property('privacy', $value);
    }

    /**
     * Set metadata default value
     *
     * @param [string || integer] $value
     * @return void
     */
    function set_default_value( $value ){
        $this->set_mapped_property('default_property', $value);
    }


    /**
     * Set metadata field type object
     *
     * stores the Field Type object with all its options.
     *
     * @param [\Tainacan\Field_Types\Field_Type] $value An object of a Field Type. Must be an instace of a class child of Field_Type
     * @return void
     */
    function set_field_type_object(\Tainacan\Field_Types\Field_Type $value){
        $this->set_field_type( get_class( $value )  );
        $this->set_mapped_property('field_type_object', base64_encode( serialize($value) ) ); // Encode to avoid backslaches removal
    }

    /**
     * This is a private methos, called by @method set_field_type_object() to set the field type 
     *
     * @param $value
     */
    private function set_field_type($value){
    	$this->set_mapped_property('field_type',   base64_encode($value) ) ; // Encode to avoid backslaches removal
    }

    // helpers
    // 
    /**
     * Returns wether this metadata is multiple or not (has the multiple option set to yes or no)
     * @return boolean true if multiple, false if not multiple
     */
    function is_multiple() {
        return $this->get_multiple() === 'yes';
    }
    
    /**
     * Returns wether this metadata is a collection key or not (has the collection key option set to yes or no)
     * @return boolean true if collection key, false if not collection key
     */
    function is_collection_key() {
        return $this->get_collection_key() === 'yes';
    }
    
    /**
     * Returns wether this metadata is required or not (has the required option set to yes or no)
     * @return boolean true if required, false if not required
     */
    function is_required() {
        return $this->get_required() === 'yes';
    }
}