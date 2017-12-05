<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Metadata Entity
 */
class Metadata extends Entity {
	
    // Collection getter and setter declared here
    use \Tainacan\Traits\Entity_Collection_Relation;

	protected static $post_type = 'tainacan-metadata';
	
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

	public function  __toString(){
		return 'Hello, I\'m the Metadata Entity';
	}

    /**
     * Retorna o ID do metadado
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Retorna o nome do metadado
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Retorna a forma de ordenação do metadado
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Retorna o parent do metadado
     *
     * @return string
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Retorna a descrição do metado
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }

    /**
     * Retorna se é metadado obrigatório
     *
     * @return boolean
     */
    function get_required(){
        return $this->get_mapped_property('required');
    }
    
    /**
     * Retorna se é metado multiplo
     *
     * @return boolean
     */
    function get_multiple(){
        return $this->get_mapped_property('multiple');
    }
    
    /**
     * Retorna a cardinalidade
     *
     * @return string
     */
    function get_cardinality(){
        return $this->get_mapped_property('cardinality');
    }
    
    /**
     * Retorna se é metadado chave
     *
     * @return boolean
     */
    function get_collection_key(){
        return $this->get_mapped_property('collection_key');
    }

    /**
     * Retorna a máscara
     *
     * @return string
     */
    function get_mask(){
        return $this->get_mapped_property('mask');
    }

    /**
     * Retorna o nível de privacidade
     *
     * @return string
     */
    function get_privacy(){
        return $this->get_mapped_property('privacy');
    }

    /**
     * Retorna valor padrão do metadado
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
        return $this->get_mapped_property('get_field_options');
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
    function is_multiple() {
        return $this->get_multiple() === 'yes';
    }
    
    function is_collection_key() {
        return $this->get_collection_key() === 'yes';
    }
    
    function is_required() {
        return $this->get_required() === 'yes';
    }
}