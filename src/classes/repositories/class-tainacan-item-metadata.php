<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Item_Metadata extends Repository {

    protected function __construct()
    {
        parent::__construct();
    }

    public $entities_type = '\Tainacan\Entities\Item_Metadata_Entity';

    private static $instance = null;

    public static function getInstance()
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function insert($item_metadata) {

        $unique = !$item_metadata->is_multiple();
		
		$field_type = $item_metadata->get_field()->get_field_type_object();
		if ($field_type->get_core()) {
			$this->save_core_field_value($item_metadata);
		} elseif ($field_type->get_primitive_type() == 'term') {
			$this->save_terms_field_value($item_metadata);
		} elseif ($field_type->get_primitive_type() == 'compound') {
			// do nothing. Compound values are updated when its child fields are updated
			return $item_metadata;
		} else {
			if ($unique) {
				
				if (is_int($item_metadata->get_meta_id())) {
					update_metadata_by_mid( 'post', $item_metadata->get_meta_id(), wp_slash( $item_metadata->get_value() ) );
				} else {
					
					/**
					 * When we are adding a field that is child of another, this means it is inside a compound field 
					 *
					 * In that case, if the Item_Metadata object is not set with a meta_id, it means we want to create a new one 
					 * and not update an existing. This is the case of a multiple compound field.
					 */
					if ( $item_metadata->get_field()->get_parent() > 0 && is_null($item_metadata->get_meta_id()) ) {
						$added_meta_id = add_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash( $item_metadata->get_value() ) );
						$added_compound = $this->add_compound_value($item_metadata, $added_meta_id);
					} else {
						update_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash( $item_metadata->get_value() ) );
					}
					
				}
				
	        } else {
	            delete_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id());
	            
	            if (is_array($item_metadata->get_value())){
	            	$values = $item_metadata->get_value();

	                foreach ($values as $value){
	                    add_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash( $value ));
	                }
	            }
	        }
		}
		
        
        
        do_action('tainacan-insert', $item_metadata);
        do_action('tainacan-insert-Item_Metadata_Entity', $item_metadata);

        $new_entity = new Entities\Item_Metadata_Entity($item_metadata->get_item(), $item_metadata->get_field());
		
		if (isset($added_compound) && is_int($added_compound)) {
			$new_entity->set_parent_meta_id($added_compound);
		}
		
		if (isset($added_meta_id) && is_int($added_meta_id)) {
			$new_entity->set_meta_id($added_meta_id);
		}
		
		return $new_entity;	
		
    }

	/**
	 * @param $item_metadata
	 *
	 * @return mixed|void
	 */
	public function delete($item_metadata){}

    public function save_core_field_value(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        $field_type = $item_metadata->get_field()->get_field_type_object();
        if ($field_type->get_core()) {
            $item = $item_metadata->get_item();
            $set_method = 'set_' . $field_type->get_related_mapped_prop();
            $value = $item_metadata->get_value();
            $item->$set_method( is_array( $value ) ? $value[0] : $value );
            if ($item->validate_core_fields()) {
                $Tainacan_Items = \Tainacan\Repositories\Items::getInstance();
                $Tainacan_Items->insert($item);
            } else {
                throw new \Exception('Item metadata should be validated beforehand');
            }
        }
    }
	
	public function save_terms_field_value($item_metadata) {
		$field_type = $item_metadata->get_field()->get_field_type_object();
		if ($field_type->get_primitive_type() == 'term') {
			$new_terms = $item_metadata->get_value();
			$taxonomy = new Entities\Taxonomy( $field_type->get_option('taxonomy_id') );
			if( $taxonomy ){
                wp_set_object_terms($item_metadata->get_item()->get_id(), $new_terms, $taxonomy->get_db_identifier() );
            }


		}
	}
	
	/**
	 * 
	 * @return null|ind the meta id of the created compound metadata
	 */
	public function add_compound_value(Entities\Item_Metadata_Entity $item_metadata, $meta_id) {
		
		$current_value = get_metadata_by_mid( 'post', $item_metadata->get_parent_meta_id() );
		
		if (is_object($current_value))
		 	$current_value = $current_value->meta_value;
			
		if ( !is_array($current_value) )
			$current_value = [];
		
		if ( !in_array( $meta_id, $current_value ) ) {
			$current_value[] = $meta_id;
		}
		
		if ( $item_metadata->get_parent_meta_id() > 0 ) {
			update_metadata_by_mid( 'post', $item_metadata->get_parent_meta_id(), $current_value );
		} elseif ( $item_metadata->get_field()->get_parent() > 0 ) {
			return add_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_field()->get_parent(), $current_value );
		}
		
		
	}

	/**
	 * Fetch Item Field objects related to an Item
	 *
	 * @param Entities\Item $object
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function fetch($object, $output = null ){
        if($object instanceof Entities\Item){
            $Tainacan_Fields = \Tainacan\Repositories\Fields::getInstance();
            
            $collection = $object->get_collection();
            
            if (!$collection instanceof Entities\Collection){
                return [];
            }
            
            $meta_list = $Tainacan_Fields->fetch_by_collection($collection, [], 'OBJECT' );
            
            $return = [];
            
            if (is_array($meta_list)) {
                foreach ($meta_list as $meta) {
                    $return[] = new Entities\Item_Metadata_Entity($object, $meta);
                }
            }
            
            return $return;
        }else{
            return [];
        }
    }

    /**
     * Get the value for a Item field.
     *
     * @param Entities\Item_Metadata_Entity $item_metadata
     * @return mixed
     */
    public function get_value(Entities\Item_Metadata_Entity $item_metadata) {
        $unique = ! $item_metadata->is_multiple();
        
        $field_type = $item_metadata->get_field()->get_field_type_object();
        if ($field_type->get_core()) {
            $item = $item_metadata->get_item();
            
            $get_method = 'get_' . $field_type->get_related_mapped_prop();
            return $item->$get_method();
        
		} elseif ($field_type->get_primitive_type() == 'term') {

            if( is_numeric( $field_type->get_option('taxonomy_id') ) ){
                $taxonomy = new Entities\Taxonomy( $field_type->get_option('taxonomy_id') );
                if( $taxonomy ){
                    $taxonomy_slug = $taxonomy->get_db_identifier();
                } else {
                    return null;
                }
            } else {
                return null;
            }

			$terms = wp_get_object_terms($item_metadata->get_item()->get_id(), $taxonomy_slug );
			
			if ($unique)
				$terms = reset($terms);
			
			return $terms;
		
		} elseif ($field_type->get_primitive_type() == 'compound') {
			
			global $wpdb;
			$rows = $wpdb->get_results( 
				$wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $item_metadata->get_item()->get_id(), $item_metadata->field->get_id()), 
				ARRAY_A );
			
			$return_value = [];
			
			if (is_array($rows)) {
				
				foreach ($rows as $row) {
					$value = $this->extract_compound_value(maybe_unserialize($row['meta_value']), $item_metadata->get_item(), $row['meta_id']);
					if ( $unique ) {
						$return_value = $value;
						break;
					} else {
						$return_value[] = $value;
					}
				}
				
			}
			
			return $return_value; 
			
        } else {
            if (is_int($item_metadata->get_meta_id())) {
				$value = get_metadata_by_mid( 'post', $item_metadata->get_meta_id() );
				if ( is_object($value) && isset( $value->meta_value) ) {
					return $value->meta_value;
				}
			} else {
				return get_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), $unique);
			}
			
        }
        
    }
	
	/**
	 * Transforms the array saved as meta_value with the IDs of post_meta saved as a value for compound fields
	 * and converts it into an array of Item Metadatada Entitites
	 *
	 * @param array $ids The array of post_meta ids
	 * @param Entities\Item $item The item this post_meta is related to
	 * @param int $compund_meta_id the meta_id of the parent compound metadata
	 * @return array An array of Item_Metadata_Entity objects
	 */
	private function extract_compound_value(array $ids, Entities\Item $item, $compund_meta_id) {
		
		$return_value = [];
		
		if (is_array($ids)) { 
			foreach ($ids as $id) {
				$post_meta_object = get_metadata_by_mid( 'post', $id );
				if ( is_object($post_meta_object) ) {
					$field = new Entities\Field($post_meta_object->meta_key);
					$return_value[$field->get_id()] = new Entities\Item_Metadata_Entity( $item, $field, $id, $compund_meta_id );
				}
				
			}
		}
		
		return $return_value;
		
	}

    public function register_post_type() { }
    
    public function get_map() { return []; }
    public function get_default_properties($map) { return []; }

	/**
	 * @param $object
	 *
	 * @return mixed
	 */
	public function update( $object, $new_values = null ) {
		return $this->insert($object);
	}

    /**
     * Suggest a value to be inserted as a item Field value, return a pending log  
     * @param Entities\Item_Metadata_Entity $item_metadata
     * @return Entities\Log
     */        
	public function suggest($item_metadata) {
		return Entities\Log::create(false, '', $item_metadata, null, 'pending');
	}
}
