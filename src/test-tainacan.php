<?php
/*
Plugin Name: Tainacan
Plugin URI: 
Description: Lorem Ipsum
Author: MediaLab UFG
Version: 10.9.8.7.6.5.4
*/


class TainacanCollections {
    
    const POST_TYPE = 'tainacan-collections';
    
    function __construct() {
        add_action('init', array(&$this, 'register_post_types'));
    }
    
    function register_post_types() {
        $labels = array(
            'name' => 'Collections',
            'singular_name' => 'Collections',
            'add_new' => 'Adicionar Novo',
            'add_new_item' =>'Adicionar Collections',
            'edit_item' => 'Editar',
            'new_item' => 'Novo Collections',
            'view_item' => 'Visualizar',
            'search_items' => 'Pesquisar',
            'not_found' => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Collections encontrado na lixeira',
            'parent_item_colon' => 'Collections acima:',
            'menu_name' => 'Collections'
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            //'supports' => array('title'),
            //'taxonomies' => array(self::TAXONOMY),
            'public' => true,
            'show_ui' => false,
            'show_in_menu' => false,
            //'menu_position' => 5,
            //'show_in_nav_menus' => false,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'capability_type' => 'post',
        );
        register_post_type(self::POST_TYPE, $args);
    }
    
    function add($title) {
        $post = [
            'post_title' => $title,
            'post_type' => self::POST_TYPE
        ];
        return wp_insert_post($post);
    }
    
    function getCollectionById($id) {
        return get_post($id);
    }
    
}

global $TainacanCollections;
$TainacanCollections = new TainacanCollections();