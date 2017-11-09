<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}





class TainacanItem extends Entity {
    
    
    function __construct($which = 0) {
        
        $this->repository = 'TainacanItems';
        
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
    //
    function get_id() {
        return $this->get_mapped_property('ID');
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
    function get_collection_id() {
        return $this->get_mapped_property('collection_id');
    }
    
    // sepecial Getters
    function get_collection() {
        if (isset($this->collection) && $this->collection instanceof TainacanCollection)
            return $this->collection;
        
        if (is_numeric($this->get_collection_id())) {
            $collection = get_post($this->get_collection_id());
            if ($collection instanceof WP_Post) {
                $this->collection = new TainacanCollection($collection);
                return $this->collection;
            }
        }
        
        return null;
        
    }
    
    // Setters
    // 
    
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
    function set_collection_id($value) {
        return $this->set_mapped_property('collection_id', $value);
    }
    
    // sepecial Setters
    
    function set_collection(TainacanCollection $collection) {
        $this->collection = $collection;
        $this->set_collection_id($collection->get_id());
    }

    
    
}