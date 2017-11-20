<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Item_Metadata implements Repository {
    
    function insert($item_metadata) {

        $unique = ! $item_metadata->is_multiple();
        
        if ($unique) {
            update_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), $item_metadata->get_value());
        } else {
            delete_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id());
            
            if (is_array($item_metadata->get_value())){
                foreach ($item_metadata->get_value() as $value){
                    add_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), $value);
                }
            }
        }
        
        // return a brand new object
        return new Entities\Item_Metadata_Entity($item_metadata->get_item(), $item_metadata->get_metadata());
        
    }

    public function update($object){

    }

    public function delete($object){

    }

    public function fetch($object){
        if($object instanceof Entities\Item){
            global $Tainacan_Items, $Tainacan_Metadatas;
            
            $collection = $object->get_collection();
            
            if (!$collection instanceof Entities\Collection){
                return [];
            }
            
            $meta_list = $Tainacan_Metadatas->get_metadata_by_collection($collection);
            
            $return = [];
            
            if (is_array($meta_list)) {
                foreach ($meta_list as $meta) {
                    $return[] = new Entities\Item_Metadata_Entity($object, $meta);
                }
            }
            
            return $return;
        } elseif($object instanceof Entities\Item_Metadata_Entity){
            // Retorna o valor do metadado

            $unique = ! $object->is_multiple();
            
            return get_post_meta($object->item->get_id(), $object->metadata->get_id(), $unique);  
        }
    }
    
}