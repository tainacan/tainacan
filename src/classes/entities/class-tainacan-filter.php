<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the entity Filter
 */
class Filter extends Entity {
    use \Tainacan\Traits\Entity_Collection_Relation;
    
    protected static $post_type = 'tainacan-filters';
    
    /**
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::repository
     * @var string
     */
    protected $repository = 'Tainacan_Filters';

	public function  __toString(){
		return 'Hello, my name is '. $this->get_name();
	}

	/**
     * Return the filter ID
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Return the filter name
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

    /**
     * Return the filter order type
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Return the filter color
     *
     * @return string
     */
    function get_color() {
        return $this->get_mapped_property('color');
    }

    /**
     * Return the metadata
     *
     * @return Metadata
     */
    function get_metadata() {
        $id = $this->get_mapped_property('metadata');
        return new Metadata( $id );
    }

    /**
     * Return the an object child of \Tainacan\Filter_Types\Filter_Type with options
     *
     * @return \Tainacan\Filter_Types\Filter_Type The filter type class with filled options
     */
    function get_filter_type_object(){
        $class_name = $this->get_filter_type();
        $object_type = new $class_name();
        $object_type->set_options(  $this->get_filter_options() );
        return $object_type;
    }

    /**
     * Return the class name for the filter type
     *
     * @return string The
     */
    function get_filter_type(){
        return $this->get_mapped_property('filter_type');
    }

    /**
     * Return the actual options for the current filter type
     *
     * @return array Configurations for the filter type object
     */
    function get_filter_options(){
        return $this->get_mapped_property('filter_type_options');
    }

    /**
     * Define the filter name
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Define the filter order type
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Define the filter description
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Define the filter color
     *
     * @param [string] $value
     * @return void
     */
    function set_color( $value ) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Define the filter metadata
     * 
     * @param \Tainacan\Entities\Metadata
     * @return void
     */
    function set_metadata( $value ){
    	$id = ( $value instanceof Metadata ) ? $value->get_id() : $value;

        $this->set_mapped_property('metadata', $id);
    }

    /**
     * Save the filter type class name
     *
     * @param string | \Tainacan\Filter_Types\Filter_Type $value The name of the class or the instance
     */
    public function set_filter_type($value){
        $this->set_mapped_property('filter_type', ( is_object( $value ) ) ? get_class( $value ) : $value );
    }
}