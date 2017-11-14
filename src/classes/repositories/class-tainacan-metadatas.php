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
        'ID'             => [
            'map'        => 'ID',
            'validation' => ''
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

    function __construct() {
        add_action('init', array(&$this, 'register_post_type'));
    }

    function register_post_type() {
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
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $metadata->WP_Post->{$mapped['map']} = $metadata->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $metadata->WP_Post->post_type = self::POST_TYPE;
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
        return new Tainacan_Metadata($metadata->WP_Post);
    }

    /**
     * @param ( Tainacan_Collection ) $collection_id
     * @param array $args
     * @return array
     */
    function get_metadata_by_collection( $collection, $args = array()) {
        
        // TODO: get metadata from parent collections

        $collection_id = ( is_object( $collection ) ) ? $collection->get_id() : $collection;

        $args = array_merge([
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'collection_id',
            'meta_value'     => $collection_id
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