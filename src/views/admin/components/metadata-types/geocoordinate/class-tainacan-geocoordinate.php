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
		$this->set_sortable( false );
		$this->set_default_options([
			'map_provider' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
			'attribution' => '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors',
			'initial_zoom' => 5,
			'maximum_zoom' => 12,
		]);
		$this->set_preview_template('
			<div>
				<div class="control">
					<img src="' . plugin_dir_url( __FILE__ ) . '/../../../../../../assets/images/geocoordinate_preview.png" alt="' . __('Image of a marker in a map.' , 'tainacan' ) . '" />
				</div>
			</div>
		');
	}

	/**
	 * @inheritdoc
	*/
	public function get_form_labels(){
		return [
			'map_provider' => [
				'title' => __( 'Map Tiles provider', 'tainacan' ),
				'description' => __( 'Link to the service used as source for displaying tile layers on the map.', 'tainacan' ),
			],
			'attribution' => [
				'title' => __( 'Attribution', 'tainacan' ),
				'description' => __( 'Text/HTML to be shown in the attribution control, e.g. "© OpenStreetMap contributors". It describes source of map data and is often a legal obligation towards copyright holders and tile providers.', 'tainacan' ),
			],
			'initial_zoom' => [
				'title' => __( 'Initial zoom', 'tainacan' ),
				'description' => __( 'Initial zoom level of the map.', 'tainacan' ),
			],
			'maximum_zoom' => [
				'title' => __( 'Maximum zoom', 'tainacan' ),
				'description' => __( 'Maximum zoom level of the map.', 'tainacan' ),
			]
		];
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
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as a string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The String representation of the value, containing one or multiple items names, linked to the item page
	 */
	public function get_value_as_string(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();

		$return = '';
		if ( $item_metadata->is_multiple() ) {
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			$total = count($value);
			$count = 0;
			foreach ($value as $v) {
				$return .= $prefix;
				$return .= (string) $v;
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}
		} else {
			$return = (string) $value;
		}
		return $return;
	}

	
	/**
	 * Get the value as a HTML string with proper date format set in admin
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		global $TAINACAN_BASE_URL;
		$value = $item_metadata->get_value();
		$options = $this->get_options();

		if ( 
			( is_string( $value ) && empty( $value ) ) ||
			( is_array( $value ) && !count( $value ) )
		)
			return '';

		$metadatum = $item_metadata->get_metadatum();
		$item_metadatum_id = $metadatum->get_id();
		$item_metadatum_id .= ( $metadatum->get_parent() && $item_metadata->get_parent_meta_id() ) ? ( '_parent_meta_id-' . $item_metadata->get_parent_meta_id() ) : '';
		$zoom_geo_query = isset($options['initial_zoom']) ? ('z=' . $options['initial_zoom'] ) : '' ;

		$return = '';

		if ( $item_metadata->is_multiple() ) {
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			
			foreach ( $value as $coordinate ) {

				$coordinate_as_array = explode(",", $coordinate);
				$latitude = isset($coordinate_as_array[0]) ? $coordinate_as_array[0] : '';
				$longitude = isset($coordinate_as_array[1]) ? $coordinate_as_array[1] : '';

				$single_value = "<a class='tainacan-coordinates' data-latitude='{$latitude}' data-longitude='{$longitude}' href='geo:{$latitude},{$longitude}?q={$latitude},{$longitude}&{$zoom_geo_query}'>
									<span>{$latitude}</span>
									<span class='coordinates-separator'>,</span>
									<span>{$longitude}</span>
								</a>";
				$return .= empty($return)
					? $prefix . $single_value . $suffix
					: $separator . $prefix . $single_value . $suffix;
			}

		} else {
			$coordinate_as_array = explode(",", $value);
			$latitude = isset($coordinate_as_array[0]) ? $coordinate_as_array[0] : '';
			$longitude = isset($coordinate_as_array[1]) ? $coordinate_as_array[1] : '';

			$return .= "<a class='tainacan-coordinates' data-latitude='{$latitude}' data-longitude='{$longitude}' href='geo:{$latitude},{$longitude}?q={$latitude},{$longitude}&{$zoom_geo_query}'>
							<span>{$latitude}</span>
							<span class='coordinates-separator'>,</span>
							<span>{$longitude}</span>
						</a>";
		}
		
		wp_enqueue_style( 'tainacan-geocoordinate-item-metadatum', $TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-geocoordinate-item-metadatum.css', array(), TAINACAN_VERSION);

		$options_as_strings = '';
		foreach ( $options as $option_key => $option ) {
			if ( is_array($option) )
				$options_as_strings .= 'data-' . $option_key . '="' . json_encode($option) . '" ';
			else if ( $option_key == 'attribution' )
				$options_as_strings .= 'data-' . $option_key . '="' . htmlentities($option) . '" ';
			else
				$options_as_strings .= 'data-' . $option_key . '="' . $option . '" ';
		};

		return '<span id="tainacan-geocoordinatemetadatum--' . $item_metadatum_id . '" data-module="geocoordinate-item-metadatum" ' . $options_as_strings . '>
					' . $return . '
				</span>';
	}

	public function get_options_as_html() {
		return "";
	}
}