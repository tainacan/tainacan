<?php

namespace Tainacan\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Tainacan_Taxonomies
 */
class Taxonomies {

    const POST_TYPE = 'tainacan-taxonomies';

    function __construct() {
        add_action('init', array(&$this, 'register_post_type'));
    }
    
    function get_map() {
        return [
            'id'              =>  [
                'map'         => 'ID',
                //'validation'  => ''
            ],
            'name'            =>  [
                'map'         => 'post_title',
                'validation'  => ''
            ],
            'parent'          =>  [
                'map'         => 'parent',
                'validation'  => ''
            ],
            'description'     =>  [
                'map'         => 'post_content',
                'validation'  => ''
            ],
            'slug'            =>  [
                'map'         => 'post_name',
                'validation'  => ''
            ],
            'allow_insert'    =>  [
                'map'         => 'meta',
                'validation'  => ''
            ],
            'collections' =>  [
                'map'         => 'meta_multi',
                'validation'  => ''
            ],
        	'collections_ids' =>  [
        		'map'         => 'meta_multi',
        		'validation'  => ''
        	],
        ];
    }

    function register_post_type() {
        $labels = array(
            'name'               => 'Taxonomy',
            'singular_name'      => 'Taxonomy',
            'add_new'            => 'Adicionar Taxonomy',
            'add_new_item'       =>'Adicionar Taxonomy',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Taxonomy',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Taxonomy encontrado na lixeira',
            'parent_item_colon'  => 'Taxonomy acima:',
            'menu_name'          => 'Taxonomy'
        );

        $args = array(
            'labels'              => $labels,
            'hierarchical'        => true,
            //'supports'          => array('title'),
            //'taxonomies'        => array(self::TAXONOMY),
            'public'              => true,
            'show_ui'             => tnc_enable_dev_wp_interface(),
            'show_in_menu'        => tnc_enable_dev_wp_interface(),
            //'menu_position'     => 5,
            //'show_in_nav_menus' => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'query_var'           => true,
            'can_export'          => true,
            'rewrite'             => true,
            'capability_type'     => 'post',
        );
        register_post_type(self::POST_TYPE, $args);
    }

    function get_taxonomies($args = []){
        $args = array_merge([
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ], $args);
        
        $posts = get_posts($args);
        
        $return = [];
        
        foreach ($posts as $post) {
        	$return[] = new \Tainacan\Entities\Taxonomy($post);
        }
        
        // TODO: Pegar taxonomias registradas via código
        
        return $return;
    }

    /**
     * @param \Tainacan\Entities\Taxonomy $metadata
     * @return int
     */
    function insert( \Tainacan\Entities\Taxonomy $taxonomy ) {
        // First iterate through the native post properties
        $map = $this->get_map();
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $taxonomy->WP_Post->{$mapped['map']} = $taxonomy->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $taxonomy->WP_Post->post_type = self::POST_TYPE;
        $taxonomy->WP_Post->post_status = 'publish';
        
        $id = wp_insert_post($taxonomy->WP_Post);
        $taxonomy->WP_Post = get_post($id);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $taxonomy->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $taxonomy->get_mapped_property($prop);
                
                delete_post_meta($id, $prop);

                if (is_array($values)){
                    foreach ($values as $value){
                        add_post_meta($id, $prop, $value);
                    }
                }
            }
        }
        
        $taxonomy->register_taxonomy();
        
        // return a brand new object
        return new\Tainacan\Entities\Taxonomy($taxonomy->WP_Post);
    }

    function registerTainacanTaxonomy( $taxonomy_name ){
        $labels = array(
            'name'              => __( 'Taxonomies', 'textdomain' ),
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
        );

        register_taxonomy( $taxonomy_name, array( ), $args );
    }

    function get_taxonomy_by_id($id) {
    	return new \Tainacan\Entities\Taxonomy($id);
    }
}