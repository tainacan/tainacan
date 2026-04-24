<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Item_Metadata_Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class GeoJSON
 */
class GeoJSON extends Metadata_Type {

	function __construct() {
		parent::__construct();
		$this->set_primitive_type( 'string' );
		$this->set_component( 'tainacan-geojson' );
		$this->set_form_component( 'tainacan-form-geojson' );
		$this->set_name( __( 'GeoJSON', 'tainacan' ) );
		$this->set_description( __( 'Represents geographical geometries using GeoJSON data, including points, lines and polygons.', 'tainacan' ) );
		$this->set_sortable( false );
		$this->set_default_options( [
			'map_provider' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
			'attribution' => '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors',
			'initial_zoom' => 5,
			'maximum_zoom' => 12,
			'initial_latitude' => -14.4086569,
			'initial_longitude' => -51.31668
		] );
		$this->set_preview_template( '
			<div>
				<div class="control">
					<img src="' . plugin_dir_url( __FILE__ ) . '/../../../../../../assets/images/geocoordinate_preview.png" alt="' . __( 'Image of geometries in a map.', 'tainacan' ) . '" />
				</div>
			</div>
		' );
	}

	public function get_form_labels() {
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
			],
			'initial_position' => [
				'title' => __( 'Initial center position', 'tainacan' ),
				'description' => __( 'Define latitude and longitude for the initial center of the map input.', 'tainacan' ),
			]
		];
	}

	private function validate_lat_long( $lat, $long ) {
		if ( !is_numeric( $lat ) || !is_numeric( $long ) ) {
			return false;
		}

		$valid_lat = ( $lat + 0 ) >= -90.0 && ( $lat + 0 ) <= 90.0;
		$valid_long = ( $long + 0 ) >= -180.0 && ( $long + 0 ) <= 180.0;

		return $valid_lat & $valid_long;
	}

	private function allowed_geometry_types() {
		return [ 'Point', 'LineString', 'Polygon', 'MultiPoint', 'MultiLineString', 'MultiPolygon' ];
	}

	private function is_valid_geometry_object( $geometry ) {
		if ( !is_array( $geometry ) ) {
			return false;
		}

		if ( empty( $geometry['type'] ) || !is_string( $geometry['type'] ) ) {
			return false;
		}

		if ( !in_array( $geometry['type'], $this->allowed_geometry_types(), true ) ) {
			return false;
		}

		return !empty( $geometry['coordinates'] );
	}

	private function normalize_geojson_to_feature_collection( $raw_value ) {
		if ( !is_string( $raw_value ) || trim( $raw_value ) === '' ) {
			return null;
		}

		$decoded = json_decode( $raw_value, true );
		if ( !is_array( $decoded ) || empty( $decoded['type'] ) ) {
			return false;
		}

		if ( $decoded['type'] === 'FeatureCollection' ) {
			$features = isset( $decoded['features'] ) && is_array( $decoded['features'] ) ? $decoded['features'] : [];
			return [
				'type' => 'FeatureCollection',
				'features' => $features
			];
		}

		if ( $decoded['type'] === 'Feature' ) {
			return [
				'type' => 'FeatureCollection',
				'features' => [ $decoded ]
			];
		}

		if ( $this->is_valid_geometry_object( $decoded ) ) {
			return [
				'type' => 'FeatureCollection',
				'features' => [
					[
						'type' => 'Feature',
						'geometry' => $decoded,
						'properties' => new \stdClass()
					]
				]
			];
		}

		return false;
	}

	private function validate_feature_collection( $feature_collection ) {
		if ( !is_array( $feature_collection ) ) {
			return false;
		}

		if ( empty( $feature_collection['type'] ) || $feature_collection['type'] !== 'FeatureCollection' ) {
			return false;
		}

		$features = isset( $feature_collection['features'] ) ? $feature_collection['features'] : [];
		if ( !is_array( $features ) ) {
			return false;
		}

		foreach ( $features as $feature ) {
			if ( !is_array( $feature ) || ( $feature['type'] ?? '' ) !== 'Feature' ) {
				return false;
			}
			if ( empty( $feature['geometry'] ) || !$this->is_valid_geometry_object( $feature['geometry'] ) ) {
				return false;
			}
		}

		return true;
	}

	private function get_feature_collection_from_value( $value ) {
		$values = is_array( $value ) ? $value : [ $value ];
		$feature_collection = [
			'type' => 'FeatureCollection',
			'features' => []
		];

		foreach ( $values as $single_value ) {
			$normalized = $this->normalize_geojson_to_feature_collection( $single_value );
			if ( $normalized === false ) {
				return false;
			}
			if ( $normalized === null ) {
				continue;
			}

			if ( isset( $normalized['features'] ) && is_array( $normalized['features'] ) ) {
				$feature_collection['features'] = array_merge( $feature_collection['features'], $normalized['features'] );
			}
		}

		return $feature_collection;
	}

	public function validate( Item_Metadata_Entity $item_metadata ) {
		$value = $item_metadata->get_value();
		$feature_collection = $this->get_feature_collection_from_value( $value );

		if ( $feature_collection === false ) {
			$this->add_error( __( 'The value is not a valid GeoJSON structure.', 'tainacan' ) );
			return false;
		}

		if ( !$this->validate_feature_collection( $feature_collection ) ) {
			$this->add_error( __( 'The GeoJSON value must contain only Point, LineString or Polygon geometries (including multi geometries).', 'tainacan' ) );
			return false;
		}

		if ( !$item_metadata->is_multiple() ) {
			$features = isset( $feature_collection['features'] ) && is_array( $feature_collection['features'] ) ? $feature_collection['features'] : [];
			if ( count( $features ) > 1 ) {
				$this->add_error( __( 'This metadata does not allow multiple values, so only one geometry can be saved.', 'tainacan' ) );
				return false;
			}
		}

		return true;
	}

	public function get_value_as_string( \Tainacan\Entities\Item_Metadata_Entity $item_metadata ) {
		$feature_collection = $this->get_feature_collection_from_value( $item_metadata->get_value() );
		if ( !$feature_collection ) {
			return '';
		}

		return apply_filters(
			'tainacan-item-metadata-get-value-as-string--type-geojson',
			wp_json_encode( $feature_collection ),
			$item_metadata
		);
	}

	public function get_value_as_html( \Tainacan\Entities\Item_Metadata_Entity $item_metadata ) {
		global $TAINACAN_BASE_URL;
		$feature_collection = $this->get_feature_collection_from_value( $item_metadata->get_value() );
		if ( !$feature_collection || empty( $feature_collection['features'] ) ) {
			return '';
		}

		$options = $this->get_options();
		$metadatum = $item_metadata->get_metadatum();
		$item_metadatum_id = $metadatum->get_id();
		$item_metadatum_id .= ( $metadatum->get_parent() && $item_metadata->get_parent_meta_id() ) ? ( '_parent_meta_id-' . $item_metadata->get_parent_meta_id() ) : '';

		wp_enqueue_style( 'tainacan-geojson-item-metadatum', $TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-geojson-item-metadatum.css', array(), TAINACAN_VERSION );

		$options_as_strings = '';
		foreach ( $options as $option_key => $option ) {
			if ( is_array( $option ) ) {
				$options_as_strings .= 'data-' . esc_attr( $option_key ) . '="' . esc_attr( wp_json_encode( $option ) ) . '" ';
			} elseif ( $option_key === 'attribution' ) {
				$options_as_strings .= 'data-' . esc_attr( $option_key ) . '="' . esc_attr( $option ) . '" ';
			} else {
				$options_as_strings .= 'data-' . esc_attr( $option_key ) . '="' . esc_attr( $option ) . '" ';
			}
		}

		$geojson_as_string = wp_json_encode( $feature_collection );
		$return = '<span id="tainacan-geojsonmetadatum--' . esc_attr( $item_metadatum_id ) . '" data-module="geojson-item-metadatum" ' . $options_as_strings . '>
			<span class="tainacan-geojson-fallback-text">' . esc_html( $geojson_as_string ) . '</span>
		</span>';

		return apply_filters( 'tainacan-item-metadata-get-value-as-html--type-geojson', $return, $item_metadata );
	}

	public function get_options_as_html() {
		return '';
	}

	public function validate_options( \Tainacan\Entities\Metadatum $metadatum ) {
		if ( !in_array( $metadatum->get_status(), apply_filters( 'tainacan-status-require-validation', [ 'publish', 'future', 'private' ] ) ) ) {
			return true;
		}

		if ( !$this->validate_lat_long( $this->get_option( 'initial_latitude' ), $this->get_option( 'initial_longitude' ) ) ) {
			return [
				'initial_position' => sprintf(
					__( 'The value (%s) is not a valid geo coordinate', 'tainacan' ),
					( '' . $this->get_option( 'initial_latitude' ) . ',' . $this->get_option( 'initial_longitude' ) )
				)
			];
		}

		return true;
	}
}
