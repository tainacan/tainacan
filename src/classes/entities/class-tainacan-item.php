<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Item
 */
class Item extends Entity {
	use \Tainacan\Traits\Entity_Collection_Relation;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Tainacan_Items';
	
	/**
	 * {@inheritDoc}
	 */
	function __construct($which = 0) {
		parent::__construct($which);
		if($which !== 0 ) $this->set_cap();
	}
	
	public function  __toString(){
		return 'Hello, my name is '. $this->get_title();
	}

	/**
	 * @param $value
	 */
	function set_terms($value){
		$this->set_mapped_property('terms', $value);
	}

	/**
	 * @return mixed|null
	 */
	function get_terms(){
		return $this->get_mapped_property('terms');
	}

	/**
	 * @return mixed|null
	 */
	function get_featured_img(){
		return $this->get_mapped_property('featured_image');
	}

	/**
	 * @param $id
	 */
	function set_featured_img_id($id){
		$this->set_mapped_property('featured_img_id', $id );
	}

	/**
	 * @return mixed|null
	 */
	function get_attachments(){
		return $this->get_mapped_property('attachments');
	}

	/**
	 * @return mixed|null
	 */
	function get_modification_date(){
		return $this->get_mapped_property('modification_date');
	}

	/**
	 * @return mixed|null
	 */
	function get_creation_date(){
		return $this->get_mapped_property('creation_date');
	}

	/**
	 * @return mixed|null
	 */
	function get_author_id(){
		return $this->get_mapped_property('author_id');
	}

	/**
	 * @return mixed|null
	 */
	function get_author_name(){
		return $this->get_mapped_property('author_name');
	}

	/**
	 * @return mixed|null
	 */
	function get_url(){
		return $this->get_mapped_property('url');
	}

    /**
     * Return the item ID
     *
     * @return integer
     */
    function get_id() {
        return $this->get_mapped_property('id');
    }
    
    /**
     * Return the item title
     *
     * @return string
     */
    function get_title() {
		return $this->get_mapped_property('title');
    }

    /**
     * Return the item order type
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }

    /**
     * Return the parent ID
     *
     * @return integer
     */
    function get_parent() {
        return $this->get_mapped_property('parent');
    }

    /**
     * Return the item description
     *
     * @return string
     */
    function get_description() {
        return $this->get_mapped_property('description');
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::get_db_identifier()
     */
    public function get_db_identifier() {
    	return $this->get_mapped_property('collection_id');
    }
    
    /**
     * Use especial Item capabilities
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::get_capabilities()
     */
    public function get_capabilities() {
    	return $this->get_collection()->get_items_capabilities();
    }
    
    /**
     * Define the title
     *
     * @param [string] $value
     * @return void
     */
    function set_title($value) {
        $this->set_mapped_property('title', $value);
    }

    /**
     * Define the order type
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Define the parent ID
     *
     * @param [integer] $value
     * @return void
     */
    function set_parent($value) {
        $this->set_mapped_property('parent', $value);
    }

    /**
     * Define the description
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Return a List of ItemMetadata objects
     *
     * It will return all fields associeated with the collection this item is part of.
     *
     * If the item already has a value for any of the fields, it will be available.
     *
     * @return array Array of ItemMetadata objects
     */
    function get_fields() {
        global $Tainacan_Item_Metadata;
        return $Tainacan_Item_Metadata->fetch($this, 'OBJECT');

    }

    /**
     * set meta cap object
     */
    protected function set_cap() {
    	$item_collection = $this->get_collection();
    	if ($item_collection) {
    		$this->cap = $item_collection->get_items_capabilities();
    	}
    }

    /**
     *
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::validate()
     */
    function validate(){
        
        if ( !in_array($this->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
        
        if( parent::validate() ){
            $arrayItemMetadata = $this->get_fields();
            if( $arrayItemMetadata  ){
                foreach ( $arrayItemMetadata as $itemMetadata ) {

                    // avoid core fields to re-validate
                    $pos = strpos($itemMetadata->get_field()->get_field_type(), 'Core');
                    if( $pos !== false ){
                        continue;
                    }

                    if( !$itemMetadata->validate() ){
                        $errors = $itemMetadata->get_errors();
                        $this->add_error( $itemMetadata->get_field()->get_name(), $errors );
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::validate()
     */
    public function validate_core_fields(){
        if ( !in_array($this->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        return parent::validate();
    }
}