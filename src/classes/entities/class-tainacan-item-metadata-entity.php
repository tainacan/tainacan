<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Item Metadatum Entity
 */
class Item_Metadata_Entity extends Entity {
	protected static $post_type = false;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Item_Metadata';
	
	protected
        $item,
		$metadatum,
		$parent_meta_id,
		$meta_id,
		$has_value,
		$value;
	
	/**
	 * 
	 * @param Item   $item    Item Entity
	 * @param Metadatum  $metadatum   Metadatum Entity
	 * @param int $meta_id ID for a specific meta row 
	 */
    function __construct(Item $item = null, Metadatum $metadatum = null, $meta_id = null, $parent_meta_id = null) {
        
        $this->set_item($item);
        $this->set_metadatum($metadatum);
		
		if (!is_null($meta_id) && is_int($meta_id)) {
			$this->set_meta_id($meta_id);
		}
		
		if (!is_null($parent_meta_id) && is_int($parent_meta_id)) {
			$this->set_parent_meta_id($parent_meta_id);
		}
		
		
    }
	
	/**
	 * Gets the string used before each value when concatenating multiple values 
	 * to display item metadata value as html or string 
	 * 
	 * @return string 
	 */
	public function get_multivalue_prefix() {
		$metadatum = $this->get_metadatum();
		$value = '';
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				
				if ( method_exists($fto, 'get_multivalue_prefix') ) {
					$value = $fto->get_multivalue_prefix();
				}
				
			}
		}
		return apply_filters('tainacan-item-metadata-get-multivalue-prefix', $value, $this);
	}
	
	/**
	 * Gets the string used after each value when concatenating multiple values 
	 * to display item metadata value as html or string 
	 * 
	 * @return string 
	 */
	public function get_multivalue_suffix() {
		$metadatum = $this->get_metadatum();
		$value = '';
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				
				if ( method_exists($fto, 'get_multivalue_suffix') ) {
					$value = $fto->get_multivalue_suffix();
				}
				
			}
		}
		return apply_filters('tainacan-item-metadata-get-multivalue-suffix', $value, $this);
	}
	
	/**
	 * Gets the string used in between each value when concatenating multiple values 
	 * to display item metadata value as html or string 
	 * 
	 * @return string 
	 */
	public function get_multivalue_separator() {
		$metadatum = $this->get_metadatum();
		$value = '<span class="multivalue-separator"> | </span>';
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				
				if ( method_exists($fto, 'get_multivalue_separator') ) {
					$value = $fto->get_multivalue_separator();
				}
				
			}
		}
		return apply_filters('tainacan-item-metadata-get-multivalue-separator', $value, $this);
	}
	
	/**
	 * Get the value as a HTML string, with markup and links
	 * @return string
	 */
	public function  get_value_as_html(){
		$metadatum = $this->get_metadatum();
		
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				
				if ( method_exists($fto, 'get_value_as_html') ) {
					return $fto->get_value_as_html($this);
				}
				
			}
		}
		
		$value = $this->get_value();
		
		$return = '';
		
		if ( $this->is_multiple() ) {
			
			$total = sizeof($value);
			$count = 0;
			$prefix = $this->get_multivalue_prefix();
			$suffix = $this->get_multivalue_suffix();
			$separator = $this->get_multivalue_separator();
			
			foreach ($value as $v) {
				
				$return .= $prefix;
				
				$return .= (string) $v;
				
				$return .= $suffix;
				
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
			
		} else {
			$return = (string) $value;
		}

		return $return;
		
		
	}

	/**
	 * Get the value as a plain text string
	 * @return string
	 */
	public function get_value_as_string() {
		return strip_tags($this->get_value_as_html());
	}
	
	/**
	 * Get value as an array
	 * @return [type] [description]
	 */
	public function get_value_as_array() {
		$value = $this->get_value();
		
		if ( $this->is_multiple() ) {
			
			$return = [];
			
			foreach ($value as $v) {
				if ( $v instanceof Term || $v instanceof ItemMetadataEntity ) {
					$return[] = $v->_toArray();
				} else {
					$return[] = $v;
				}
			}
			
		} else {
			
			$return = '';
			
			if ( $value instanceof Term || $value instanceof ItemMetadataEntity ) {
				$return = $value->_toArray();
			} else {
				$return = $value;
			}
		}

		return $return;

	}
	
	/**
	 * Convert the object to an Array
	 * @return array the representation of this object as an array
	 */
    public function  _toArray(){
		$as_array = [];
		
		$as_array['value'] = $this->get_value_as_array();
		$as_array['value_as_html'] = $this->get_value_as_html();
		$as_array['value_as_string'] = $this->get_value_as_string();

		if($this->get_metadatum()->get_metadata_type_object()->get_primitive_type() === 'date'){
			$as_array['date_i18n'] = $this->get_date_i18n($this->get_value_as_string());
		}

	    $as_array['item']  = $this->get_item()->_toArray();
	    $as_array['metadatum'] = $this->get_metadatum()->_toArray();

		return apply_filters('tainacan-item-metadata-to-array', $as_array, $this);
    }
    
    /**
     * Define the item
     *
     * @param Item $item
     * @return void
     */
    function set_item(Item $item = null) {
        $this->item = $item;
    }
    
    /**
     * Define the metadatum value
     *
     * @param [integer | string] $value
     * @return void
     */
    function set_value($value) {
        $this->value = $value;
    }
    
    /**
     * Define the metadatum
     *
     * @param Metadatum $metadatum
     * @return void
     */
    function set_metadatum(Metadatum $metadatum = null) {
        $this->metadatum = $metadatum;
    }
	
	/**
	 * Set the specific meta ID for this metadata.
	 *
	 * When this value is set, get_value() will use it to fetch the value from 
	 * the post_meta table, instead of considering the item and metadatum IDs
	 * 
	 * @param int $meta_id the ID of a specifica post_meta row
	 */
	function set_meta_id($meta_id) {
		if (is_int($meta_id)) {
			$this->meta_id = $meta_id;
			return true;
			// TODO: Should we check here to see if this meta_id is really from this metadatum and item?
		}
		return false;
	}
	
	/**
	 * Set parent_meta_id. Used when a item_metadata is inside a compound Metadatum 
	 *
	 * When you have a multiple compound metadatum, this indicates of which instace of the value this item_metadata is attached to
	 * 
	 * @param [type] $parent_meta_id [description]
	 */
	function set_parent_meta_id($parent_meta_id) {
		if (is_int($parent_meta_id)) {
			$this->parent_meta_id = $parent_meta_id;
			return true;
			// TODO: Should we check here to see if this meta_id is really from this metadatum and item?
		}
		return false;
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
     * Return the metadatum
     *
     * @return Metadatum
     */
    function get_metadatum() {
        return $this->metadatum;
    }
	
	/**
     * Return the meta_id
     *
     * @return Metadatum
     */
    function get_meta_id() {
        return isset($this->meta_id) ? $this->meta_id : null;
    }
	
	/**
     * Return the meta_id
     *
     * @return Metadatum
     */
    function get_parent_meta_id() {
        return isset($this->parent_meta_id) ? $this->parent_meta_id : 0;
    }
    
    /**
     * Return the metadatum value
     *
     * @return string | integer
     */
    function get_value() {
        if (isset($this->value))
            return $this->value;
        
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        return $Tainacan_Item_Metadata->get_value($this);
    }
	
	/**
     * Check wether the item has a value stored in the database or not
     *
     * @return bool
     */
    function has_value() {
        if (isset($this->has_value))
            return $this->has_value;
        
		$value = $this->get_value();
		$this->has_value = (is_array($value)) ? !empty(array_filter($value)) : !empty($value);
		return $this->has_value;
    }
    
    /**
     * Return true if metadatum is multiple, else return false
     *
     * @return boolean
     */
    function is_multiple() {
        return $this->get_metadatum()->is_multiple();
    }
    
    /**
     * Return true if metadatum is key
     *
     * @return boolean
     */
    function is_collection_key() {
        return $this->get_metadatum()->is_collection_key();
    }
    
    /**
     * Return true if metadatum is required
     *
     * @return boolean
     */
    function is_required() {
        return $this->get_metadatum()->is_required();
    }
    
    /**
     * Validate attributes
     *
     * @return boolean
     */
    function validate() {   
        $value = $this->get_value();
        $metadatum = $this->get_metadatum();
        $item = $this->get_item();

	    if (empty($value) && $this->is_required() && in_array( $item->get_status(), apply_filters( 'tainacan-status-require-validation', [
			    'publish',
			    'future',
			    'private'
		    ] ) )
	    ) {
		    $this->add_error('required', $metadatum->get_name() . ' is required');
		    return false;
	    } elseif (empty($value) && !$this->is_required()) {
		    $this->set_as_valid();
		    return true;
	    } elseif(empty($value) && $this->is_required() && !in_array( $item->get_status(), apply_filters( 'tainacan-status-require-validation', [
			    'publish',
			    'future',
			    'private'
		    ] ) )) {

		    $this->set_as_valid();
		    return true;
	    }

        $classMetadatumType = $metadatum->get_metadata_type_object();
        if( is_object( $classMetadatumType ) ){
            if( method_exists ( $classMetadatumType , 'validate' ) ){
                if( ! $classMetadatumType->validate( $this ) ) {
                    $this->add_error('metadata_type_error', $classMetadatumType->get_errors() );
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
                }
                
                if ($this->is_required() && !$one_filled) {
                    $this->add_error('required', $metadatum->get_name() . ' is required');
                    return false;
                }
                
                if (!$valid) {
                    $this->add_error('invalid', $metadatum->get_name() . ' is invalid');
                    return false;
                }
                
                $this->set_as_valid();
                return true;   
            } else {
                $this->add_error('invalid', $metadatum->get_name() . ' is invalid');
                return false;
            }
        } else {

            if( is_array($value) ){
				$this->add_error('not_multiple', $metadatum->get_name() . ' do not accept array as value');
                return false;
            }
            
            if ($this->is_collection_key()) {
                $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
                
                $test = $Tainacan_Items->fetch([
                    'meta_query' => [
                        [
                            'key'   => $this->metadatum->get_id(),
                            'value' => $value
                        ],
                    ],
					'post__not_in' => [$item->get_id()]
                ], $item->get_collection());

                if ($test->have_posts()) {
                    $this->add_error('key_exists', $metadatum->get_name() . ' is a collection key and there is another item with the same value');
                    return false;
                }
            }

            $this->set_as_valid();
            return true;   
        }   
    }
}