<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Textarea extends Metadata_Type {
	use \Tainacan\Traits\Formatter_Text;

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('long_string');
        $this->set_component('tainacan-textarea');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ) {
        return '<tainacan-textarea 
                        metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                        item_id="'.$itemMetadata->get_item()->get_id().'"    
                        value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                        name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-textarea>';
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
			foreach ( $value as $el ) {
				$return .= nl2br($this->make_clickable_links($el));
				$count ++;
				if ($count <= $total)
					$return .= ', ';
			}
		} else {
			$return = nl2br($this->make_clickable_links($value));
		}
		return $return;
	}
}