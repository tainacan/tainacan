<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Text extends Metadata_Type {
	use \Tainacan\Traits\Formatter_Text;

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        parent::set_primitive_type('string');
        parent::set_component('tainacan-text');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-text metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                               item_id="'.$itemMetadata->get_item()->get_id().'"    
                               value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                               name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-text>';
	}
	
	/**
	 * Get the value as a HTML string with links
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$total = sizeof($value);
			$count = 0;
			foreach ( $value as $el ) {
				$return .= $this->make_clickable_links($el);
				$count ++;
				if ($count <= $total)
					$return .= ', ';
			}
		} else {
			$return = $this->make_clickable_links($value);
		}
		return $return;
	}
}