<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Category extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('term');
        
        $this->set_default_options([
            'allow_new_terms' => false
        ]);

        $this->form_component = 'tainacan-form-category';
        // TODO: Set component depending on options. If multiple is checkbox. if single, select. etc.
        $this->component = 'tainacan-category';
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        $options = ( isset( $this->options['options'] ) ) ? $this->options['options'] : '';
        return '<tainacan-selectbox    
                                       options="' . $options . '"
                                       field_id ="'.$itemMetadata->get_field()->get_id().'" 
                                       item_id="'.$itemMetadata->get_item()->get_id().'"    
                                       value=\''.json_encode( $itemMetadata->get_value() ).'\'
                                       name="'.$itemMetadata->get_field()->get_name().'"></tainacan-selectbox>';
    }
	
	public function validate_options(\Tainacan\Entities\Field $field) {
		
		if ( !in_array($field->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
		if (empty($this->get_option('taxonomy_id')))
			return ['taxonomy_id' => __('Please select a category', 'tainacan')];
		
		global $Tainacan_Fields;
		
		$category_fields = $Tainacan_Fields->fetch([
			'collection_id' => $field->get_collection_id(),
			'field_type' => 'Tainacan\\Field_Types\\Category'
		], 'OBJECT');
		
		$category_fields = array_map(function ($field_map) {
			$fto = $field_map->get_field_type_object();
			return [ $field_map->get_id() => $fto->get_option('taxonomy_id') ];
		}, $category_fields);

		if( is_array( $category_fields ) ){
            foreach ($category_fields as $field_id => $category_field) {
                if ( is_array( $category_field ) && key($category_field) != $field->get_id()
                    && in_array($this->get_option('taxonomy_id'), $category_field)) {
                    return ['taxonomy_id' => __('You can not have 2 Category Fields using the same category in a collection', 'tainacan')];
                }
		    }
        }
		
		return true;
		
	}
	
	/**
     * Validate item based on field type categories options
     *
     * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
     * @return bool Valid or not
     */
    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();
        $field = $item_metadata->get_field();
		
        if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

		$valid = true;
        
        if (false === $this->get_option('allow_new_terms')) {
			$terms = $item_metadata->get_value();
			
			if (false === $terms)
				return true;
			
			if (!is_array($terms))
				$terms = array($terms);
			
			foreach ($terms as $term) {
				if (is_object($term) && $term instanceof \WP_Term) {
					$term = $term->term_id;
				}
				
				if (!term_exists($term)) {
					$valid = false;
					break;
				}
			}
			
		}
		
		return $valid;
        
    }
	
}