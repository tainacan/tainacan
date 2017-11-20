<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Respect\Validation\Validator as v;

class Collections implements Repository {
    
    var $map;

    function __construct() {
        add_action('init', array(&$this, 'tainacan_register_post_type'));
    }
    
    function get_map() {
        return [
            'id'             => [
                'map'        => 'ID',
                //'validation' => v::numeric(),
            ],
            'name'           =>  [
                'map'        => 'post_title',
                'validation' => v::stringType(),
            ],
            'order'          =>  [
                'map'        => 'menu_order',
                //'validation' => v::stringType(),
            ],
            'parent'         =>  [
                'map'        => 'parent',
                //'validation' => v::stringType(),
            ],
            'description'    =>  [
                'map'        => 'post_content',
                //'validation' => v::stringType(),
            ],
            'slug'           =>  [
                'map'        => 'post_name',
                //'validation' => v::stringType(),
            ],
            'itens_per_page' =>  [
                'map'        => 'meta',
                'default'    => 10,
                'validation' => v::intVal()->positive(),
            ],
        ];
    }

    function tainacan_register_post_type() {
        $labels = array(
            'name'               => 'Collections',
            'singular_name'      => 'Collections',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       => 'Adicionar Collections',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Collections',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Collections encontrado na lixeira',
            'parent_item_colon'  => 'Collections acima:',
            'menu_name'          => 'Collections'
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
        register_post_type(Entities\Collection::POST_TYPE, $args);
    }
    
    function insert($collection) {
        
        // validate
        if (!$collection->validate()){
            return $collection->get_errors();
        }
            // TODO: Throw Warning saying you must validate object before insert()
        
        $map = $this->get_map();
        
        // First iterate through the native post properties
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $collection->WP_Post->{$mapped['map']} = $collection->get_mapped_property($prop);
            }
        }
        
        // save post and geet its ID
        $collection->WP_Post->post_type = Entities\Collection::POST_TYPE;
        $collection->WP_Post->post_status = 'publish';
        
        // TODO verificar se salvou mesmo
        $id = wp_insert_post($collection->WP_Post);

        // reset object
        $collection->WP_Post = get_post($id);
        
        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $collection->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $collection->get_mapped_property($prop);
                
                delete_post_meta($id, $prop);
                
                if (is_array($values)){
                    foreach ($values as $value){
                        add_post_meta($id, $prop, $value);
                    }
                }
            }
        }
        
        $collection->tainacan_register_post_type();
        
        // return a brand new object
        return new Entities\Collection($collection->WP_Post);
    }

    public function update($object){

    }

    public function delete($object){

    }

    /**
     * Obtém um coleção específica pelo ID ou várias coleções
     *
     * @param array $object || int $object
     * @return Array || Collection 
     */
    public function fetch($object = []){
        if(is_numeric($object)){
            return new Entities\Collection($object);
        } elseif(is_array($object)) {
            $args = array_merge([
                'post_type'      => Entities\Collection::POST_TYPE,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ], $object);
            
            $posts = get_posts($args);
            
            $collections = [];
            foreach ($posts as $post) {
                $collections[] = new Entities\Collection($post);
            }
            
            // TODO: Pegar coleções registradas via código
            
            return $collections;
        }
    }
}