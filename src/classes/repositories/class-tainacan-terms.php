<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
* Class Tainacan_Terms
*/
class Terms extends Repository {
	
	protected $entities_type = '\Tainacan\Entities\Term';
    public function get_map() {
        return [
            'term_id'     => [
                'map'        => 'term_id',
                //'validation' => ''
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
    }
    
    public function insert($term){
        // First iterate through the native post properties
        $map = $this->get_map();
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
        
        do_action('tainacan-insert', $term);
        do_action('tainacan-insert-Term', $term);
        
        return $term_inserted['term_id'];
    }

    /**
     * Get a term or all terms
     *
     * @param string || Array $object1
     * @param string || Array || interger $object2
     * @param string $object3
     * @return Array of WP_Term || WP_Term
     */
    public function fetch( $object1 = '', $object2 = '', $object3  = ''){
        if(!empty($object1) && !empty($object2) && empty($object3)){
            return get_terms( $object1, $object2 );
        } elseif(!empty($object1) && !empty($object2) && !empty($object3)){
            $wp_term = get_term_by($object1, $object2, $object3);

            $tainacan_term = new Entities\Term( $wp_term );
            $tainacan_term->set_user( get_term_meta($tainacan_term->get_id() , 'user', true ) );

            return $tainacan_term;
        }
    }

    public function update($object){

    }

    public function delete($object){

    }
    
    public function register_post_type() { }
}