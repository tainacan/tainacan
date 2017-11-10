<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
* Class Tainacan_Terms
*/
class Tainacan_Terms {

    var $map = [
        'term_id' => 'term_id',
        'name' => 'name',
        'parent' => 'parent',
        'description' => 'description',
        'taxonomy' => 'taxonomy',
        'user' => 'termmeta',
    ];

    function insert( Tainacan_Term $term ){
        // First iterate through the native post properties
        $map = $this->map;
        foreach ($map as $prop => $mapped) {
            if ($mapped != 'termmeta') {
                $term->WP_Term->$mapped = $term->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $term_inserted = wp_insert_term( $term->WP_Term->name, $term->WP_Term->taxonomy, [
            'parent' => ( isset( $term->WP_Term->parent ) ) ? $term->WP_Term->parent : 0,
            'description' => ( isset( $term->WP_Term->description ) ) ? $term->WP_Term->description : '',
        ]);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped == 'termmeta') {
                update_term_meta($term_inserted['term_id'], $prop, $term->get_mapped_property($prop));
            }
        }

        return $term_inserted['term_id'];
    }

    function get_terms( $taxonomies, $args ){
        return get_terms( $taxonomies, $args );
    }

    function get_term_by($field,$value,$taxonomy){
        $wp_term = get_term_by($field,$value,$taxonomy);

        $tainacan_term = new Tainacan_Term( $wp_term );
        $tainacan_term->set_user( get_term_meta($tainacan_term->get_id() , 'user', true ) );

        return $tainacan_term;
    }
}

global $Tainacan_Terms;
$Tainacan_Terms = new Tainacan_Terms();