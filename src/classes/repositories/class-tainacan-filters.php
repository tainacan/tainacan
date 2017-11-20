<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Filters implements Repository {

    function __construct(){
        add_action('init', array(&$this, 'register_post_type'));
    }
    
    function get_map() {
        return [
            'id'                 => [
                'map'        => 'ID',
                //'validation' => ''
            ],
            'name'               => [
                'map'        => 'post_title',
                'validation' => ''
            ],
            'order'              => [
                'map'        => 'menu_order',
                'validation' => ''
            ],
            'description'        => [
                'map'        => 'post_content',
                'validation' => ''
            ],
            'filter_type_object' => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'filter_type'        => [
                'map' => 'meta',
                'validation' => ''
            ],
            'collection_id'      => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'color'              => [
                'map'        => 'meta',
                'validation' => ''
            ],
            'metadata'           => [
                'map'        => 'meta',
                'validation' => ''
            ],
        ];
    }

    function register_post_type(){
        $labels = array(
            'name'               => 'Filter',
            'singular_name'      => 'Filter',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       => 'Adicionar Filters',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Filter',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Filter encontrado na lixeira',
            'parent_item_colon'  => 'Filter acima:',
            'menu_name'          => 'Filters'
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
        register_post_type(Entities\Filter::POST_TYPE, $args);
    }


    /**
     * @param Entities\Metadata $metadata
     * @return int
     */
    function insert($metadata) {
        // First iterate through the native post properties
        $map = $this->get_map();
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $metadata->WP_Post->{$mapped['map']} = $metadata->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $metadata->WP_Post->post_type = Entities\Filter::POST_TYPE;
        $metadata->WP_Post->post_status = 'publish';
        $id = wp_insert_post($metadata->WP_Post);
        $metadata->WP_Post = get_post($id);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $metadata->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $metadata->get_mapped_property($prop);
                
                delete_post_meta($id, $prop);
                
                if (is_array($values)){
                    foreach ($values as $value){
                        add_post_meta($id, $prop, $value);
                    }
                }
            }
        }

        // return a brand new object
        return new Entities\Filter($metadata->WP_Post);
    }

    public function delete($object){

    }

    public function update($object){

    }

    public function fetch($object = [], $args = []){
        /**
         * Se for numérico retorna o objeto filtro
         * Se não, mas se há valor em $object e $args retorna filtro de coleção especifica
         * Se não, mas se for string retorna os filtros pelo tipo de metadado
         * Se não, retorna todos os filtros
         */
        if(is_numeric($object)){
            return new Entities\Filter($object);
        } elseif (!empty($object) && !empty($args)) {
            // TODO: get filters from parent collections

            $collection_id = ( is_object( $object ) ) ? $object->get_id() : $object;

            $args = array_merge([
                'post_type'      => Entities\Filter::POST_TYPE,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_key'       => 'collection_id',
                'meta_value'     => $collection_id
            ], $args);

            $wp_query = new \WP_Query($args);

            return $wp_query;
        } elseif(is_string($object)) {
            $filters = array();
            $filters_type = $this->fetch();
    
            foreach ( $filters_type as $filter_type ){
                if( in_array( $object,  $filter_type->get_supported_types() ) ){
                    $filters[] = $filter_type;
                }
            }
    
            return $filters;
        } else {
            $filters = array();
        
            foreach (get_declared_classes() as $class) {
                if (is_subclass_of($class, '\Tainacan\Filter_Types\Filter_Type')){
                    $filters[] = new $class();
                }
            }

            return $filters;
        }
    }
}