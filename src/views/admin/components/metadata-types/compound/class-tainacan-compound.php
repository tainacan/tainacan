<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;
use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Compound extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('compound');
        $this->set_component('tainacan-compound');
    }

    /**
     * generate the metadata for this metadatum type
     */
    public function form(){

    }
	
		
	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, each HTML representation of the value of each metadatum composing this metadata
	 */
	public function get_value_as_html(Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		
		$return = '';
		
		if ( $item_metadata->is_multiple() ) {
			
			$return .= '<div class="tainacan-compund-group">';
			
			foreach ( $value as $compound_element ) {
				
				$return .= '<div class="tainacan-compund-metadatum">';
				
				foreach ( $compound_element as $meta ) {
					if ( $meta instanceof Item_Metadata_Entity ) {
						$return .= '<h4>' . $meta->get_metadatum()->get_name() . "</h4>\n";
						$return .= '<p>' . $meta->get_value_as_html() . '</p>' . "\n\n";
					}
				}
				
				$return .= '</div>' . "\n\n";
				
			}
			
			$return .= '</div>' . "\n\n";
			
			
		} else {
			
			foreach ( $value as $meta ) {
				
				$return .= '<div class="tainacan-compund-metadatum">';
				
				if ( $meta instanceof Item_Metadata_Entity ) {
					$return .= '<h4>' . $meta->get_metadatum()->get_name() . "</h4>\n";
					$return .= '<p>' . $meta->get_value_as_html() . '</p>';
				}
				
				$return .= '</div>' . "\n\n";	
				
			}
			
		}
		
		return $return;
		
	}
	

    
}