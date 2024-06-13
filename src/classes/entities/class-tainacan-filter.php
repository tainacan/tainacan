<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the entity Filter
 */
class Filter extends Entity {
    use \Tainacan\Traits\Entity_Collection_Relation;

    protected
        $name,
        $description,
        $order,
        $metadatum,
        $metadatum_id,
        $max_options,
        $filter_type,
        $filter_type_options,
        $begin_with_filter_collapsed,
        $display_in_repository_level_lists,
        $description_bellow_name;

    static $post_type = 'tainacan-filter';
    public $enabled_for_collection = true;

    /**
     * {@inheritDoc}
     * @see \Tainacan\Entities\Entity::repository
     * @var string
     */
    protected $repository = 'Filters';

	public function  __toString(){
		return apply_filters("tainacan-filter-to-string", $this->get_name(), $this);
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function _toArray(){
		$filter_array = parent::_toArray();
		$metadatum_id = $filter_array['metadatum_id'];
		$metadatum = $this->get_metadatum();

		$filter_array['metadatum'] = [];
		$filter_array['metadatum']['metadatum_id'] = $metadatum_id;
		
		if ($metadatum instanceof Metadatum) {
			$filter_array['metadatum']['metadatum_name'] = $metadatum->get_name();
			$meta_object = $metadatum->get_metadata_type_object();
			if (is_object($meta_object)) {
				$filter_array['metadatum']['metadata_type_object'] = $meta_object->_toArray();
			}
		}
		
		
		return apply_filters('tainacan-filter-to-array', $filter_array, $this);
	}

    /**
     * Return the filter name
     *
     * @return string
     */
    function get_name() {
        return $this->get_mapped_property('name');
    }

	/**
	 * @return mixed|null
	 */
	function get_description() {
    	return $this->get_mapped_property('description');
    }
    
    /**
	 * @return mixed|null
	 */
	function get_placeholder() {
    	return $this->get_mapped_property('placeholder');
    }

    /**
     * Return the filter order type
     *
     * @return string
     */
    function get_order() {
        return $this->get_mapped_property('order');
    }


	/**
	 * Return max number of options to be showed
	 * @return mixed|null
	 */
	function get_max_options(){
    	return $this->get_mapped_property('max_options');
    }

	/**
	 * Set max number of options to be showed
	 *
	 * @param $max_options
	 */
	function set_max_options($max_options){
		$this->set_mapped_property('max_options', $max_options);
    }

	/**
	 * Return the metadatum ID
	 *
	 * @return integer Metadatum ID
	 */
    function get_metadatum_id() {
        return $this->get_mapped_property('metadatum_id');
    }
    
    /**
	 * Return the metadatum object
	 *
	 * @return Metadatum | null
	 * @throws \Exception
	 */
    function get_metadatum() {
        if (isset($this->metadatum)) {
            return $this->metadatum;
        }
        $id = $this->get_metadatum_id();
        $metadatum = \Tainacan\Repositories\Metadata::get_instance()->fetch((int) $id);
        if ($metadatum instanceof Metadatum) {
            $this->metadatum = $metadatum;
            return $metadatum;
        } else {
            return null;
        }
    }

    /**
     * Return the an object child of \Tainacan\Filter_Types\Filter_Type with options
     *
     * @return \Tainacan\Filter_Types\Filter_Type The filter type class with filled options
     */
    function get_filter_type_object(){
        $class_name = $this->get_filter_type();

		if( is_null($class_name) || !class_exists( $class_name ) ){
			return null;
		}

        $object_type = new $class_name();
        $object_type->set_options(  $this->get_mapped_property('filter_type_options') );
        return $object_type;
    }

    /**
     * Return the class name for the filter type
     *
     * @return string The
     */
    function get_filter_type(){
        return $this->get_mapped_property('filter_type');
    }

    /**
     * Return the actual options for the current filter type
     *
     * @return array Configurations for the filter type object
     */
    function get_filter_type_options(){
        $object = $this->get_filter_type_object();
        if ($object) {
            return $object->get_options(); // merge with dedault filter type options
        }
        return $this->get_mapped_property('filter_type_options');
    }

    /**
     * Return 'yes' or 'no' to the option of begining the filter collapsed
     * 
     * @return string
     */
    public function get_begin_with_filter_collapsed() {
        return $this->get_mapped_property('begin_with_filter_collapsed');
    }

    /**
     * Return 'yes' or 'no' to the option of display in repository level lists
     * 
     * @return string
     */
    public function get_display_in_repository_level_lists() {
        return $this->get_mapped_property('display_in_repository_level_lists');
    }
    	
	/**
	 * Return the filter description_bellow_name
	 *
	 * @return string
	 */
	function get_description_bellow_name() {
		return $this->get_mapped_property('description_bellow_name');
	}

    /**
     * Define the filter name
     *
     * @param [string] $value
     * @return void
     */
    function set_name($value) {
        $this->set_mapped_property('name', $value);
    }

    /**
     * Define the filter order type
     *
     * @param [string] $value
     * @return void
     */
    function set_order($value) {
        $this->set_mapped_property('order', $value);
    }

    /**
     * Define the filter description
     *
     * @param [string] $value
     * @return void
     */
    function set_description($value) {
        $this->set_mapped_property('description', $value);
    }

    /**
     * Define the filter placeholder
     *
     * @param [string] $value
     * @return void
     */
    function set_placeholder($value) {
        $this->set_mapped_property('placeholder', $value);
    }

    /**
     * Define the filter metadatum passing an object
     * 
     * @param \Tainacan\Entities\Metadatum
     * @return void
     */
    function set_metadatum( \Tainacan\Entities\Metadatum $value ){
    	$id = $value->get_id();

        $this->set_metadatum_id($id);
        $this->metadatum = $value;
    }
    
    /**
     * Define the filter metadatum passing an ID
     * 
     * @param int $value the metadatum ID
     * @return void
     */
    function set_metadatum_id( $value ){
        unset($this->metadatum);
        $this->set_mapped_property('metadatum_id', $value);
    }

    /**
     * Save the filter type class name
     *
     * @param string | \Tainacan\Filter_Types\Filter_Type $value The name of the class or the instance
     */
    public function set_filter_type($value){
		$this->set_mapped_property('filter_type', ( is_object( $value ) ) ? get_class( $value ) : $value );
    }

    /**
     * Tells if filter should begin collapsed, not loading facets
     * 
     * @param string $begin_with_filter_collapsed
     */
    public function set_begin_with_filter_collapsed($begin_with_filter_collapsed) {
        $this->set_mapped_property('begin_with_filter_collapsed', $begin_with_filter_collapsed);
    }

    /**
     * Tells if filter should appear in repository level lists, even belonging to a collection
     * 
     * @param string $display_in_repository_level_lists
     */
    public function set_display_in_repository_level_lists($display_in_repository_level_lists) {
        $this->set_mapped_property('display_in_repository_level_lists', $display_in_repository_level_lists);
    }

	/**
	 * Transient property used to store the status of the filter for a particular collection
	 *
	 * Used by the API to tell front end when a metadatum is disabled
	 *
	 */
	public function get_enabled_for_collection() {
		return $this->enabled_for_collection;
	}
	public function set_enabled_for_collection($value) {
		$this->enabled_for_collection = $value;
	}

    /**
	 * Set filter description_bellow_name
	 *
	 * @param [string] $value If the description will be displayed below the name instead of inside a tooltip (yes/no)
	 * @return void
	 */
	function set_description_bellow_name($value) {
		$this->set_mapped_property('description_bellow_name', $value);
	}


	/**
	 * {@inheritdoc }
	 *
	 * Also validates the metadatum, calling the validate_options callback of the Metadatum Type
	 *
	 * @return bool valid or not
	 * @throws \Exception
	 */
    public function validate() {
        $is_valid = parent::validate();
        if (false === $is_valid)
            return false;


        $status = $this->get_status();
        $status_obj = get_post_status_object($status);
        
        if ($status_obj->public) {
            $metadatum = $this->get_metadatum();
            if ($metadatum) {
                $metadatum_status_obj = get_post_status_object($metadatum->get_status());
                if ( ! $metadatum_status_obj->public ) {
                    $this->add_error('status', __('Filter cannot be public because the related metadatum is private', 'tainacan'));
                    return false;
                }
            }
        }

        $fto = $this->get_filter_type_object();
        if (is_object($fto)) {
            $is_valid = $fto->validate_options( $this );
        }

        if (true === $is_valid) {
        	$this->set_as_valid();
	        return true;
        }

        if (!is_array($is_valid)) {
	        throw new \Exception( "Return of validate_options metadatum type method should be an Array in case of error" );
        }

        foreach ($is_valid as $metadatum => $message) {
            $this->add_error($metadatum, $message);
        }

        $this->add_error('filter_type_options', $is_valid);

        return false;
    }

    /**
     * Set Filter type options
     *
     * @param [string || integer] $value
     * @return void
     */
    function set_filter_type_options( $value ){
        $this->set_mapped_property('filter_type_options', $value);
    }
}