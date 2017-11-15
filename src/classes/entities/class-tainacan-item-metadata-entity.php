<?php

namespace Tainacan\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Item_Metadata_Entity extends \Tainacan\Entity {
    
    function __construct(Item $item, Metadata $metadata) {
        
        $this->repository = 'Tainacan_Item_Metadata';
        
        $this->set_item($item);
        $this->set_metadata($metadata);
    }
    
    function set_item(Item $item) {
        $this->item = $item;
    }
    
    function set_value($value) {
        $this->value = $value;
    }
    
    function set_metadata(Metadata $metadata) {
        $this->metadata = $metadata;
    }
    
    function get_item() {
        return $this->item;
    }
    
    function get_metadata() {
        return $this->metadata;
    }
    
    function get_value() {
        if (isset($this->value))
            return $this->value;
        
        global $Tainacan_Item_Metadata;
        return $Tainacan_Item_Metadata->get_item_metadata_value($this);
    }
        
    function is_multiple() {
        return $this->get_metadata()->is_multiple();
    }
    
    function is_collection_key() {
        return $this->get_metadata()->is_collection_key();
    }
    
    function is_required() {
        return $this->get_metadata()->is_required();
    }
    
    function validate() {   
        $value = $this->get_value();
        $metadata = $this->get_metadata();
        $item = $this->get_item();
        
        if (empty($value) && $this->is_required()) {
            $this->add_error('required', $metadata->get_name() . ' is required');
            return false;
        }
        
        if ($this->is_multiple()) {
            
            if (is_array($value)) {
                
                // if its required, at least one must be filled
                $one_filled = false;
                $valid = true;
                foreach($value as $val) {
                    if (!empty($val))
                        $one_filled = true;
                    
                    // TODO: call fieldtype validation
                    // if (invalid) $valid = false;
                    
                }
                
                if ($this->is_required() && !$one_filled) {
                    $this->add_error('required', $metadata->get_name() . ' is required');
                    return false;
                }
                
                if (!$valid) {
                    $this->add_error('invalid', $metadata->get_name() . ' is invalid');
                    return false;
                }
                
                $this->reset_errors();
                return true;
                
                
            } else {
                $this->add_error('invalid', $metadata->get_name() . ' is invalid');
                return false;
            }
            
            
        } else {
            
            if ($this->is_collection_key()) {
            	$Tainacan_Items = new \Tainacan\Repositories\Items();
                
                $test = $Tainacan_Items->query([
                    'collections' => $item->get_collection(),
                    'metadata'    => [
                        ['key' => $this->metadata->get_id(), 'value' => $value],
                    ]
                ]);

                if (!empty($test)) {
                    $this->add_error('key_exists', $metadata->get_name() . ' is a collection key and there is another item with the same value');
                    return false;
                }
            }
            
            // TODO: call fieldType validation
            // 
            $this->reset_errors();
            return true;   
        }   
    }
}