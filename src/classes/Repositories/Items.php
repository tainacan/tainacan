<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class TainacanItems {
    
    var $map = [
        'ID' => [
            'map' => 'ID',
            'validation' => ''
        ],
        'title' =>  [
            'map' => 'post_title',
            'validation' => ''
        ],
        'description' =>  [
            'map' => 'post_content',
            'validation' => ''
        ],
        'collection_id' =>  [
            'map' => 'meta',
            'validation' => ''
        ],
        //'collection' => 'relation...',
        // metadata .. metadata...
    ];
    
    function __construct() {
        add_action('init', array(&$this, 'register_post_types'));
    }
    
    function register_post_types() {
        
        global $TainacanCollections, $Tainacan_Taxonomies;
        
        $collections = $TainacanCollections->get_collections();
        $taxonomies = $Tainacan_Taxonomies->get_taxonomies();

        if (!is_array($collections))
            return;
            
        
        
        
        
        // register collections post type and associate taxonomies
        foreach ($collections as $collection) {
            
            $collection->register_post_type();
            
        }
        
        
        // register taxonomies
        foreach ($taxonomies as $taxonomy) {
            $taxonomy->register_taxonomy();
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
            if ($mapped['map'] != 'meta') {
                $item->WP_Post->{$mapped['map']} = $item->get_mapped_property($prop);
            }
        }
        
        // save post and geet its ID
        $item->WP_Post->post_type = $cpt;
        $id = wp_insert_post($item->WP_Post);
        $item->WP_Post = get_post($id);
        
        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $item->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $item->get_mapped_property($prop);
                delete_post_meta($id, $prop);
                if (is_array($values))
                    foreach ($values as $value)
                        add_post_meta($id, $prop, $value);
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