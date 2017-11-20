<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Representa a entidade Item Metadata
 */
class Item_Metadata_Entity extends \Tainacan\Entity {
    
    function __construct(Item $item, Metadata $metadata) {
        
        $this->repository = 'Tainacan_Item_Metadata';
        
        $this->set_item($item);
        $this->set_metadata($metadata);
    }
    
    /**
     * Atribui o item
     *
     * @param Item $item
     * @return void
     */
    function set_item(Item $item) {
        $this->item = $item;
    }
    
    /**
     * Define o valor do metadado
     *
     * @param [integer || string] $value
     * @return void
     */
    function set_value($value) {
        $this->value = $value;
    }
    
    /**
     * Atribui o Metadado
     *
     * @param Metadata $metadata
     * @return void
     */
    function set_metadata(Metadata $metadata) {
        $this->metadata = $metadata;
    }
    
    /**
     * Retorna o Item
     *
     * @return Item
     */
    function get_item() {
        return $this->item;
    }
    
    /**
     * Retorna o Metadado
     *
     * @return Metadata
     */
    function get_metadata() {
        return $this->metadata;
    }
    
    /**
     * Retorna o valor do metadado
     *
     * @return string || integer
     */
    function get_value() {
        if (isset($this->value))
            return $this->value;
        
        global $Tainacan_Item_Metadata;
        return $Tainacan_Item_Metadata->fetch($this);
    }
    
    /**
     * Retorna se metado é multiplo ou não
     *
     * @return boolean
     */
    function is_multiple() {
        return $this->get_metadata()->is_multiple();
    }
    
    /**
     * Retorna se metadado é chave (seu valor não deve se repetir)
     *
     * @return boolean
     */
    function is_collection_key() {
        return $this->get_metadata()->is_collection_key();
    }
    
    /**
     * Retorna se metadado é obrigatório
     *
     * @return boolean
     */
    function is_required() {
        return $this->get_metadata()->is_required();
    }
    
    /**
     * Valida atributos
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
                        [
                            'key' => $this->metadata->get_id(), 
                            'value' => $value
                        ],
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