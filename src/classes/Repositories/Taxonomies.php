<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Tainacan_Metadatas
 */
class Tainacan_Taxonomies {

    const POST_TYPE = 'tainacan-taxonomies';
    const DB_IDENTIFIER_META = '_db_identifier';

    var $map = [
        'ID' => 'ID',
        'name' => 'post_title',
        'parent' => 'parent',
        'description' => 'post_content',
        'allow_insert' => 'meta',
        'collection' => 'meta',
    ];


    function __construct() {
        add_action('init', array(&$this, 'register_post_type'));
    }

    function register_post_type() {
        $labels = array(
            'name' => 'Taxonomy',
            'singular_name' => 'Taxonomy',
            'add_new' => 'Adicionar Taxonomy',
            'add_new_item' =>'Adicionar Taxonomy',
            'edit_item' => 'Editar',
            'new_item' => 'Novo Taxonomy',
            'view_item' => 'Visualizar',
            'search_items' => 'Pesquisar',
            'not_found' => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Taxonomy encontrado na lixeira',
            'parent_item_colon' => 'Taxonomy acima:',
            'menu_name' => 'Taxonomy'
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

    function get_taxonomy_db_identifier($id) {
        $meta = get_post_meta($id, self::DB_IDENTIFIER_META, true);

        if (!$meta) {
            $p = get_post($id);
            add_post_meta($id, self::DB_IDENTIFIER_META, $p->post_name);
            return $p->post_name;
        }

        return $meta;
    }

    /**
     * @param Tainacan_Taxonomy $metadata
     * @return int
     */
    function insert( Tainacan_Taxonomy $taxonomy ) {
        // First iterate through the native post properties
        $map = $this->map;
        foreach ($map as $prop => $mapped) {
            if ($mapped != 'meta') {
                $taxonomy->WP_Post->$mapped = $taxonomy->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $taxonomy->WP_Post->post_type = self::POST_TYPE;
        $taxonomy->WP_Post->post_status = 'publish';
        $id = wp_insert_post($taxonomy->WP_Post);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped == 'meta') {
                update_post_meta($id, $prop, $taxonomy->get_mapped_property($prop));
            }
        }

        // DB Identifier
        // the first time a collection is saved, save the slug as a db Identifier,
        // This will be the slug of the post type that will be created for this collection
        // Later, if the slug is changed (and thus the URL of this collection) the db Identifier
        // does not change and we dont lose all the items
        if (!get_post_meta($id, self::DB_IDENTIFIER_META, true)) {
            $p = get_post($id);
            add_post_meta($id, self::DB_IDENTIFIER_META, $p->post_name);
            registerTainacanTaxonomy( $p->post_name );
        }

        return $id;
    }

    function registerTainacanTaxonomy( $taxonomy_name ){
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
}