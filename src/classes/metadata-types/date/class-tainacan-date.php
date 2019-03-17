<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Helpers;
/**
 * Class TainacanMetadatumType
 */
class Date extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('date');
        $this->set_component('tainacan-date');
        $this->set_name( __('Date', 'tainacan') );
        $this->set_description( __('Exact date type, with day, month and year.', 'tainacan') );
        $this->set_preview_template('
            <div>
                <div class="control is-inline">
                    <input type="text" placeholder="' . __('mm/dd/yyyy') . '" class="input"></input>
                </div>
            </div>
        ');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-date metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                               item_id="'.$itemMetadata->get_item()->get_id().'"    
                               value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                               name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-date>';
    }


    public function validate( Item_Metadata_Entity $item_metadata) {
        $value = $item_metadata->get_value();
        $format = 'Y-m-d';

        if (is_array($value)) {
            foreach ($value as $date_value) {
                $d = \DateTime::createFromFormat($format, $date_value);
                if (!$d || $d->format($format) !== $date_value) {
                    $this->add_error( 
                        sprintf(
                            __('Invalid date format. Expected format is YYYY-MM-DD, got %s.', 'tainacan'),
                            $date_value
                        )
                    );
                    return false;
                }
            }
            return True;
        }
        
        $d = \DateTime::createFromFormat($format, $value);
        
        if (!$d || $d->format($format) !== $value) {
            $this->add_error( 
                sprintf(
                    __('Invalid date format. Expected format is YYYY-MM-DD, got %s.', 'tainacan'),
                    $value
                )
            );
            return false;
        }
        return true;
        
    }
	
	/**
	 * Get the value as a HTML string with proper date format set in admin
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
				$return .= mysql2date(get_option('date_format'), ($el));
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
		} else {
			$return = mysql2date(get_option('date_format'), ($value));
		}
		return $return;
		
	}


}