<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Tainacan_Metadatas
 */
class Tainacan_Metadatas {

    const POST_TYPE = 'tainacan-metadata';

    var $map = [
        'ID' => 'ID',
        'name' => 'post_title',
        'order' => 'menu_order',
        'parent' => 'parent',
        'description' => 'post_content',
        'type' => 'meta',
        'required' => 'meta',
        'cardinality' => 'meta',
        'privacy' => 'meta',
        'mask' => 'meta',
        'default_value' => 'meta',
        'option' => 'meta',
        'collection_id' => 'meta',
    ];

    function __construct() {
        add_action('init', array(&$this, 'register_post_type'));
    }

    function register_post_type() {
        $labels = array(
            'name' => 'Metadata',
            'singular_name' => 'Metadata',
            'add_new' => 'Adicionar Metadata',
            'add_new_item' =>'Adicionar Metadata',
            'edit_item' => 'Editar',
            'new_item' => 'Novo Metadata',
            'view_item' => 'Visualizar',
            'search_items' => 'Pesquisar',
            'not_found' => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Collections encontrado na lixeira',
            'parent_item_colon' => 'Metadata acima:',
            'menu_name' => 'Metadata'
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

    /**
     * @param Tainacan_Metadata $metadata
     * @return int
     */
    function insert( Tainacan_Metadata $metadata ) {
        // First iterate through the native post properties
        $map = $this->map;
        foreach ($map as $prop => $mapped) {
            if ($mapped != 'meta') {
                $metadata->WP_Post->$mapped = $metadata->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $metadata->WP_Post->post_type = self::POST_TYPE;
        $metadata->WP_Post->post_status = 'publish';
        $id = wp_insert_post($metadata->WP_Post);
        $metadata->WP_Post = get_post($id);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped == 'meta') {
                update_post_meta($id, $prop, $metadata->get_mapped_property($prop));
            } elseif ($mapped == 'meta_multi') {
                $values = $metadata->get_mapped_property($prop);
                delete_post_meta($id, $prop);
                if (is_array($values))
                    foreach ($values as $value)
                        add_post_meta($id, $prop, $value);
            }
        }

        return $id;
    }

    /**
     * @param ( TainacanCollection ) $collection_id
     * @param array $args
     * @return array
     */
    function get_metadata_by_collection( $collection, $args = array()) {
        $collection_id = ( is_object( $collection ) )  ? $collection->get_id() : $collection;

        $args = array_merge([
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => 'collection',
            'meta_value' => $collection_id
        ], $args);

        $posts = get_posts($args);

        $return = [];

        foreach ($posts as $post) {
            $return[] = new Tainacan_Metadata($post);
        }

        return $return;
    }

    
    /**
     * @param int $id
     * @return Tainacan_Metadata
     */
    function get_metadata_by_id($id) {
        return new Tainacan_Metadata($id);
    }
}

global $Tainacan_Metadatas;
$Tainacan_Metadatas = new Tainacan_Metadatas();