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
					$point = $params['point'];
					$distance = $params['distance'];
					$key = $params['key'];
					$where_geo[] = "
					(
						mtgeoquery.meta_key = $key
						AND
						ST_Distance_Sphere(
							point(SUBSTRING_INDEX(mtgeoquery.meta_value, ',', 1), SUBSTRING_INDEX(mtgeoquery.meta_value, ',', -1)),
							point($point)
						) <= $distance
					)
					";
					break;
				case 'CONTAINS':
					$polygon = $params['polygon'];
					$key = $params['key'];
					$where_geo[] = "(
						mtgeoquery.meta_key = $key
						AND
						ST_CONTAINS(
							ST_GEOMFROMTEXT('POLYGON(($polygon))'),
							point(SUBSTRING_INDEX(wp.meta_value, ',', 1), SUBSTRING_INDEX(wp.meta_value, ',', -1))
						) = true
					)";
					break;
			}
		}
		
		$where_geo = trim(implode(" AND ", $where_geo));
		return "$where AND $where_geo";
	}
}
GeoCoordinate_Helper::get_instance();