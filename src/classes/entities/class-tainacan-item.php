<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Representa a entidade Item
 */
class Item extends Entity {
	use \Tainacan\Traits\Entity_Collection_Relation;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Tainacan_Items';
	
	public function  __toString(){
		return 'Hello, I\'m the Item Entity';
	}

    /**
     * Retorna o ID do item
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }

    /**
     * Retorna o titulo do item
     *
     * @return string
     */
    function get_title() {
        return $this->get_mapped_property('title');
    }

    /**
     * Retorna o tipo de ordenação do item
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Retorna o ID do parent do item
     *
     * @return integer
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Retorna a descrição do item
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }
    
    /**
     * Atribui o título
     *
     * @param [string] $value
     * @return void
     */
    function set_title($value) {
        $this->set_mapped_property('title', $value);
    }

    /**
     * Atribui o tipo de ordenação
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Atribui o ID do parent
     *
     * @param [integer] $value
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Atribui a descrição
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Retorna um metadado ou lista de metadados
     *
     * @return Array || Metadata
     */
    function get_metadata() {
        global $Tainacan_Metadatas;

        if (isset($this->metadata))
            return $this->metadata;
        
        $collection = $this->get_collection();
        $all_metadata = [];
        if ($collection) {
            $meta_list = $Tainacan_Metadatas->fetch_by_collection( $collection, [], 'OBJECT' );
            
            foreach ($meta_list as $meta) {
                $all_metadata[$meta->get_id()] = new Item_Metadata_Entity($this, $meta);
            }
        }
        return $all_metadata;
    }
    
    /**
     * Atribui Metadado
     *
     * @param Metadata $new_metadata
     * @param [string || integer || array] $value
     * @return void
     */
    function add_metadata(Metadata $new_metadata, $value) {
        
        //TODO Multiple metadata must receive an array as value
        $item_metadata = new Item_Metadata_Entity($this, $new_metadata);
       
        $item_metadata->set_value($value);
        
        $current_meta = $this->get_metadata();
        $current_meta[$new_metadata->get_id()] = $item_metadata;
        
        $this->set_metadata($current_meta);
    }
    
    /**
     * Função auxiliar à @method add_metadata
     *
     * @param Array $metadata
     * @return void
     */
    function set_metadata(Array $metadata) {
        $this->metadata = $metadata;
    }
}