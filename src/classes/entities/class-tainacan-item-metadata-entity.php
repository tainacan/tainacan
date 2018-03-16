<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Item Field Entity
 */
class Item_Metadata_Entity extends Entity {
	protected static $post_type = false;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Tainacan_Item_Metadata';
	
    function __construct(Item $item, Field $field) {
        
        $this->set_item($item);
        $this->set_field($field);
    }

	public function  __toString(){
		return 'Hello, I\'m the Item Field Entity';
	}

    public function  __toArray(){
        $as_array['value'] = $this->get_value();
	    $as_array['item']  = $this->get_item()->__toArray();
	    $as_array['field'] = $this->get_field()->__toArray();

	    return $as_array;
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
     * Define the field value
     *
     * @param [integer | string] $value
     * @return void
     */
    function set_value($value) {
        $this->value = $value;
    }
    
    /**
     * Define the field
     *
     * @param Field $field
     * @return void
     */
    function set_field(Field $field) {
        $this->field = $field;
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
     * Return the field
     *
     * @return Field
     */
    function get_field() {
        return $this->field;
    }
    
    /**
     * Return the field value
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
     * Return true if field is multiple, else return false
     *
     * @return boolean
     */
    function is_multiple() {
        return $this->get_field()->is_multiple();
    }
    
    /**
     * Return true if field is key
     *
     * @return boolean
     */
    function is_collection_key() {
        return $this->get_field()->is_collection_key();
    }
    
    /**
     * Return true if field is required
     *
     * @return boolean
     */
    function is_required() {
        return $this->get_field()->is_required();
    }
    
    /**
     * Validate attributes
     *
     * @return boolean
     */
    function validate() {   
        $value = $this->get_value();
        $field = $this->get_field();
        $item = $this->get_item();
        
        if (empty($value) && $this->is_required()) {
            $this->add_error('required', $field->get_name() . ' is required');
            return false;
        }

        $classFieldType = $field->get_field_type_object();
        if( is_object( $classFieldType ) ){
            if( method_exists ( $classFieldType , 'validate' ) ){
                if( ! $classFieldType->validate( $this ) ) {
                    $this->add_error('field_type_error', $classFieldType->get_errors() );
                    return false;
                }
            }
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
                    $this->add_error('required', $field->get_name() . ' is required');
                    return false;
                }
                
                if (!$valid) {
                    $this->add_error('invalid', $field->get_name() . ' is invalid');
                    return false;
                }
                
                $this->set_as_valid();
                return true;   
            } else {
                $this->add_error('invalid', $field->get_name() . ' is invalid');
                return false;
            }
        } else {

            if( is_array($value) ){
                $this->add_error('not_multiple', $field->get_name() . ' do not accept array as value');
                return false;
            }
            
            if ($this->is_collection_key()) {
            	$Tainacan_Items = new \Tainacan\Repositories\Items();
                
                $test = $Tainacan_Items->fetch([
                    'meta_query' => [
                        [
                            'key'   => $this->field->get_id(),
                            'value' => $value
                        ],
                    ]
                ], $item->get_collection());

                if ($test->have_posts()) {
                    $this->add_error('key_exists', $field->get_name() . ' is a collection key and there is another item with the same value');
                    return false;
                }
            }

            $this->set_as_valid();
            return true;   
        }   
    }
}