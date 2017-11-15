<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tainacan_Item extends Tainacan_Entity {
    
    use Tainacan_Entity_Collection_Relation;
    
    function __construct($which = 0) {
        
        $this->repository = 'Tainacan_Items';
        
        if (is_numeric($which) && $which > 0) {
            $post = get_post($which);
            if ($post instanceof WP_Post) {
                $this->WP_Post = get_post($which);
            }
            
        } elseif ($which instanceof WP_Post) {
            $this->WP_Post = $which;
        } else {
            $this->WP_Post = new StdClass();
        }
        
    }

    // Getters
    function get_id() {
        return $this->get_mapped_property('id');
    }
    function get_title() {
        return $this->get_mapped_property('title');
    }
    function get_order() {
        return $this->get_mapped_property('order');
    }
    function get_parent() {
        return $this->get_mapped_property('parent');
    }
    function get_description() {
        return $this->get_mapped_property('description');
    }
    
    // Setters
    function set_title($value) {
        return $this->set_mapped_property('title', $value);
    }
    function set_order($value) {
        return $this->set_mapped_property('order', $value);
    }
    function set_parent($value) {
        return $this->set_mapped_property('parent', $value);
    }
    function set_description($value) {
        return $this->set_mapped_property('description', $value);
    }

    // Metadata
    function get_metadata() {
        if (isset($this->metadata))
            return $this->metadata;
        
        $collection = $this->get_collection();
        $return = [];
        if ($collection) {
            $metaList = $collection->get_metadata();
            
            foreach ($metaList as $meta) {
                $return[$meta->get_id()] = new Tainacan_Item_Metadata_Entity($this, $meta);
            }
        }
        return $return;
    }
    
    function add_metadata(Tainacan_Metadata $new_metadata, $value) {
        
        //TODO Multiple metadata must receive an array as value
        $item_metadata = new Tainacan_Item_Metadata_Entity($this, $new_metadata);
        $item_metadata->set_value($value);
        $current_meta = $this->get_metadata();
        $current_meta[$new_metadata->get_id()] = $item_metadata;
        
        $this->set_metadata($current_meta);
    }
    
    function set_metadata(Array $metadata) {
        $this->metadata = $metadata;
    }
    
}