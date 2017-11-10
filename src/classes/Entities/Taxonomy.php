<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tainacan_Taxonomy extends Entity {

    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Taxonomies';

        if ( is_numeric( $which ) && $which > 0) {
            $post = get_post( $which );
            if ( $post instanceof WP_Post) {
                $this->WP_Post = get_post( $which );
            }

        } elseif ( $which instanceof WP_Post ) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new StdClass();
        }

    }

    // Getters

    function get_id() {
        return $this->get_mapped_property('ID');
    }


    function get_name() {
        return $this->get_mapped_property('name');
    }

    function get_parent() {
        return $this->get_mapped_property('parent');
    }


    function get_description() {
        return $this->get_mapped_property('description');
    }

    function get_allow_insert() {
        return ( boolean ) $this->get_mapped_property('allow_insert');
    }

    function get_taxonomy_name() {
        return $this->get_mapped_property('taxonomy_name');
    }



    // TODO: Uma taxonomia pode estar vinculada a mais de uma coleção
    function get_collection() {
        return new TainacanCollection( $this->get_mapped_property('collection') );
    }

    // Setters


    function set_name($value) {
        return $this->set_mapped_property('name', $value);
    }

    function set_parent($value) {
        return $this->set_mapped_property('parent', $value);
    }


    function set_description($value) {
        return $this->set_mapped_property('description', $value);
    }

    function set_allow_insert($value) {
        return $this->set_mapped_property('allow_insert', $value);
    }

    /**
     * @param TainacanCollection
     *
     * TODO: Uma taxonomia pode estar vinculada a mais de uma coleção
     */
    function set_collection($value) {
        $ID = ($value instanceof  TainacanCollection ) ? $value->get_id() : $value;
        return $this->set_mapped_property('collection', $ID);
    }
}