<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Relationship extends Field_Type {


    public $collections;
    public $search_metadata;
    public $avoid_selected_items;
    public $inverse;

    function __construct(){
        parent::set_primitive_type('');
    }

    /**
     * get the collections that the metadata type is related
     *
     * @return mixed
     */
    public function get_collections(){
       return $this->collections;
    }

    /**
     * get the metadata to search items
     *
     * @return mixed
     */
    public function get_search_metadata(){
        return $this->search_metadata;
    }

    /**
     * avoid selected items
     *
     * @return mixed
     */
    public function get_avoid_selected_items(){
        return $this->avoid_selected_items;
    }

    /**
     * verify inverse metadata
     *
     * @return mixed
     */
    public function get_inverse(){
        return $this->inverse;
    }

    /**
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){
        return '<tainacan-relationship name="'.$metadata->get_name().'"></tainacan-relationship>';
    }
}