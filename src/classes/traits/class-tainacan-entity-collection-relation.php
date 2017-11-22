<?php

namespace Tainacan\Traits;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// used by Item, Event, Field

trait Entity_Collection_Relation {

    public function get_collection_id() {
        return $this->get_mapped_property('collection_id');
    }
    
    public function get_collection() {
    	if (isset($this->collection) && $this->collection instanceof Entities\Collection)
            return $this->collection;
        
        if (is_numeric($this->get_collection_id())) {
            global $Tainacan_Collections;

            $this->collection = $Tainacan_Collections->fetch($this->get_collection_id());
            return $this->collection;
        }
        
        return null;
    }
    
    public function set_collection_id($value) {
        $this->collection = null;
        return $this->set_mapped_property('collection_id', $value);  
    }
    
    public function set_collection(Entities\Collection $collection) {
        $this->collection = $collection;
        $this->set_collection_id($collection->get_id());
    }

}