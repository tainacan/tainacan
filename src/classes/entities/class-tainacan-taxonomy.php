<?php

namespace Tainacan\Entities;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Taxonomy extends \Tainacan\Entity {

	use \Tainacan\Traits\Entity_Collections_Relation;
    
    function __construct( $which = 0 ) {

        $this->repository = 'Tainacan_Taxonomies';

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
    
    function register_taxonomy() {
        $labels = array(
            'name'              => $this->get_name(),
            'singular_name'     => __( 'Taxonomy','textdomain' ),
            'search_items'      => __( 'Search taxonomies', 'textdomain' ),
            'all_items'         => __( 'All taxonomies', 'textdomain' ),
            'parent_item'       => __( 'Parent taxonomy', 'textdomain' ),
            'parent_item_colon' => __( 'Parent taxonomy:', 'textdomain' ),
            'edit_item'         => __( 'Edit taxonomy', 'textdomain' ),
            'update_item'       => __( 'Update taxonomy', 'textdomain' ),
            'add_new_item'      => __( 'Add New taxonomy', 'textdomain' ),
            'new_item_name'     => __( 'New Genre taxonomy', 'textdomain' ),
            'menu_name'         => __( 'Genre', 'textdomain' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => tnc_enable_dev_wp_interface(),
            'show_admin_column' => tnc_enable_dev_wp_interface(),
            'rewrite'           => [
                'slug' => $this->get_slug()
            ],
        );
        
        
        $tax_cpts = [];
        if (is_array($this->fetch())){
            foreach ($this->fetch() as $tax_col){
                $tax_cpts[] = $tax_col->get_db_identifier();
            }
        }
        
        if (taxonomy_exists($this->get_db_identifier())){
            unregister_taxonomy($this->get_db_identifier());
        }
        
        register_taxonomy( 
            $this->get_db_identifier(), 
            $tax_cpts, 
            $args 
        );
        
        return true;
    }

    // Getters
    function get_id() {
        return $this->get_mapped_property('id');
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

    function get_slug() {
        return $this->get_mapped_property('slug');
    }
    
    // special Getters
    function get_db_identifier() {
        return $this->get_id() ? 'tnc_tax_' . $this->get_id() : false;
    }

    // Setters
    function set_name($value) {
        return $this->set_mapped_property('name', $value);
    }

    function set_parent($value) {
        return $this->set_mapped_property('parent', $value);
    }
    
    function set_slug($value) {
        return $this->set_mapped_property('slug', $value);
    }

    function set_description($value) {
        return $this->set_mapped_property('description', $value);
    }

    function set_allow_insert($value) {
        return $this->set_mapped_property('allow_insert', $value);
    }
}