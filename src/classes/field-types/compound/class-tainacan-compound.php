<?php

namespace Tainacan\Field_Types;

use Tainacan\Entities\Field;
use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Compound extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        $this->set_primitive_type('compound');
        $this->set_component('tainacan-compound');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-text 
                               id="tainacan-text-' . $itemMetadata->get_item()->WP_Post->post_name . '"
                               field_id ="'.$itemMetadata->get_field()->get_id().'" 
                               item_id="'.$itemMetadata->get_item()->get_id().'"    
                               value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                               name="'.$itemMetadata->get_field()->get_name().'"></tainacan-text>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){

    }
	
		
	/**
	 * Return the value of an Item_Metadata_Entity using a field of this field type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, each HTML representation of the value of each field composing this metadata
	 */
	public function get_value_as_html(Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		
		$return = '';
		
		if ( $item_metadata->is_multiple() ) {
			
			$return .= '<div class="tainacan-compund-group">';
			
			foreach ( $value as $compound_element ) {
				
				$return .= '<div class="tainacan-compund-field">';
				
				foreach ( $compound_element as $meta ) {
					if ( $meta instanceof Item_Metadata_Entity ) {
						$return .= '<h4>' . $meta->get_field()->get_name() . "</h4>\n";
						$return .= '<p>' . $meta->get_value_as_html() . '</p>' . "\n\n";
					}
				}
				
				$return .= '</div>' . "\n\n";
				
			}
			
			$return .= '</div>' . "\n\n";
			
			
		} else {
			
			foreach ( $value as $meta ) {
				
				$return .= '<div class="tainacan-compund-field">';
				
				if ( $meta instanceof Item_Metadata_Entity ) {
					$return .= '<h4>' . $meta->get_field()->get_name() . "</h4>\n";
					$return .= '<p>' . $meta->get_value_as_html() . '</p>';
				}
				
				$return .= '</div>' . "\n\n";	
				
			}
			
		}
		
		return $return;
		
	}
	

    
}