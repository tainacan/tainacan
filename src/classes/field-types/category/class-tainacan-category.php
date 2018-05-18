<?php

namespace Tainacan\Field_Types;

use Tainacan\Entities\Field;
use Tainacan\Entities\Item_Metadata_Entity;
use Tainacan\Repositories\Fields;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Category extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        $this->set_primitive_type('term');
        
        $this->set_default_options([
            'allow_new_terms' => false
        ]);

        $this->set_form_component('tainacan-form-category');
        $this->set_component('tainacan-category');
    }

    /**
     * @inheritdoc
     */
    public function get_form_labels(){
        return [
            'taxonomy_id' => [
                'title' => __( 'Collection Related', 'tainacan' ),
                'description' => __( 'Select the collection to fetch items', 'tainacan' ),
            ],
            'input_type' => [
                'title' => __( 'Input type', 'tainacan' ),
                'description' => __( 'The html type of the terms list ', 'tainacan' ),
            ],
            'allow_new_terms' => [
                'title' => __( 'Allow new terms', 'tainacan' ),
                'description' => __( 'Allows to create new terms', 'tainacan' ),
            ]
        ];
    }

    /**
     * @param $itemMetadata Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        $options = ( isset( $this->get_options()['options'] ) ) ? $this->get_options()['options'] : '';
        return '<tainacan-selectbox    
                                       options="' . $options . '"
                                       field_id ="'.$itemMetadata->get_field()->get_id().'" 
                                       item_id="'.$itemMetadata->get_item()->get_id().'"    
                                       value=\''.json_encode( $itemMetadata->get_value() ).'\'
                                       name="'.$itemMetadata->get_field()->get_name().'"></tainacan-selectbox>';
    }
	
	public function validate_options( Field $field) {
		
		if ( !in_array($field->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
		if (empty($this->get_option('taxonomy_id')))
			return ['taxonomy_id' => __('Please select a taxonomy', 'tainacan')];
		
		$Tainacan_Fields = Fields::get_instance();
		
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
                    return ['taxonomy_id' => __('You can not have 2 taxonomy metadata using the same taxonomy in a collection.', 'tainacan')];
                }
		    }
        }
		
		return true;
		
	}

	/**
	 * Validate item based on field type categories options
	 *
	 * @param Item_Metadata_Entity $item_metadata
	 *
	 * @return bool Valid or not
	 */
    public function validate( Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();

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
				if (is_object($term) && $term instanceof \Tainacan\Entities\Term) {
					$term = $term->get_id();
				}
				
				if (!term_exists($term)) {
					$valid = false;
					break;
				}
			}
			
		}
		
		return $valid;
        
    }
	
	/**
	 * Return the value of an Item_Metadata_Entity using a field of this field type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, containing one or multiple terms, separated by comma, linked to term page
	 */
	public function get_value_as_html(Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		
		$return = '';
		
		if ( $item_metadata->is_multiple() ) {
			
			$count = 1;
			$total = sizeof($value);
			
			foreach ( $value as $term ) {
				if ( $term instanceof \Tainacan\Entities\Term ) {
					$return .= $term->__toHtml();
				}
				
				$count ++;
				
				if ( $count <= $total ) {
					$return .= ', ';
				}
				
			}
			
		} else {
			
			if ( $value instanceof \Tainacan\Entities\Term ) {
				$return .= $value->__toHtml();
			}
			
		}
		
		return $return;
		
	}
	
}