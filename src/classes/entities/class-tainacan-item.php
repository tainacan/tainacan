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
	 * @return mixed|null
	 */
	function get_featured_img(){
		return $this->get_mapped_property('featured_image');
	}

	/**
	 * @param $value
	 */
	function set_featured_img($value){
		$this->set_mapped_property('featured_image', $value);
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
    	if(!empty($this->get_db_identifier())) {
	    	$db_identifier = Collection::$db_identifier_prefix.$this->get_db_identifier().Collection::$db_identifier_sufix;
	    	if(!empty($db_identifier))
	    	{
		    	$post_type_obj = get_post_type_object($db_identifier);
		    	if(!is_object($post_type_obj)) {
		    		throw new \Exception(sprintf("Collection post type (%s) is not setted and cannot be registred", $db_identifier));
		    	}
		    	$this->cap = $post_type_obj->cap;
	    	}
    	}
    }

    /**
     *
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::validate()
     */
    function validate(){
        //var_dump($this->get_status()); die;
        if ( !in_array($this->get_status(), apply_filters('tainacan-status-validation', ['publish','future','private'])) )
            return true;
        
        if( parent::validate() ){
            $arrayItemMetadata = $this->get_fields();
            if( $arrayItemMetadata  ){
                foreach ( $arrayItemMetadata as $itemMetadata ) {
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
}