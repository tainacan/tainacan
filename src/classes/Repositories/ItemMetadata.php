<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class Tainacan_Item_Metadata {
    
    function get_item_metadata_by_item(TainacanItem $item) {
        global $TainacanItems, $Tainacan_Metadatas;
        
        $collection = $item->get_collection();
        
        if (!$collection instanceof TainacanCollection)
            return [];
        
        $meta_list = $Tainacan_Metadatas->get_metadata_by_collection($collection);
        
        $return = [];
        
        if (is_array($meta_list)) {
            foreach ($meta_list as $meta) {
                $return = new Tainacan_Item_Metadata_Entity($item, $meta);
            }
        }
        
        return $return;
        
    }
    
    function insert(Tainacan_Item_Metadata_Entity $item_metadata) {

        $unique = ! $item_metadata->is_multiple();
        
        if ($unique) {
            update_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), $item_metadata->get_value());
        } else {
            delete_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id());
            if (is_array($item_metadata->get_value())) 
                foreach ($item_metadata->get_value() as $value)
                    add_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), $value);
        }
        
        // return a brand new object
        return new Tainacan_Item_Metadata_Entity($item_metadata->get_item(), $item_metadata->get_metadata());
        
    }
    
    function get_item_metadata_value(Tainacan_Item_Metadata_Entity $item_metadata) {
        
        $unique = ! $item_metadata->is_multiple();
            
        return get_post_meta($item_metadata->item->get_id(), $item_metadata->metadata->get_id(), $unique);
        
    }
    
}

global $Tainacan_Item_Metadata;
$Tainacan_Item_Metadata = new Tainacan_Item_Metadata();