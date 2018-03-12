<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Item_Metadata extends Repository {
	public $entities_type = '\Tainacan\Entities\Item_Metadata_Entity';
    
    public function insert($item_metadata) {

        $unique = !$item_metadata->is_multiple();
		
		$field_type = $item_metadata->get_field()->get_field_type_object();
		if ($field_type->core) {
			$this->save_core_field_value($item_metadata);
		} elseif ($field_type->get_primitive_type() == 'term') {
			$this->save_terms_field_value($item_metadata);
		} else {
			if ($unique) {
	            update_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash( $item_metadata->get_value() ) );
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

        return new Entities\Item_Metadata_Entity($item_metadata->get_item(), $item_metadata->get_field());
    }

	/**
	 * @param $item_metadata
	 *
	 * @return mixed|void
	 */
	public function delete($item_metadata){}

    public function save_core_field_value(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        $field_type = $item_metadata->get_field()->get_field_type_object();
        if ($field_type->core) {
            $item = $item_metadata->get_item();
            $set_method = 'set_' . $field_type->related_mapped_prop;
            $value = $item_metadata->get_value();
            $item->$set_method( is_array( $value ) ? $value[0] : $value );
            if ($item->validate_core_fields()) {
                global $Tainacan_Items;
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
	 * Fetch Item Field objects related to an Item
	 *
	 * @param Entities\Item $object
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function fetch($object, $output = null ){
        if($object instanceof Entities\Item){
            global $Tainacan_Items, $Tainacan_Fields;
            
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
        if ($field_type->core) {
            $item = $item_metadata->get_item();
            
            $get_method = 'get_' . $field_type->related_mapped_prop;
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
			
        } else {
            return get_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), $unique);
        }
        
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
