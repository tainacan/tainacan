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
     * The WordPress capability for the entity post type. Default is to be equal to $post_type
     * @var string
     */
    protected static $capability_type = false;
    
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
     * Capabilities of the post_type, one of:
     * - edit_posts - Controls whether objects of this post type can be edited.
	 * - edit_others_posts - Controls whether objects of this type owned by other users
	 *   can be edited. If the post type does not support an author, then this will
	 *   behave like edit_posts.
	 * - publish_posts - Controls publishing objects of this post type.
	 * - read_private_posts - Controls whether private objects can be read.
     * - read - Controls whether objects of this post type can be read.
	 * - delete_posts - Controls whether objects of this post type can be deleted.
	 * - delete_private_posts - Controls whether private objects can be deleted.
	 * - delete_published_posts - Controls whether published objects can be deleted.
	 * - delete_others_posts - Controls whether objects owned by other users can be
	 *   can be deleted. If the post type does not support an author, then this will
	 *   behave like delete_posts.
	 * - edit_private_posts - Controls whether private objects can be edited.
	 * - edit_published_posts - Controls whether published objects can be edited.
     * @var object
     */
    public $cap;

	/**
	 * Create an instance of Entity 
	 *
	 * If ID or WP Post is passed, it retrieves the object from the database 
	 * 
	 * Attention: If the ID or Post provided do not match the Entity post type, an Exception will be thrown
	 * 
	 * @param integer|\WP_Post optional $which Entity ID or a WP_Post object for existing Entities. Leave empty to create a new Entity.
	 *
	 * @throws \Exception
	 */
    function __construct($which = 0) {
    	if (is_numeric($which) && $which > 0) {
    		$post = get_post($which);
			if ($post instanceof \WP_Post) {
    			$this->WP_Post = get_post($which);
    		} else {
				throw new \Exception( 'No entity was found with ID ' . $which );
			}
    		
    	} elseif ($which instanceof \WP_Post) {
    		$this->WP_Post = $which;
    	} elseif ( // is a stdClass Post object, like returned by delete
    		$which instanceof \stdClass &&
    		property_exists($which, 'post_type') &&
    		property_exists($which, 'ID') && 
    		property_exists($which, 'post_status')
    	) {
    		$this->WP_Post = new \WP_Post($which);
    	} elseif (is_array($which) && isset($which['ID'])) {
    		$this->WP_Post = new \WP_Post( (object)$which );
    	} else {
    		$this->WP_Post = new \StdClass();
    	}
		
		$collection_pt_pattern = '/' . Collection::$db_identifier_prefix . '\d+' . Collection::$db_identifier_sufix . '/';
		
    	if(
            $this->WP_Post instanceof \WP_Post &&
    		isset( $this->WP_Post->ID ) &&
    		( 
    			( $this->get_post_type() !== false && $this->WP_Post->post_type != $this->get_post_type() ) ||
    			// Lets check if it is a collection and have rigth post_type
    			( $this->get_post_type() === false && $this->WP_Post->post_type && ! preg_match($collection_pt_pattern, $this->WP_Post->post_type) ) 
    		)
    	) {
    		if($this->get_post_type() === false) {
    			
				throw new \Exception('the returned post is not the same type of the entity! expected: '.Collection::$db_identifier_prefix.$this->get_db_identifier().Collection::$db_identifier_sufix.' and actual: '.$this->WP_Post->post_type );
    		}
    		else {
    			throw new \Exception('the returned post is not the same type of the entity! expected: '.$this->get_post_type().' and actual: '.$this->WP_Post->post_type );
    		}
    	}
    	if($this->get_post_type() !== false) {
    		$this->cap = $this->get_capabilities();
    	} elseif ($this instanceof Item) {
    	    $item_collection = $this->get_collection();
            if ($item_collection) {
                $this->cap = $item_collection->get_items_capabilities();
            }
    	}
    }

    public function get_repository() {
        $namespace = '\Tainacan\Repositories\\'.$this->repository;
        $repository = $namespace::get_instance();

        return $repository;
    }

	/**
	 * @param $date
	 *
	 * @return string
	 */
	public function get_date_i18n($date){

		if($date) {
			$unix_time_stamp = strtotime( $date );

			return date_i18n( get_option( 'date_format' ), $unix_time_stamp );
		}

		return '';
	}

    /**
     * return the value for a mapped property
     * @param string $prop id of property
     * @return mixed property value
     */
    public function get_mapped_property($prop) {
    	if ( isset($this->$prop) ){
    		return $this->$prop;
    	}

    	//prop is not set at object, try to get from database
    	$repository = $this->get_repository();
		$value = $repository->get_mapped_property($this, $prop);

        return apply_filters('tainacan-entity-get-property', $value, $prop, $this);
    }
    
    /**
     * set the value of a mapped property
     *
     * This is a protected method. If you want to set an entity prop 
     * using the prop name dynamically, use the set() method
     * 
     * @param string $prop id of the property
     * @param mixed $value the value to be setted
     */
    protected function set_mapped_property($prop, $value) {
        $this->set_validated(false);
		$value = apply_filters('tainacan-entity-set-property', $value, $prop, $this);
        $this->$prop = $value;
    }
	
	/**
     * set the value property
     *
     * 
     * @param string $prop id of the property
     * @param mixed $value the value to be setted
     * @return null|mixed Null on failure, the value that was set on success
     */
    public function set($prop, $value) {
        $method = 'set_' . $prop;
		if ( method_exists($this, $method) ) {
			return $this->$method($value);
		}
    }

	/**
	 * get the value property
	 *
	 *
	 * @param string $prop id of the property
	 * @return null|mixed Null on failure, the value that was set on success
	 */
	public function get($prop) {
		$method = 'get_' . $prop;
		if ( method_exists($this, $method) ) {
			return $this->$method();
		}
	}

    /**
     * set the status of the entity
     * @param string $value
     */
    public function set_status($value){
    	$value = apply_filters('tainacan-set-post-status', $value);
    	$this->set_mapped_property('status', $value);
    }

    /**
     * Validate the class values/properties, to be used before insert/save/update
     *
     * If Entity is not valid, validation error messages are available via get_errors() method
     * 
     * @return boolean
     */
    public function validate() {

        $repository = $this->get_repository();
        $map = $repository->get_map();
        
        $is_valid = true;

        $this->reset_errors();
        
        foreach ($map as $prop => $mapped) {
            if (!$this->validate_prop($prop)) {
	            $is_valid = false;
            }
        }
        
        if($is_valid){
        	$this->set_as_valid();
        }

        return $is_valid;
    }
    
    /**
     * Validate a single property
     * @param string $prop id of the property to be validate
     * @return boolean
     */
    public function validate_prop($prop) {
        $repository = $this->get_repository();
        $map = $repository->get_map();
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

                        $this->add_error($prop, $message);
                        $is_valid = false;
                    }
                }
            } else {
                if (!$validation->validate($prop_value)) {
                	$this->add_error($prop, $message);
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
    
    public static function get_capability_type() {
    	return false !== static::$capability_type ? static::$capability_type : static::$post_type;
    }
    
    public function get_status(){
   		$value = $this->get_mapped_property('status');
   		if(empty($value)) $value = 'draft';
   		return apply_filters('tainacan-get-post-status', $value);
    }
    
    /**
     * Get entity DB identifier
     *
     * This identifier is used to register the entity on database, ex.: post_type
     *
     * @return string
     */
    function get_db_identifier() {
    	return self::get_post_type();
    }
    
    /**
     * Get the entity ID
     *
     * @return integer
     */
    public function get_id() {
    	return $this->get_mapped_property('id');
    }
    
    public function add_error($type, $message) {
        $this->errors[] = [$type => $message];
		$this->set_validated(false);
    }
    
    /**
     * Clear the errors array
     */
    public function reset_errors() {
        $this->errors = [];
    }
    
    public function get_validated() {
        return $this->validated;
    }
    
    protected function set_validated($value) {
        $this->validated = $value;
    }
    
    protected function set_as_valid() {
        $this->reset_errors();
        $this->set_validated(true);
        return true;
    }

    public function _toArray(){
        $repository = $this->get_repository();
	    $map = $repository->get_map();

	    $attributes = [];
	    foreach($map as $prop => $content) {
		    $attributes[$prop] = $this->get_mapped_property($prop);
	    }
		
		$hook_prefix = self::get_post_type();
		
		return apply_filters("{$hook_prefix}-to-array", $attributes, $this);
    }

	public function  _toJson(){
		return json_encode($this->_toArray(), JSON_NUMERIC_CHECK);
	}
	
	/**
	 * Return if user can read this entity
	 * @param int|\WP_User $user
	 * @return bool
	 */
	public function can_read($user = null) {
        $repository = $this->get_repository();
		return $repository->can_read($this, $user);
	}
	
	/**
	 * Return if user can edit this entity
	 * @param int|\WP_User|null $user the user for capability check, null for the current user
	 * @return bool
	 */
	public function can_edit($user = null)	{
        $repository = $this->get_repository();
		return $repository->can_edit($this, $user);
	}
	
	/**
	 * Return if user can delete this entity
	 * @param int|\WP_User|null $user the user for capability check, null for the current user
	 * @return bool
	 */
	public function can_delete($user = null)	{
        $repository = $this->get_repository();
		return $repository->can_delete($this, $user);
	}
	
	/**
	 * Return if user can publish this entity
	 * @param int|\WP_User|null $user the user for capability check, null for the current user
	 * @return bool
	 */
	public function can_publish($user = null)	{
        $repository = $this->get_repository();
		return $repository->can_publish($this, $user);
	}
	
	/**
	 * Get the capabilities list for the post type of the entity
	 *
	 * @uses get_post_type_capabilities to get the list.
	 *
	 * This method is usefull for getting the capabilities of the entity post type
	 * regardless if it has been already registered or not.
	 *
	 * @return object Object with all the capabilities as member variables.
	 */
	public function get_capabilities() {
        $args = [
			'map_meta_cap'    => true,
			'capability_type' => self::get_capability_type(),
			'capabilities'    => array()
		];
		
		return get_post_type_capabilities((object) $args);
	}
	
	/**
	 * Compare this entity props with self old values or with $which other entity 
	 * @param Entity|integer|\WP_Post $which default ($which = 0) to self compare with stored entity
	 * @return array
	 */
	public function diff($which = 0) {
        $repository = $this->get_repository();
		return $repository->diff($which, $this);
	}
	
}