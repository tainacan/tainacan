<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;
use \Respect\Validation\Validator as v;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Items extends Repository {
	public $entities_type = '\Tainacan\Entities\Item';
    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(), [
            'title'         =>  [
                'map'        => 'post_title',
                'title'       => __('Title', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Title of the item', 'tainacan'),
                'on_error'   => __('The title should be a text value and not empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty()
            ],
            'description'   =>  [
                'map'        => 'post_content',
                'title'      => __('Description', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The item description', 'tainacan'),
                'validation' => ''
            ],
            'collection_id' =>  [
                'map'        => 'meta',
                'title'      => __('Collection', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('The collection ID', 'tainacan'),
                'validation' => ''
            ],
            //'collection' => 'relation...',
            // metadata .. metadata...
        ]);
    }
    
    public function register_post_type() {
        
        global $Tainacan_Collections, $Tainacan_Taxonomies;
        
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        $taxonomies = $Tainacan_Taxonomies->fetch([], 'OBJECT');

        if (!is_array($collections)){
            return;
        }

        // register collections post type and associate taxonomies
        foreach ($collections as $collection) {
            $collection->register_collection_item_post_type();
        }
         
        // register taxonomies
        if (is_array($taxonomies) && sizeof($taxonomies) > 0) {
            foreach ($taxonomies as $taxonomy) {
                $taxonomy->register_taxonomy();
            }  
        }
    }
 
    public function insert($item) {

        global $Tainacan_Metadatas;

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
    			update_post_meta($id, $prop,  wp_slash( $item->get_mapped_property($prop) ));
    		} elseif ($mapped['map'] == 'meta_multi') {
    			$values = $item->get_mapped_property($prop);
    			
    			delete_post_meta($id, $prop);
    			
    			if (is_array($values)){
    				foreach ($values as $value){
    					add_post_meta($id, $prop, wp_slash( $value ));
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
    	
    	do_action('tainacan-insert', $item);
    	do_action('tainacan-insert-Item', $item);
    	
    	// return a brand new object
    	return new Entities\Item($item->WP_Post);
    }

    /**
     * fetch items based on ID or WP_Query args
     *
     * Items are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
     * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
     * appropriate WP_Query argument
     *
     * The second paramater specifies from which collections item should be fetched.
     * You can pass the Collection ID or object, or an Array of IDs or collection objects
     *
     * @param array $args WP_Query args || int $args the item id
     * @param array $collections Array Entities\Collection || Array int collections IDs || int collection id || Entities\Collection collection object
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return \WP_Query|Array an instance of wp query OR array of entities;
     */
    public function fetch($args = [],$collections = [], $output = null){

        global $Tainacan_Collections;

        if(is_numeric($args)){
            return new Entities\Item($args);
        }

        if (empty($collections)){
            $wp_query = $Tainacan_Collections->fetch();
            if( $wp_query->have_posts() ){
                while ( $wp_query->have_posts() ){
                    $wp_query->the_post();
                    $collections[] = new Entities\Collection(  get_the_ID() );
                }
            }
        }

        if (is_numeric($collections)){
            $collections = $Tainacan_Collections->fetch($collections);
        }

        if ($collections instanceof Entities\Collection) {
            $cpt = $collections->get_db_identifier();
        } elseif (is_array($collections)) {
            $cpt = [];

            foreach ($collections as $collection) {
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

        //TODO: get collection order and order by options
        
        $args = $this->parse_fetch_args($args);
        
        $args = array_merge([
            'post_status'    => 'publish',
        ], $args);

        $args['post_type'] = $cpt;

        $wp_query = new \WP_Query($args);
        return $this->fetch_output($wp_query, $output);
    }

    public function update($object){

    }

	/**
	 * @param $args ( is a array like [post_id, [is_permanently => bool]] )
	 *
	 * @return mixed|Entities\Item
	 */
	public function delete($args){
    	if(!empty($args[1]) && $args[1]['is_permanently'] === true){
    		return new Entities\Item(wp_delete_post($args[0], $args[1]['is_permanently']));
	    }

	    return new Entities\Item(wp_trash_post($args[0]));
    }
    
}