<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Metadatas
 */
class Metadatas extends Repository {
	protected $entities_type = '\Tainacan\Entities\Metadata';
	
    public function get_map() {
        return [
            'id'             => [
                'map'        => 'ID',
                //'validation' => ''
            ],
            'name'           => [
                'map'        => 'post_title',
                'validation' => ''
            ],
            'order'          => [
                'map'        => 'menu_order',
                'validation' => ''
            ],
            'parent'         => [
                'map'        => 'parent',
                'validation' => ''
            ],
            'description'    => [
                'map'        => 'post_content',
                'validation' => ''
            ],
            'field_type'     => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'required'       => [
                'map'        => 'meta',
                'validation' => '', // yes or no
                'default'    => 'no'
            ],
            'collection_key' => [
                'map'        => 'meta',
                'validation' => '', // yes or no. it cant be key if its multiple
                'default'    => 'no'
            ],
            'multiple'       => [
                'map'        => 'meta',
                'validation' => '', // yes or no. It cant be multiple if its collection_key
                'default'    => 'no'
            ],
            'cardinality'    => [
                'map'        => 'meta',
                'validation' => '',
                'default'    => 1
            ],
            'privacy'        => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'mask'           => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'default_value'  => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'field_type_object' => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'collection_id'  => [
                'map'        => 'meta',
                'validation' => ''
            ],
        ];
    }

    public function register_post_type() {
        $labels = array(
            'name'               => 'Metadata',
            'singular_name'      => 'Metadata',
            'add_new'            => 'Adicionar Metadata',
            'add_new_item'       =>'Adicionar Metadata',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Metadata',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Collections encontrado na lixeira',
            'parent_item_colon'  => 'Metadata acima:',
            'menu_name'          => 'Metadata'
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
            'publicly_queryable'  => true,
            'exclude_from_search' => true,
            'has_archive'         => true,
            'query_var'           => true,
            'can_export'          => true,
            'rewrite'             => true,
            'capability_type'     => 'post',
        );
        register_post_type(Entities\Metadata::get_post_type(), $args);
    }


    /**
     * fetch metadata based on ID or WP_Query args
     *
     * metadata are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter
     *
     * @param array $args WP_Query args || int $args the metadata id
     * @return \WP_Query an instance of wp query
     */
    public function fetch( $args ) {

        if( is_numeric($args) ){
            return new Entities\Metadata($args);
        } elseif (!empty($args)) {

            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ], $args);

            $args['post_type'] = Entities\Metadata::get_post_type();

            return new \WP_Query($args);
        }
    }

    /**
     * fetch metadata by collection
     *
     * @param Entities\Collection $collection
     * @param array $args
     * @return Array Entities\Metadata
     */
    public function fetch_by_collection(Entities\Collection $collection, $args = []){
        $metadata = [];
        $collection_id = $collection->get_id();

        $args = array_merge([
            'meta_key'       => 'collection_id',
            'meta_value'     => $collection_id
        ], $args);

        $wp_query = $this->fetch($args);

        if ( $wp_query->have_posts() ){
            while ( $wp_query->have_posts() ) {
                $wp_query->the_post();
                $metadata[] = new Entities\Metadata(  get_the_ID() );
            }
        }

        return $metadata;

    }

    public function update($object){

    }

    public function delete($object){

    }
}