<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


use \Respect\Validation\Validator as v;
/**
 * Class Tainacan_Taxonomies
 */
class Taxonomies extends Repository {
	public $entities_type = '\Tainacan\Entities\Taxonomy';

    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(), [
            'name'            =>  [
                'map'         => 'post_title',
                'title'       => __('Name', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Name of the taxonomy', 'tainacan'),
                'on_error'   => __('The taxonomy should be a text value and not empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'parent'          =>  [
                'map'         => 'parent',
                'title'      => __('Parent', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('Parent taxonomy', 'tainacan'),
                'validation'  => ''
            ],
            'description'     =>  [
                'map'         => 'post_content',
                'title'      => __('Description', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The taxonomy description', 'tainacan'),
                'validation'  => ''
            ],
            'slug'            =>  [
                'map'         => 'post_name',
                'title'      => __('Slug', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The taxonomy slug', 'tainacan'),
                'validation'  => ''
            ],
            'allow_insert'    =>  [
                'map'         => 'meta',
                'title'      => __('Allow insert', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Allow/Deny the creation of new terms in the taxonomy', 'tainacan'),
                'on_error'   => __('Allow insert is invalid, allowed values ( yes/no )', 'tainacan'),
                'validation' => v::stringType()->in(['yes', 'no']), // yes or no
                'default'    => 'yes'
            ],
        	'collections_ids' =>  [
        		'map'         => 'meta_multi',
                'title'      => __('Collections', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The IDs of collection where the taxonomy is used', 'tainacan'),
        		'validation'  => ''
        	],
        ]);
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Taxonomies', 'tainacan'),
            'singular_name'      => __('Taxonomy', 'tainacan'),
            'add_new'            => __('Add new', 'tainacan'),
            'add_new_item'       => __('Add new Taxonomy', 'tainacan'),
            'edit_item'          => __('Edit Taxonomy', 'tainacan'),
            'new_item'           => __('New Taxonomy', 'tainacan'),
            'view_item'          => __('View Taxonomy', 'tainacan'),
            'search_items'       => __('Search Taxonomies', 'tainacan'),
            'not_found'          => __('No Taxonomies found ', 'tainacan'),
            'not_found_in_trash' => __('No Taxonomies found in trash', 'tainacan'),
            'parent_item_colon'  => __('Parent Taxonomy:', 'tainacan'),
            'menu_name'          => __('Taxonomies', 'tainacan')
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
            'supports'            => [
                'title',
                'editor',
                'page-attributes'
            ]
        );
        register_post_type(Entities\Taxonomy::get_post_type(), $args);
    }

    /**
     * @param Entities\Taxonomy $taxonomy
     * @return int
     */
    public function insert($taxonomy) {

    	$new_taxonomy = parent::insert($taxonomy);
        $new_taxonomy->register_taxonomy();
        
        // return a brand new object
        return $new_taxonomy;
    }

    public function tainacan_taxonomy( $taxonomy_name ){
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

    /**
     * fetch taxonomies based on ID or WP_Query args
     *
     * Taxonomies are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
     * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
     * appropriate WP_Query argument
     *
     * @param array $args WP_Query args | int $args the taxonomy id
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return \WP_Query|Array an instance of wp query OR array of entities;
     */
    public function fetch( $args = [], $output = null ) {
        
        // TODO: Pegar taxonomias registradas via código
        
        if( is_numeric($args) ){
            return new Entities\Taxonomy($args);
        } elseif (is_array($args)) {

            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ], $args);

            $args = $this->parse_fetch_args($args);

            $args['post_type'] = Entities\Taxonomy::get_post_type();

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }

    public function update($object){

    }

    public function delete($object){

    }
}