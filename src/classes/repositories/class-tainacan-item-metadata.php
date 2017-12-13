<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Item_Metadata extends Repository {
	public $entities_type = '\Tainacan\Entities\Item_Metadata_Entity';
    public function insert($item_metadata) {

        $unique = ! $item_metadata->is_multiple();
        
        if ($unique) {
            update_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), wp_slash( $item_metadata->get_value() ) );
        } else {
            delete_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id());
            
            if (is_array($item_metadata->get_value())){
            	$values = $item_metadata->get_value();

                foreach ($values as $value){
                    add_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), wp_slash( $value ));
                }
            }
        }
        
        do_action('tainacan-insert', $item_metadata);
        do_action('tainacan-insert-Item_Metadata_Entity', $item_metadata);
        // return a brand new object
        return new Entities\Item_Metadata_Entity($item_metadata->get_item(), $item_metadata->get_metadata());
        
    }

    public function update($object){

    }

	/**
	 * Delete Item Metadata
	 *
	 * @param $item_metadata
	 *
	 * @return mixed|void
	 */
	public function delete($item_metadata){
//    	if(is_array($item_metadata->get_value())){
//    		$values = $item_metadata->get_value();
//
//    		foreach ($values as $value){
//    			delete_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), wp_slash($value));
//		    }
//	    } else {
//    		delete_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), wp_slash($item_metadata->get_value()));
//	    }
    }

    /**
     * Fetch Item Metadata objects related to an Item
     *
     * @param Entities\Item $object
     * @return array
     */
    public function fetch($object, $output = null ){
        if($object instanceof Entities\Item){
            global $Tainacan_Items, $Tainacan_Metadatas;
            
            $collection = $object->get_collection();
            
            if (!$collection instanceof Entities\Collection){
                return [];
            }
            
            $meta_list = $Tainacan_Metadatas->fetch_by_collection($collection, [], 'OBJECT' );
            
            $return = [];
            
            if (is_array($meta_list)) {
                foreach ($meta_list as $meta) {
                    $return[] = new Entities\Item_Metadata_Entity($object, $meta);
                }
            }
            
            return $return;
        }else{
            return [];
        }
    }

    /**
     * Get the value for a Item metadata.
     *
     * @param Entities\Item_Metadata_Entity $item_metadata
     * @return mixed
     */
    public function get_value(Entities\Item_Metadata_Entity $item_metadata) {
        $unique = ! $item_metadata->is_multiple();

        return get_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), $unique);
    }

    public function register_post_type() { }
    
    public function get_map() { return []; }
    public function get_default_properties($map) { return []; }
    
}