<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Item_Metadata extends Repository {
	public $entities_type = '\Tainacan\Entities\Item_Metadata_Entity';
    public function insert($item_metadata) {

        $unique = !$item_metadata->is_multiple();

        if ($unique) {
            $field_type = $item_metadata->get_field()->get_field_type_object();
            if ($field_type->core) {
                $item = $item_metadata->get_item();
                $set_method = 'set_' . $field_type->related_mapped_prop;
                $item->$set_method($item_metadata->get_value());
                if ($item->validate()) {
                    global $TainacanItems;
                    $TainacanItems->insert($item);
                } else {
                    throw new \Exception('Item metadata should be validated beforehand');
                }
            } else {
                add_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash( $item_metadata->get_value() ) );
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
        
        do_action('tainacan-insert', $item_metadata);
        do_action('tainacan-insert-Item_Metadata_Entity', $item_metadata);

        return new Entities\Item_Metadata_Entity($item_metadata->get_item(), $item_metadata->get_field());
    }

    public function update($item_metadata, $new_values = null){
	    $unique = !$item_metadata->is_multiple();

	    if ($unique) {
		    update_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash( $item_metadata->get_value() ) );
	    } else {
		    delete_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id());

		    if (is_array($item_metadata->get_value())){
			    $values = $item_metadata->get_value();

			    foreach ($values as $value){
				    update_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash( $value ));
			    }
		    }
	    }

	    do_action('tainacan-update', $item_metadata);
	    do_action('tainacan-update-Item_Metadata_Entity', $item_metadata);

	    return new Entities\Item_Metadata_Entity($item_metadata->get_item(), $item_metadata->get_field());
    }

	/**
	 * Delete Item Field
	 *
	 * @param $item_metadata
	 *
	 * @return mixed|void
	 */
	public function delete($item_metadata){
//    	if(is_array($item_metadata->get_value())){
//    		$values = $item_metadata->get_value();
//
//    		foreach ($values as $value){
//    			delete_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash($value));
//		    }
//	    } else {
//    		delete_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), wp_slash($item_metadata->get_value()));
//	    }
    }

    /**
     * Fetch Item Field objects related to an Item
     *
     * @param Entities\Item $object
     * @return array
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

        return get_post_meta($item_metadata->item->get_id(), $item_metadata->field->get_id(), $unique);
    }

    public function register_post_type() { }
    
    public function get_map() { return []; }
    public function get_default_properties($map) { return []; }
    
}