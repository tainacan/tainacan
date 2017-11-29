<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Entity Super class
 *
 */
class Entity {
	/**
	 * The repository of that entity 
	 * @var \Tainacan\Repositories\Repository
	 */
	protected $repository;
	/**
	 * Array of errors, for example, register validations errors
	 * @var array
	 */
    private $errors = [];
    /**
     * The WordPress post_type for store this class if is needed, false otherwise
     * @var string
     */
    protected static $post_type = false;
    /**
     * Store the WordPress post object 
     * @var \WP_Post
     */
    public $WP_Post;

    /**
     * return the value for a mapped property
     * @param string $prop id of property
     * @return mixed property value
     */
    public function get_mapped_property($prop) {
        
        global ${$this->repository};
        $map = ${$this->repository}->get_map();
        
        if (isset($this->$prop) && !empty($this->$prop)){
            return $this->$prop;
        }
        
        if (!array_key_exists($prop, $map)){
            return null;
        }
        
        $mapped = $map[$prop]['map'];
        
        if ( $mapped == 'meta') {
            $property = isset($this->WP_Post->ID) ? get_post_meta($this->WP_Post->ID, $prop, true) : null;
        } elseif ( $mapped == 'meta_multi') {
            $property = isset($this->WP_Post->ID) ? get_post_meta($this->WP_Post->ID, $prop, false) : null;
        } elseif ( $mapped == 'termmeta' ){
            $property = get_term_meta($this->WP_Term->term_id, $prop, true);
        } elseif ( isset( $this->WP_Post )) {
            $property = isset($this->WP_Post->$mapped) ? $this->WP_Post->$mapped : null;
        } elseif ( isset( $this->WP_Term )) {
            $property = isset($this->WP_Term->$mapped) ? $this->WP_Term->$mapped : null;
        }
        
        if (empty($property) && isset($map[$prop]['default']) && !empty($map[$prop]['default'])){
            $property = $map[$prop]['default'];
        }
            
        return $property;
    }
    
    /**
     * set the value of a mapped property
     * @param string $prop id of the property
     * @param mixed $value the value to be setted
     */
    public function set_mapped_property($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Validate the class values/properties, to be used before insert/save/update
     * @return boolean
     */
    public function validate() {
        
        global ${$this->repository};
        $map = ${$this->repository}->get_map();
        
        $is_valid = true;

        $this->reset_errors();
        
        foreach ($map as $prop => $mapped) {
            if (!$this->validate_prop($prop))
                $is_valid = false;
        }
        
        return $is_valid;
    }
    
    /**
     * Validate a single property
     * @param string $prop id of the property to be validate
     * @return boolean
     */
    public function validate_prop($prop) {
        global ${$this->repository};
        $map = ${$this->repository}->get_map();
        $mapped = $map[$prop];
        
        $is_valid = true;
        
        if (
            isset($mapped['validation']) && 
            is_object($mapped['validation']) &&
            method_exists($mapped['validation'], 'validate')
        ) {
            $validation = $mapped['validation'];
            $prop_value = $this->get_mapped_property($prop);
            
            if (is_array($prop_value)) {
                foreach ($prop_value as $val) {
                    if (!$validation->validate($val)) {
                        //
                        $this->add_error('invalid', $prop . ' is invalid');
                        $is_valid = false;
                    }
                }
            } else {
                if (!$validation->validate($prop_value)) {
                    //
                    $this->add_error('invalid', $prop . ' is invalid');
                    $is_valid = false;
                }
            }
        }
        
        return $is_valid;

    }
    
    public function get_errors() {
        return $this->errors;
    }
    
    public static function get_post_type() {
    	return static::$post_type;
    }
    
    public function add_error($type, $message) {
        $this->errors[] = [$type => $message];
    }
    
    /**
     * Clear the errors array
     */
    public function reset_errors() {
        $this->errors = [];
    }
 
}