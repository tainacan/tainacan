<?php

// used by Item, Event, Field

trait EntityCollectionRelation {

    function get_collection_id() {
        return $this->get_mapped_property('collection_id');
    }
    

    function get_collection() {
        if (isset($this->collection) && $this->collection instanceof TainacanCollection)
            return $this->collection;
        
        if (is_numeric($this->get_collection_id())) {
            global $TainacanCollections;
            $this->collection = $TainacanCollections->get_collection_by_id($this->get_collection_id());
            return $this->collection;
        }
        
        return null;
        
    }
    
    function set_collection_id($value) {
        return $this->set_mapped_property('collection_id', $value);
        $this->collection = null;
    }
    
    function set_collection(TainacanCollection $collection) {
        $this->collection = $collection;
        $this->set_collection_id($collection->get_id());
    }

}