<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class TainacanItems {
    
    var $map = [
        'ID' => 'ID',
        'title' => 'post_title',
        'description' => 'post_content',
        'collection_id' => 'meta',
        //'collection' => 'relation...'
    ];
    
    function __construct() {
        add_action('init', array(&$this, 'register_post_types'));
    }
    
    function register_post_types() {
        
        global $TainacanCollections, $Tainacan_Taxonomies;
        
        $collections = $TainacanCollections->get_collections();
        $taxonomies = $Tainacan_Taxonomies->get_taxonomies();

        $labels = array(
            'name' => 'Item',
            'singular_name' => 'Item',
            'add_new' => 'Adicionar Novo',
            'add_new_item' =>'Adicionar Item',
            'edit_item' => 'Editar',
            'new_item' => 'Novo Item',
            'view_item' => 'Visualizar',
            'search_items' => 'Pesquisar',
            'not_found' => 'Nenhum Item encontrado',
            'not_found_in_trash' => 'Nenhum Item encontrado na lixeira',
            'parent_item_colon' => 'Item acima:',
            'menu_name' => 'Item'
        );
        
        if (!is_array($collections))
            return;
            
        foreach ($collections as $collection) {
            
            $labels['menu_name'] = $collection->get_name();
            $cpt_slug = $TainacanCollections->get_collection_db_identifier($collection->get_id());
            
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
            register_post_type($cpt_slug, $args);
            $this->register_post_type_taxonomies( $cpt_slug, $taxonomies );
        }
    }


    function register_post_type_taxonomies( $cpt_slug, $taxonomies ){
        foreach ($taxonomies as $taxonomy) {
            register_taxonomy_for_object_type( $taxonomy, $cpt_slug );
        }
    }
    
    
    function insert(TainacanItem $item) {
        
        
        $map = $this->map;
        
        // get collection to determine post type
        $collection = $item->get_collection();
        
        if (!$collection)
            return false;
        
        $cpt = $collection->get_db_identifier();
        
        // iterate through the native post properties
        foreach ($map as $prop => $mapped) {
            if ($mapped != 'meta') {
                $item->WP_Post->$mapped = $item->get_mapped_property($prop);
            }
        }
        
        // save post and geet its ID
        $item->WP_Post->post_type = $cpt;
        $id = wp_insert_post($item->WP_Post);
        
        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped == 'meta') {
                update_post_meta($id, $prop, $item->get_mapped_property($prop));
            }
        }
        
        // TODO - save item medatada
        // get collection Metadata
        // foreach metadata...
        
        
        return $id;
    }
    
   
    function get_item_by_id($id) {
        return new TainacanItem($id);
    }


    function get_metadata( TainacanItem $item ){
        global $TainacanCollections;
        $values = [];

        $collection_metadata = $TainacanCollections->get_metadata( $item->get_collection() );
        foreach ($collection_metadata as $metadata) {
            $values[] = [
                'metadata_id' =>  $metadata->get_id(),
                'value' => get_post_meta( $item->get_id(), $metadata->get_id()),
            ];
        }

        return $values;
    }


    function set_metadata( TainacanItem $item, $values){
        global $TainacanCollections;

        $collection_metadata = $TainacanCollections->get_metadata( $item->get_collection() );
        foreach ($collection_metadata as $metadata) {

        }
    }
    
}

global $TainacanItems;
$TainacanItems = new TainacanItems();