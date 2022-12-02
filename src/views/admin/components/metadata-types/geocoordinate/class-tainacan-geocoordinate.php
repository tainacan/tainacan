<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class GeoCoordinate
 */
class GeoCoordinate extends Metadata_Type {

	function __construct() {
		// call metadatum type constructor
		parent::__construct();
		$this->set_primitive_type('geo');
		$this->set_component('tainacan-geocoordinate');
		$this->set_form_component('tainacan-form-geocoordinate');
		$this->set_name( __('GeoCoordinate', 'tainacan') );
		$this->set_description( __('Represents a geographical location that is determined by latitude and longitude coordinates.', 'tainacan') );
		$this->set_preview_template('
			<div>
				<div class="control">
					!!POINT IN MAP!!
				</div>
			</div>
		');
	}

	/**
	 * Validates a given coordinate
	 *
	 * @param float|int|string $lat Latitude
	 * @param float|int|string $long Longitude
	 * @return bool `true` if the coordinate is valid, `false` if not
	 */
	private function validateLatLong($lat, $long) {
		return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $lat.','.$long);
	}

	public function validate( Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$value = is_array($value) ? $value : [$value];
		foreach ($value as $coordinate) {
			$arr_coordinate = explode(",", $coordinate);
			if( count($arr_coordinate) != 2 || !$this->validateLatLong($arr_coordinate[0], $arr_coordinate[1])) {
				$this->add_error( sprintf(__('The value (%s) is not a valid geo coordinate', 'tainacan'), $coordinate ) );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Get the value as a HTML string with proper date format set in admin
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$return = is_array($value) ? implode(" - ", $value) : $value;
		return $return;
	}
}