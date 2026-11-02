<?php
/**
 * Tests for the `geoquery` WP_Query extension provided by GeoCoordinate_Helper.
 *
 * @package Test_Tainacan
 */

namespace Tainacan\Tests;

/**
 * Covers the posts_join / posts_where filters that implement the `geoquery`
 * argument accepted by the items REST endpoint (DISTANCE and CONTAINS).
 */
class GeoCoordinateMetadatumTypes extends TAINACAN_UnitTestCase {

	/**
	 * Brasília: inside POLYGON below.
	 */
	const POINT_INSIDE = '-15.75,-47.85';

	/**
	 * São Paulo: ~870 km away, outside POLYGON below.
	 */
	const POINT_OUTSIDE = '-23.55,-46.63';

	/**
	 * Closed ring around Brasília, "lat lng" pairs separated by commas
	 * (same format the REST API receives in `geoquery[0][polygon]`).
	 */
	const POLYGON = '-15.7 -47.8, -15.8 -47.8, -15.8 -47.9, -15.7 -47.9, -15.7 -47.8';

	/**
	 * Collection holding the fixtures.
	 *
	 * @var \Tainacan\Entities\Collection
	 */
	private $collection;

	/**
	 * GeoCoordinate metadatum of the collection.
	 *
	 * @var \Tainacan\Entities\Metadatum
	 */
	private $metadatum;

	/**
	 * Item whose coordinate is inside POLYGON.
	 *
	 * @var \Tainacan\Entities\Item
	 */
	private $item_inside;

	/**
	 * Item whose coordinate is outside POLYGON.
	 *
	 * @var \Tainacan\Entities\Item
	 */
	private $item_outside;

	/**
	 * Creates a collection with a GeoCoordinate metadatum and two located items.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Geo collection',
				'status' => 'publish',
			),
			true
		);

		$this->metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'location',
				'collection'    => $this->collection,
				'metadata_type' => 'Tainacan\Metadata_Types\GeoCoordinate',
				'status'        => 'publish',
			),
			true
		);

		$this->item_inside  = $this->create_item_with_point( 'Inside', self::POINT_INSIDE );
		$this->item_outside = $this->create_item_with_point( 'Outside', self::POINT_OUTSIDE );
	}

	/**
	 * Creates a published item in the fixture collection with the given coordinate.
	 *
	 * @param string $title Item title.
	 * @param string $point Coordinate as "lat,lng".
	 * @return \Tainacan\Entities\Item
	 */
	private function create_item_with_point( $title, $point ) {
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => $title,
				'collection' => $this->collection,
				'status'     => 'publish',
			),
			true
		);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $item, $this->metadatum );
		$item_metadata->set_value( $point );
		$this->assertTrue( $item_metadata->validate(), 'Fixture coordinate must be valid: ' . $point );
		\Tainacan\Repositories\Item_Metadata::get_instance()->insert( $item_metadata );

		return $item;
	}

	/**
	 * Runs an items fetch with the given geoquery and returns the matched item IDs,
	 * failing if the database reported an error (the symptom of #1221).
	 *
	 * @param array $geoquery Value for the `geoquery` fetch argument.
	 * @return int[]
	 */
	private function fetch_ids_with_geoquery( array $geoquery ) {
		global $wpdb;
		$wpdb->last_error = '';

		$items = \Tainacan\Repositories\Items::get_instance()->fetch(
			array( 'geoquery' => $geoquery ),
			$this->collection,
			'OBJECT'
		);

		$this->assertSame( '', $wpdb->last_error, 'geoquery produced a database error: ' . $wpdb->last_error );

		return array_map(
			function ( $item ) {
				return $item->get_id();
			},
			$items
		);
	}

	/**
	 * Builds a CONTAINS geoquery entry against the fixture metadatum.
	 *
	 * @param string $polygon Polygon in the REST "lat lng, lat lng, ..." format.
	 * @return array
	 */
	private function contains_query( $polygon ) {
		return array(
			array(
				'compare' => 'CONTAINS',
				'key'     => $this->metadatum->get_id(),
				'polygon' => $polygon,
			),
		);
	}

	/**
	 * Builds a DISTANCE geoquery entry against the fixture metadatum.
	 *
	 * @param string $point    Center as "lat,lng".
	 * @param int    $distance Radius in meters.
	 * @return array
	 */
	private function distance_query( $point, $distance ) {
		return array(
			array(
				'compare'  => 'DISTANCE',
				'key'      => $this->metadatum->get_id(),
				'point'    => $point,
				'distance' => $distance,
			),
		);
	}

	/**
	 * CONTAINS must read the stored point from the joined postmeta alias.
	 */
	public function test_geoquery_contains_returns_items_inside_polygon() {
		$ids = $this->fetch_ids_with_geoquery( $this->contains_query( self::POLYGON ) );

		$this->assertEquals( array( $this->item_inside->get_id() ), $ids );
	}

	/**
	 * DISTANCE keeps working after the refactor.
	 */
	public function test_geoquery_distance_returns_items_within_radius() {
		$ids = $this->fetch_ids_with_geoquery( $this->distance_query( self::POINT_OUTSIDE, 50000 ) );

		$this->assertEquals( array( $this->item_outside->get_id() ), $ids );
	}

	/**
	 * Two different geo queries in the same request must each apply their own
	 * JOIN/WHERE. A static "already applied" flag used to make every query after
	 * the first ignore the geoquery argument and return unfiltered results.
	 */
	public function test_consecutive_geoqueries_are_both_applied() {
		$first = $this->fetch_ids_with_geoquery( $this->contains_query( self::POLYGON ) );
		$this->assertEquals( array( $this->item_inside->get_id() ), $first );

		$second = $this->fetch_ids_with_geoquery( $this->distance_query( self::POINT_OUTSIDE, 50000 ) );
		$this->assertEquals( array( $this->item_outside->get_id() ), $second );
	}

	/**
	 * An invalid polygon adds no WHERE clause, so no JOIN may be added either:
	 * an unconstrained postmeta join would return one row per meta entry.
	 */
	public function test_geoquery_with_invalid_polygon_is_ignored() {
		$ids = $this->fetch_ids_with_geoquery( $this->contains_query( 'not a polygon; DROP TABLE wp_posts' ) );

		sort( $ids );
		$expected = array( $this->item_inside->get_id(), $this->item_outside->get_id() );
		sort( $expected );
		$this->assertEquals( $expected, $ids );
	}
}
