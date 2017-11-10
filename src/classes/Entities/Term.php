<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tainacan_Term extends Entity {

    function __construct($which = 0, $taxonomy = '' ) {

        $this->repository = 'Tainacan_Terms';
        $this->set_taxonomy( $taxonomy );

        if ( is_numeric( $which ) && $which > 0) {
            $post = get_term_by('id', $which, $taxonomy);
            if ( $post instanceof WP_Term) {
                $this->WP_Term = get_term_by('id', $which, $taxonomy);
            }

        } elseif ( $which instanceof WP_Term ) {
            $this->WP_Term = $which;
        } else {
            $this->WP_Term = new StdClass();
        }

    }

    // Getters

    function get_id() {
        return $this->get_mapped_property('term_id');
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

    function get_user() {
        return $this->get_mapped_property('user');
    }

    function get_taxonomy() {
        return $this->get_mapped_property('taxonomy');
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

    function set_user($value) {
        return $this->set_mapped_property('user', $value);
    }

    function set_taxonomy($value) {
        return $this->set_mapped_property('taxonomy', $value);
    }
}
