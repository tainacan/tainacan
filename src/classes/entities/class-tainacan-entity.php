<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Entity Super class
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
     * Indicates wether an entity was validated, calling the validate() method
     *
     * Entities MUST be validated before attempt to save
     * 
     * @var boolean
     */
    private $validated = false;
    
    /**
     * Create an instance of Entity and get post data from database or create a new StdClass if $which is 0 
     * @param integer|\WP_Post optional $which Entity ID or a WP_Post object for existing Entities. Leave empty to create a new Entity.
     */
    function __construct($which = 0) {
    	if (is_numeric($which) && $which > 0) {
    		$post = get_post($which);
    		
    		if ($post instanceof \WP_Post) {
    			$this->WP_Post = get_post($which);
    		}
    		
    	} elseif ($which instanceof \WP_Post) {
    		$this->WP_Post = $which;
    	} else {
    		$this->WP_Post = new \StdClass();
    	}
    }
    
    /**
     * return the value for a mapped property
     * @param string $prop id of property
     * @return mixed property value
     */
    public function get_mapped_property($prop) {
    	if (isset($this->$prop) && !empty($this->$prop)){
    		return $this->$prop;
    	}
    	//prop is not set at object, try to get from database
        global ${$this->repository};
        return ${$this->repository}->get_mapped_property($this, $prop);
    }
    
    /**
     * set the value of a mapped property
     * @param string $prop id of the property
     * @param mixed $value the value to be setted
     */
    public function set_mapped_property($prop, $value) {
        $this->set_validated(false);
        $this->$prop = $value;
    }

	/**
	 * Define the entity status
	 *
	 * @param [string] $value
	 */
	public function set_status($value){
    	$value = apply_filters('tainacan-set-post-status', $value);
    	$this->set_mapped_property('status', $value);
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
        
        $this->set_validated($is_valid);
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
            $message = ( isset( $mapped['on_error'] ) ) ? $mapped['on_error'] : $prop. __(' is invalid', 'tainacan');

            if (is_array($prop_value)) {
                foreach ($prop_value as $val) {
                    if (!$validation->validate($val)) {
                        //
                        $this->add_error('invalid', $message);
                        $is_valid = false;
                    }
                }
            } else {
                if (!$validation->validate($prop_value)) {
                    //
                    $this->add_error('invalid', $message);
                    $is_valid = false;
                }
            }
        }
        
        return $is_valid;

    }

	/**
	 * Return the array of error messages
	 *
	 * @return array
	 */
	public function get_errors() {
        return $this->errors;
    }

	/**
	 * Return the entity post type
	 *
	 * @return string
	 */
	public static function get_post_type() {
    	return static::$post_type;
    }

	/**
	 * Return the entity status
	 *
	 * @return mixed
	 */
	public function get_status(){
   		$value = $this->get_mapped_property('status');
   		if(empty($value)) $value = 'draft';
   		return apply_filters('tainacan-get-post-status', $value);
    }

	/**
	 * Make a array of error messages
	 *
	 * @param $type
	 * @param $message
	 */
	public function add_error($type, $message) {
        $this->errors[] = [$type => $message];
    }
    
    /**
     * Clear the errors array
     */
    public function reset_errors() {
        $this->errors = [];
    }

	/**
	 * Return true if is validated or false if not validated
	 *
	 * @return boolean
	 */
	public function get_validated() {
        return $this->validated;
    }

	/**
	 * Define validated
	 *
	 * @param [boolean] $value
	 */
	protected function set_validated($value) {
        $this->validated = $value;
    }

	/**
	 * Define as valid
	 *
	 * @return boolean
	 */
	protected function set_as_valid() {
        $this->reset_errors();
        $this->set_validated(true);
        return true;
    }

	/**
	 * Return the attributes of the entity in JSON
	 *
	 * @return JSON
	 */
	public function  __toJSON(){
		global ${$this->repository};
		$map = ${$this->repository}->get_map();

		$attributes = [];
		foreach($map as $prop => $content) {
			$attributes[$prop] = $this->get_mapped_property($prop);
		}

		return json_encode($attributes, JSON_NUMERIC_CHECK, JSON_UNESCAPED_UNICODE);
	}
 
}