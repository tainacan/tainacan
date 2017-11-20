<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Items implements Repository {
    
    function __construct() {
        add_action('init', array(&$this, 'register_post_types'));
    }
    
    function get_map() {
        return [
            'id'            => [
                'map'        => 'ID',
                //'validation' => ''
            ],
            'title'         =>  [
                'map'        => 'post_title',
                'validation' => ''
            ],
            'description'   =>  [
                'map'        => 'post_content',
                'validation' => ''
            ],
            'collection_id' =>  [
                'map'        => 'meta',
                'validation' => ''
            ],
            //'collection' => 'relation...',
            // metadata .. metadata...
        ];
    }
    
    function register_post_types() {
        
        global $Tainacan_Collections, $Tainacan_Taxonomies;
        
        $collections = $Tainacan_Collections->fetch();
        $taxonomies = $Tainacan_Taxonomies->get_taxonomies();

        if (!is_array($collections)){
            return;
        }

        // register collections post type and associate taxonomies
        foreach ($collections as $collection) {
            $collection->register_post_type();
        }
         
        // register taxonomies
        foreach ($taxonomies as $taxonomy) {
            $taxonomy->register_taxonomy();
        }  
    }
 
    function insert($item) {
        $map = $this->get_map();
        
        // get collection to determine post type
        $collection = $item->get_collection();
        
        if (!$collection){
            return false;
        }
        
        $cpt = $collection->get_db_identifier();
        
        // iterate through the native post properties
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $item->WP_Post->{$mapped['map']} = $item->get_mapped_property($prop);
            }
        }
        
        // save post and geet its ID
        $item->WP_Post->post_type = $cpt;
        $item->WP_Post->post_status = 'publish';
        
        $id = wp_insert_post($item->WP_Post);
        $item->WP_Post = get_post($id);
        
        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $item->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $item->get_mapped_property($prop);
                
                delete_post_meta($id, $prop);
                
                if (is_array($values)){
                    foreach ($values as $value){
                        add_post_meta($id, $prop, $value);
                    }
                }
            }
        }
        
        // save metadata
        $metadata = $item->get_metadata();
        global $Tainacan_Item_Metadata;
        
        foreach ($metadata as $meta) {
            $Tainacan_Item_Metadata->insert($meta);
        }
        
        // return a brand new object
        return new Entities\Item($item->WP_Post);
    }

    public function fetch($args = [], $object = []){

        global $Tainacan_Collections;
        
        if(is_numeric($args)){
            return new Entities\Item($args);            
        }

        if (empty($object)) {
            $object = $Tainacan_Collections->fetch();
        }
        
        if (is_numeric($object)){
            $object = $Tainacan_Collections->fetch($collection);
        }
        
        if ($object instanceof Entities\Collection) {
            $cpt = $object->get_db_identifier();
        } elseif (is_array($object)) {
            $cpt = [];
            
            foreach ($object as $collection) {
                if (is_numeric($collection)){
                    $collection = $Tainacan_Collections->fetch($collection);
                }
                if ($collection instanceof Entities\Collection){
                    $cpt[] = $collection->get_db_identifier();
                }
            }
            
        } else {
            return [];
        }
        
        if (empty($cpt)){
            return [];
        }

        $args = array_merge([
            'post_type'      => $cpt,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ], $args);

        $posts = get_posts($args);
        
        $return = [];
        
        foreach ($posts as $post) {
        	$return[] = new Entities\Item($post);
        }
        
        return $return;
    }

    public function update($object){

    }

    public function delete($object){

    }
    
    // same as WP_Query with few paramaters more:
    // collections ID or array of IDs, object or array of objects
    // metadata - array of metadata in meta_query format
    // other item properties, present in the "map"
    function query($args) {
        
        $map = $this->get_map();

        $wp_query_exceptions = [
            'ID'         => 'p',
            'post_title' => 'title'
        ];
        
        $meta_query = [];
        
        foreach ($map as $prop => $mapped) {
            if (array_key_exists($prop, $args)) {
                $prop_value = $args[$prop];
                
                unset($args[$prop]);
                
                if ( $mapped['map'] == 'meta' || $mapped['map'] == 'meta_multi' ) {
                    $meta_query[] = [
                        'key'   => $prop,
                        'value' => $prop_value
                    ];
                } else {
                    $prop_search_name = array_key_exists($mapped['map'], $wp_query_exceptions) ? $wp_query_exceptions[$mapped['map']] : $mapped['map'];
                    $args[$prop_search_name] = $prop_value;
                }
                
            }
        }
        
        if ( isset($args['metadata']) && is_array($args['metadata']) && !empty($args['metadata'])) {
            $meta_query = array_merge($args['metadata'],$meta_query);
        }
        
        $args['meta_query'] = $meta_query;
        
        unset($args['metadata']);
        
        $collections = !empty($args['collections']) ? $args['collections'] : [];
        unset($args['collections']);
        
        return $this->fetch($args, $collections);
        ### TODO I think its better if we return a WP_Query object. easier for loop and debugging
    }
}