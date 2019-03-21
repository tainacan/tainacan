<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Core_Description extends Metadata_Type {
    use \Tainacan\Traits\Formatter_Text;
    
    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('string');
        $this->set_core(true);
        $this->set_related_mapped_prop('description');
        $this->set_component('tainacan-textarea');
        $this->set_name( __('Core Description', 'tainacan') );
        $this->set_description( __('The "Core Description" is a compulsory metadata automatically created for all collections by default. This is the main description displayed in items lists and where the basic research tools will do their searches.', 'tainacan') );
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

     public function render( $itemMetadata ){
         return '<tainacan-textarea 
                         id="tainacan-textarea-' . $itemMetadata->get_item()->WP_Post->post_name . '"
                         metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                         item_id="'.$itemMetadata->get_item()->get_id().'"    
                         value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                         name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-textarea>';
     }

    /**
     * generate the metadata for this metadatum type
     */
    public function form(){

    }
    
    /**
     * Description core Metadatum type is stored as the item content (description)
     *
     * Lets validate it as the item description
     *
     * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
     * @return bool Valid or not
     *
     * Quarantine - Core metadata should be validated as any other metadata
     * and item title is no longer mandatory
    
    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();
        
        if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
        
        $item->set_description($item_metadata->get_value());
        
        return $item->validate_prop('description');
        
    }
	
	*/

    public function validate_options( Metadatum $metadatum ) {
		
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
        if ( $metadatum->get_multiple() != 'no') {
            return ['multiple' => __('Core Metadata can not accept multiple values', 'tainacan')];
        }
		
		return true;
		
    }
    
    /**
	 * Get the value as a HTML string with links and breakline tag.
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$total = sizeof($value);
			$count = 0;
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			foreach ( $value as $el ) {
				$return .= $prefix;
				$return .= nl2br($this->make_clickable_links($el));
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
		} else {
			$return = nl2br($this->make_clickable_links($value));
		}
		return $return;
	}
    
}