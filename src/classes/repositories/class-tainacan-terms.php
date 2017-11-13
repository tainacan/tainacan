<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
* Class Tainacan_Terms
*/
class Tainacan_Terms {

    var $map = [
        'term_id'     => [
            'map'        => 'term_id',
            'validation' => ''
        ],
        'name'        => [
            'map'        => 'name',
            'validation' => ''
        ],
        'parent'      => [
            'map'        => 'parent',
            'validation' => ''
        ],
        'description' => [
            'map'        => 'description',
            'validation' => ''
        ],
        'taxonomy'    => [
            'map'        => 'taxonomy',
            'validation' => ''
        ],
        'user'        => [
            'map'        => 'termmeta',
            'validation' => ''
        ],
    ];

    function insert( Tainacan_Term $term ){
        // First iterate through the native post properties
        $map = $this->map;
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'termmeta') {
                $term->WP_Term->{$mapped['map']} = $term->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $term_inserted = wp_insert_term( $term->WP_Term->name, $term->WP_Term->taxonomy, [
            'parent'      => ( isset( $term->WP_Term->parent ) ) ? $term->WP_Term->parent : 0,
            'description' => ( isset( $term->WP_Term->description ) ) ? $term->WP_Term->description : '',
        ]);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'termmeta') {
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