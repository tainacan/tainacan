<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class TainacanCollections {
    
    const POST_TYPE = 'tainacan-collections';
    
    var $map = [
        'ID' => [
            'map' => 'ID',
            'validation' => ''
        ],
        'name' =>  [
            'map' => 'post_title',
            'validation' => ''
        ],
        'order' =>  [
            'map' => 'menu_order',
            'validation' => ''
        ],
        'parent' =>  [
            'map' => 'parent',
            'validation' => ''
        ],
        'description' =>  [
            'map' => 'post_content',
            'validation' => ''
        ],
        'slug' =>  [
            'map' => 'post_name',
            'validation' => ''
        ],
        'itens_per_page' =>  [
            'map' => 'meta',
            'validation' => ''
        ],
    ];
    
    function __construct() {
        add_action('init', array(&$this, 'register_post_type'));
    }
    
    function register_post_type() {
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
            'show_ui' => tnc_enable_dev_wp_interface(),
            'show_in_menu' => tnc_enable_dev_wp_interface(),
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
    
    function insert(TainacanCollection $collection) {
        // First iterate through the native post properties
        $map = $this->map;
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $collection->WP_Post->{$mapped['map']} = $collection->get_mapped_property($prop);
            }
        }
        
        // save post and geet its ID
        $collection->WP_Post->post_type = self::POST_TYPE;
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
                if (is_array($values))
                    foreach ($values as $value)
                        add_post_meta($id, $prop, $value);
            }
        }
        
        $collection->register_post_type();
        
        // return a brand new object
        return new TainacanCollection($collection->WP_Post);
    }
    
    function get_collections($args = array()) {
        
        $args = array_merge([
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ], $args);
        
        $posts = get_posts($args);
        
        $return = [];
        
        foreach ($posts as $post) {
            $return[] = new TainacanCollection($post);
        }
        
        // TODO: Pegar coleções registradas via código
        
        return $return;
    }
    
    function get_collection_by_id($id) {
        return new TainacanCollection($id);
    }

    
}

global $TainacanCollections;
$TainacanCollections = new TainacanCollections();