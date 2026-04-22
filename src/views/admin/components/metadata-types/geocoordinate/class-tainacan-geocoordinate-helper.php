<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class GeoCoordinate_Helper {
	use \Tainacan\Traits\Singleton_Instance;
	static $posts_join= false;
	static $filter_where= false;

	function init() {
		$this->init_filters();
	}

	function init_filters() {
		add_filter('posts_join', array( &$this, 'posts_join' ), 10, 2);
		add_filter('posts_where', array( &$this, 'posts_where' ), 10, 2);
	}

	// join for relationship metadata
	function posts_join( $join, $wp_query ) {
		global $wpdb;
		$args = $wp_query->query;
		if ($this::$posts_join || !isset($args['geoquery'])) { 
			return $join;
		}
		$this::$posts_join = true;
		$join .= " INNER JOIN $wpdb->postmeta AS mtgeoquery ON ( wp_posts.ID = mtgeoquery.post_id ) ";
		remove_filter('posts_join', [$this, 'relationships_join']);
		return $join;
	}

	public function posts_where($where, $wp_query) {
		global $wpdb;
		$args = $wp_query->query;

		if ($this::$filter_where || !isset($args['geoquery'])) {
			return $where;
		}
		$this::$filter_where = true;
		$geoquery = $args['geoquery'];
		$where_geo = [];
		foreach($geoquery as $params) {
			switch ($params['compare']) {
				case 'DISTANCE':
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
					$safe_polygon = implode(', ', $safe_pairs);
					$where_geo[] = $wpdb->prepare(
						"(mtgeoquery.meta_key = %s AND ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((" . $safe_polygon . "))'), point(SUBSTRING_INDEX(wp.meta_value, ',', 1), SUBSTRING_INDEX(wp.meta_value, ',', -1))) = true)",
						$key
					);
					break;
			}
		}

		if ( empty($where_geo) ) {
			return $where;
		}

		$where_geo = trim(implode(" AND ", $where_geo));
		return "$where AND $where_geo";
	}
}
GeoCoordinate_Helper::get_instance();