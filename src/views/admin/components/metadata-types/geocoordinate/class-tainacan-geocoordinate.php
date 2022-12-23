<?php

namespace Tainacan\Metadata_Types;

require_once(__DIR__ . '/class-tainacan-geocoordinate-helper.php');

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
	 * @param float|int|number $lat Latitude
	 * @param float|int|number $long Longitude
	 * @return bool `true` if the coordinate is valid, `false` if not
	 */
	private function validateLatLong($lat, $long) {
		if ( !is_numeric($lat) || !is_numeric($long) )
			return false;
			
		$validataLat = ($lat + 0) >= -90.0 && ($lat + 0) <= 90.0;
		$validataLong = ($long + 0) >= -180.0 && ($long + 0) <= 180.0;
		return $validataLat & $validataLong;
	}

	public function validate( Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$value = is_array($value) ? $value : [$value];
		foreach ($value as $coordinate) {
			$arr_coordinate = explode(",", $coordinate);
			if ( count($arr_coordinate) != 2 || !$this->validateLatLong($arr_coordinate[0], $arr_coordinate[1])) {
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
		global $TAINACAN_BASE_URL;
		$value = $item_metadata->get_value();
		$metadatum = $item_metadata->get_metadatum();
		$item_metadatum_id = $metadatum->get_id();
		$item_metadatum_id .= $metadatum->get_parent() ? ( $metadatum->get_parent() . '_parent_meta_id-') : '';
		
		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			
			foreach ( $value as $coordinate ) {

				$coordinate_as_array = explode(",", $coordinate);
				$latitude = isset($coordinate_as_array[0]) ? $coordinate_as_array[0] : '';
				$longitude = isset($coordinate_as_array[1]) ? $coordinate_as_array[1] : '';

				$single_value = "<span class='tainacan-coordinates' data-latitude='{$latitude}' data-longitude='{$longitude}'><span>{$latitude}</span><span class='coordinates-separator'>,</span><span>{$longitude}</span></span>";
				$return .= empty($return)
					? $prefix . $single_value . $suffix
					: $separator . $prefix . $single_value . $suffix;
			}

		} else {
			$coordinate_as_array = explode(",", $value);
			$latitude = isset($coordinate_as_array[0]) ? $coordinate_as_array[0] : '';
			$longitude = isset($coordinate_as_array[1]) ? $coordinate_as_array[1] : '';

			$return .= "<span class='tainacan-coordinates' data-latitude='{$latitude}' data-longitude='{$longitude}'><span>{$latitude}</span><span class='coordinates-separator'>,</span><span>{$longitude}</span></span>";
		}
		
		wp_enqueue_style( 'tainacan-geocoordinate-item-metadatum', $TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-geocoordinate-item-metadatum.css', array(), TAINACAN_VERSION);

		return '<span id="tainacan-geocoordinatemetadatum--' . $item_metadatum_id . '" data-module="geocoordinate-item-metadatum">
					' . $return . '
				</span>';
	}
}