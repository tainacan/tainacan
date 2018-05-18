<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Relationship extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        $this->set_primitive_type('item');
        $this->set_component('tainacan-relationship');
        $this->set_form_component('tainacan-form-relationship');
    }

    /**
     * @inheritdoc
     */
    public function get_form_labels(){
       return [
           'collection_id' => [
               'title' => __( 'Collection Related', 'tainacan' ),
               'description' => __( 'Select the collection to fetch items', 'tainacan' ),
           ],
           'search' => [
               'title' => __( 'Metadata for search', 'tainacan' ),
               'description' => __( 'Select the metadata to help the search', 'tainacan' ),
           ],
           'repeated' => [
               'title' =>__( 'Allow repeated items', 'tainacan' ),
               'description' => __( 'Allows different items to be related to the same item selected in another collection.', 'tainacan' ),
           ]
       ];
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-relationship 
                            collection_id="' . $this->get_options()['collection_id'] . '"
                            field_id ="'.$itemMetadata->get_field()->get_id().'" 
                            item_id="'.$itemMetadata->get_item()->get_id().'"    
                            value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                            name="'.$itemMetadata->get_field()->get_name().'"></tainacan-relationship>';
    }
    
    public function validate_options(\Tainacan\Entities\Field $field) {
        if ( !in_array($field->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        if (!empty($this->get_option('collection_id')) && !is_numeric($this->get_option('collection_id'))) {
            return [
                'collection_id' => __('Invalid collection ID','tainacan')
            ];
        } else if( empty($this->get_option('collection_id'))) {
            return [
                'collection_id' => __('The related collection is required','tainacan')
            ];
        }
        return true;
    }
	
	/**
	 * Return the value of an Item_Metadata_Entity using a field of this field type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, containing one or multiple items names, linked to the item page
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		
		$return = '';
		
		if ( $item_metadata->is_multiple() ) {
			
			$count = 1;
			$total = sizeof($value);
			
			foreach ( $value as $item_id ) {
				
				try {
					
					$item = new \Tainacan\Entities\Item($item_id);
					
					if ( $item instanceof \Tainacan\Entities\Item ) {
						$return .= $item->__toHtml();
					}
					
					$count ++;
					
					if ( $count <= $total ) {
						$return .= ', ';
					}
					
				} catch (Exception $e) {
					// item not found 
				}
				
			}
			
		} else {
			
			try {
				
				$item = new \Tainacan\Entities\Item($value);
				
				if ( $item instanceof \Tainacan\Entities\Item ) {
					$return .= $item->__toHtml();
				}
				
			} catch (Exception $e) {
				// item not found 
			}
			
		}
		
		return $return;
		
	}
	
}