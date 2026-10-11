<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class GeoCoordinate_Helper {
	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Alias given to the postmeta table joined for geo queries.
	 * Both posts_join() and posts_where() must read from this alias.
	 */
	const META_ALIAS = 'mtgeoquery';

	function init() {
		$this->init_filters();
	}

	function init_filters() {
		add_filter('posts_join', array( &$this, 'posts_join' ), 10, 2);
		add_filter('posts_where', array( &$this, 'posts_where' ), 10, 2);
	}

	// join for geocoordinate metadata
	function posts_join( $join, $wp_query ) {
		global $wpdb;
		$args = $wp_query->query;
		// Idempotent per query: skip if this query's JOIN clause already has the alias.
		// (A static flag here would silently disable geo queries after the first one in the same request.)
		if ( !isset($args['geoquery']) || false !== strpos($join, ' AS ' . self::META_ALIAS . ' ') ) {
			return $join;
		}
		// Only join when posts_where() will actually add a clause. An unconstrained postmeta
		// join would otherwise return one row per meta entry of each item.
		if ( empty( $this->build_geo_clauses( $args['geoquery'] ) ) ) {
			return $join;
		}
		$join .= " INNER JOIN {$wpdb->postmeta} AS " . self::META_ALIAS . " ON ( {$wpdb->posts}.ID = " . self::META_ALIAS . ".post_id ) ";
		return $join;
	}

	public function posts_where($where, $wp_query) {
		$args = $wp_query->query;

		if ( !isset($args['geoquery']) || false !== strpos($where, self::META_ALIAS . '.meta_key') ) {
			return $where;
		}
		$where_geo = $this->build_geo_clauses( $args['geoquery'] );
		if ( empty( $where_geo ) ) {
			return $where;
		}
		return "$where AND " . trim( implode( ' AND ', $where_geo ) );
	}

	/**
	 * Builds one prepared WHERE fragment per valid `geoquery` entry.
	 * Entries with missing keys or non-numeric coordinates are skipped.
	 *
	 * @param mixed $geoquery Raw `geoquery` argument: array of ['compare' => 'DISTANCE'|'CONTAINS', 'key' => metadatum ID, ...].
	 * @return string[] Prepared SQL fragments; empty when nothing valid was given.
	 */
	private function build_geo_clauses( $geoquery ) {
		global $wpdb;
		$where_geo = [];
		if ( !is_array($geoquery) ) {
			return $where_geo;
		}
		foreach($geoquery as $params) {
			if ( !is_array($params) || !isset($params['compare'], $params['key']) ) {
				continue;
			}
			switch ($params['compare']) {
				case 'DISTANCE':
					if ( !isset($params['point'], $params['distance']) ) {
						break;
					}
					$point_parts = explode(',', $params['point'], 2);
					if ( count($point_parts) !== 2 || !is_numeric(trim($point_parts[0])) || !is_numeric(trim($point_parts[1])) ) {
						break;
					}
					$lat      = floatval(trim($point_parts[0]));
					$lng      = floatval(trim($point_parts[1]));
					$distance = floatval($params['distance']);
					$key      = $params['key'];
					$where_geo[] = $wpdb->prepare(
						"(mtgeoquery.meta_key = %s AND ST_Distance_Sphere(point(SUBSTRING_INDEX(mtgeoquery.meta_value, ',', 1), SUBSTRING_INDEX(mtgeoquery.meta_value, ',', -1)), point(%f, %f)) <= %f)",
						$key, $lat, $lng, $distance
					);
					break;
				case 'CONTAINS':
					if ( !isset($params['polygon']) ) {
						break;
					}
					$polygon = $params['polygon'];
					$key     = $params['key'];
					$pairs   = array_map('trim', explode(',', $polygon));
					$safe_pairs = [];
					$valid = true;
					foreach ($pairs as $pair) {
						$coords = preg_split('/\s+/', trim($pair), 2);
						if ( count($coords) !== 2 || !is_numeric($coords[0]) || !is_numeric($coords[1]) ) {
							$valid = false;
							break;
						}
						$safe_pairs[] = floatval($coords[0]) . ' ' . floatval($coords[1]);
					}
					if ( !$valid || empty($safe_pairs) ) {
						break;
					}
					$safe_polygon = 'POLYGON((' . implode( ', ', $safe_pairs ) . '))';
					// The stored point lives in the joined postmeta alias, same as the DISTANCE branch.
					// Reading it from "wp.meta_value" referenced a table that is not part of the statement (#1221).
					$where_geo[] = $wpdb->prepare(
						"(mtgeoquery.meta_key = %s AND ST_CONTAINS(ST_GEOMFROMTEXT(%s), point(SUBSTRING_INDEX(mtgeoquery.meta_value, ',', 1), SUBSTRING_INDEX(mtgeoquery.meta_value, ',', -1))) = true)",
						$key,
						$safe_polygon
					);
					break;
			}
		}

		return $where_geo;
	}
}
GeoCoordinate_Helper::get_instance();