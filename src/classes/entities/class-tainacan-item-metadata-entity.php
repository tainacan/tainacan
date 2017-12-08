<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Item Metadata Entity
 */
class Item_Metadata_Entity extends Entity {
	protected static $post_type = false;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Tainacan_Item_Metadata';
	
    function __construct(Item $item, Metadata $metadata) {
        
        $this->set_item($item);
        $this->set_metadata($metadata);
    }

	public function  __toString(){
		return 'Hello, I\'m the Item Metadata Entity';
	}
    
    /**
     * Define the item
     *
     * @param Item $item
     * @return void
     */
    function set_item(Item $item) {
        $this->item = $item;
    }
    
    /**
     * Define the metadata value
     *
     * @param [integer | string] $value
     * @return void
     */
    function set_value($value) {
        $this->value = $value;
    }
    
    /**
     * Define the metadata
     *
     * @param Metadata $metadata
     * @return void
     */
    function set_metadata(Metadata $metadata) {
        $this->metadata = $metadata;
    }
    
    /**
     * Return the item
     *
     * @return Item
     */
    function get_item() {
        return $this->item;
    }
    
    /**
     * Return the metadata
     *
     * @return Metadata
     */
    function get_metadata() {
        return $this->metadata;
    }
    
    /**
     * Return the metadata value
     *
     * @return string | integer
     */
    function get_value() {
        if (isset($this->value))
            return $this->value;
        
        global $Tainacan_Item_Metadata;
        return $Tainacan_Item_Metadata->get_value($this);
    }
    
    /**
     * Return true if metadata is multiple, else return false
     *
     * @return boolean
     */
    function is_multiple() {
        return $this->get_metadata()->is_multiple();
    }
    
    /**
     * Return true if metadata is key
     *
     * @return boolean
     */
    function is_collection_key() {
        return $this->get_metadata()->is_collection_key();
    }
    
    /**
     * Return true if metadata is required
     *
     * @return boolean
     */
    function is_required() {
        return $this->get_metadata()->is_required();
    }
    
    /**
     * Validate attributes
     *
     * @return boolean
     */
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
                
                $this->set_as_valid();
                return true;   
            } else {
                $this->add_error('invalid', $metadata->get_name() . ' is invalid');
                return false;
            }
        } else {
            
            if ($this->is_collection_key()) {
            	$Tainacan_Items = new \Tainacan\Repositories\Items();
                
                $test = $Tainacan_Items->fetch([
                    'meta_query'    => [
                        [
                            'key' => $this->metadata->get_id(), 
                            'value' => $value
                        ],
                    ]
                ], $item->get_collection());

                if ($test->have_posts()) {
                    $this->add_error('key_exists', $metadata->get_name() . ' is a collection key and there is another item with the same value');
                    return false;
                }
            }
            
            // TODO: call fieldType validation
            // 
            $this->set_as_valid();
            return true;   
        }   
    }
}