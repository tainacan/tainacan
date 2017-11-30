<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Metadatas
 */
class Metadatas extends Repository {
	protected $entities_type = '\Tainacan\Entities\Metadata';
	protected $default_metadata = 'default';
	
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
     * constant used in default metadata in attribute collection_id
     *
     * @return string the value of constant
     */
    public function get_default_metadata_attribute(){
        return $this->default_metadata;
    }


    /**
     * fetch metadata based on ID or WP_Query args
     *
     * metadata are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
     * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
     * appropriate WP_Query argument
     *
     * @param array $args WP_Query args || int $args the metadata id
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return \WP_Query|Array an instance of wp query OR array of entities;
     */
    public function fetch( $args, $output = null ) {

        if( is_numeric($args) ){
            return new Entities\Metadata($args);
        } elseif (!empty($args)) {

            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ], $args);

            $args['post_type'] = Entities\Metadata::get_post_type();

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }

    /**
     * fetch metadata by collection, searches all metadata available
     *
     * @param Entities\Collection $collection
     * @param array $args
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return Array Entities\Metadata
     */
    public function fetch_by_collection(Entities\Collection $collection, $args = [], $output = null){
        $collection_id = $collection->get_id();

        //get parent collections
        $parents = get_post_ancestors( $collection_id );

        //insert the actual collection
        $parents[] = $collection_id;

        //search for default metadata
        $parents[] = $this->get_default_metadata_attribute();

        $meta_query = array(
            'key'     => 'collection_id',
            'value'   => $parents,
            'compare' => 'IN',
        );

        if( isset( $args['meta_query'] ) ){
            $args['meta_query'][] = $meta_query;
        }else{
            $args['meta_query'] = array( $meta_query );
        }

        return $this->fetch( $args, $output );
    }

    public function update($object){

    }

    public function delete($object){

    }
}