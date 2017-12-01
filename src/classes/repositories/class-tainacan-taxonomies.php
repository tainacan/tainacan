<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Tainacan_Taxonomies
 */
class Taxonomies extends Repository {
	protected $entities_type = '\Tainacan\Entities\Taxonomy';

    public function get_map() {
    	return apply_filters('tainacan-get-map', [
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
        	'collections_ids' =>  [
        		'map'         => 'meta_multi',
        		'validation'  => ''
        	],
        ]);
    }

    public function register_post_type() {
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
        } elseif (!empty($args)) {

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