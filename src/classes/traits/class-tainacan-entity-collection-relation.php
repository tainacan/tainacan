<?php

namespace Tainacan\Traits;

// used by Item, Event, Field

trait Entity_Collection_Relation {

    function get_collection_id() {
        return $this->get_mapped_property('collection_id');
    }
    
    function get_collection() {
    	if (isset($this->collection) && $this->collection instanceof \Tainacan\Entities\Collection)
            return $this->collection;
        
        if (is_numeric($this->get_collection_id())) {
            global $Tainacan_Collections;

            $this->collection = $Tainacan_Collections->get_collection_by_id($this->get_collection_id());
            return $this->collection;
        }
        
        return null;
    }
    
    function set_collection_id($value) {
        $this->collection = null;
        return $this->set_mapped_property('collection_id', $value);  
    }
    
    function set_collection(\Tainacan\Entities\Collection $collection) {
        $this->collection = $collection;
        $this->set_collection_id($collection->get_id());
    }

}