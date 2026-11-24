<?php

namespace Tainacan\Tests;

/**
 * Previous and next item URLs built by Theme_Helper::get_adjacent_items().
 */
class Test_Adjacent_Items extends TAINACAN_UnitTestCase {

	/**
	 * @var string
	 */
	private $original_permalink_structure;

	public function setUp(): void {
		parent::setUp();
		$this->original_permalink_structure = get_option( 'permalink_structure' );

		// The REST controllers are assigned while the plugin file loads. The
		// test suite loads that file from a function, so the assignment never
		// becomes a global. The navigation helper reads this one.
		global $tainacan_rest_items_controller;
		if ( ! $tainacan_rest_items_controller instanceof \Tainacan\API\EndPoints\REST_Items_Controller ) {
			$tainacan_rest_items_controller = new \Tainacan\API\EndPoints\REST_Items_Controller();
		}
	}

	public function tearDown(): void {
		$this->set_permalink_structure( $this->original_permalink_structure );
		$_GET  = array();
		$_POST = array();
		parent::tearDown();
	}

	/**
	 * Plain permalinks already put the item slug in the query string.
	 * The list arguments must be added with "&", and the current item's
	 * query var must not replace the neighbor's slug.
	 */
	public function test_plain_permalinks_keep_the_neighbor_and_the_list_position() {
		$this->set_permalink_structure( '' );

		$collection = $this->create_collection( 'Plain adjacent' );
		$items      = $this->create_items( $collection, array(
			'Alpha' => 'publish',
			'Beta'  => 'publish',
			'Gamma' => 'publish',
		) );

		$this->open_item_from_list( $items['Beta'], $this->list_args( $collection->get_id(), 1 ) );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();
		$post_type = $collection->get_db_identifier();

		$this->assertSame( 'Alpha', $adjacent['previous']['title'] );
		$this->assertSame( 'Gamma', $adjacent['next']['title'] );

		$this->assert_neighbor_url( $items['Alpha'], $adjacent['previous']['url'], array(
			'pos'         => '0',
			$post_type    => $items['Alpha']->get_slug(),
			'source_list' => 'collection',
			'ref'         => "hello world's",
			'extra'       => array(
				array( 'note' => 'hello world' ),
			),
		) );
		$this->assert_neighbor_url( $items['Gamma'], $adjacent['next']['url'], array(
			'pos'         => '2',
			$post_type    => $items['Gamma']->get_slug(),
			'source_list' => 'collection',
			'ref'         => "hello world's",
			'extra'       => array(
				array( 'note' => 'hello world' ),
			),
		) );
	}

	/**
	 * Pretty permalinks have no query string. The list arguments still
	 * need a single "?", including nested values.
	 */
	public function test_pretty_permalinks_append_the_list_arguments_once() {
		$this->set_permalink_structure( '/%postname%/' );

		$collection = $this->create_collection( 'Pretty adjacent' );
		$items      = $this->create_items( $collection, array(
			'Alpha' => 'publish',
			'Beta'  => 'publish',
			'Gamma' => 'publish',
		) );

		$beta_permalink = get_permalink( $items['Beta']->get_id() );
		$this->assertSame( false, strpos( $beta_permalink, '?' ) );

		$this->open_item_from_list( $items['Beta'], $this->list_args( $collection->get_id(), 1 ) );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assert_neighbor_url( $items['Gamma'], $adjacent['next']['url'], array(
			'pos'   => '2',
			'ref'   => "hello world's",
			'extra' => array(
				array( 'note' => 'hello world' ),
			),
		) );
		$this->assertSame( false, strpos( $adjacent['next']['url'], $collection->get_db_identifier() . '=' ) );
		$this->assertNotSame( false, strpos( $adjacent['next']['url'], '/' . $items['Gamma']->get_slug() . '/' ) );
	}

	/**
	 * A draft permalink is "?post_type=…&p=…". Following it must keep "pos"
	 * as its own parameter, and the next link must not keep this draft's id.
	 */
	public function test_draft_neighbor_keeps_its_own_post_id_and_the_list_position() {
		$this->set_permalink_structure( '/%postname%/' );

		$collection = $this->create_collection( 'Draft adjacent' );
		$items      = $this->create_items( $collection, array(
			'Alpha' => 'publish',
			'Beta'  => 'draft',
			'Gamma' => 'publish',
		) );

		$args = $this->list_args( $collection->get_id(), 0 );
		$args['status'] = 'any';
		$this->open_item_from_list( $items['Alpha'], $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assertSame( 'Beta', $adjacent['next']['title'] );
		$this->assert_neighbor_url( $items['Beta'], $adjacent['next']['url'], array(
			'pos'       => '1',
			'post_type' => $collection->get_db_identifier(),
			'p'         => (string) $items['Beta']->get_id(),
		) );
		$this->assertSame( false, strpos( $adjacent['next']['url'], '?pos=' ) );

		$this->open_item_from_list( $items['Beta'], array_merge( $args, array( 'pos' => 1 ) ) );
		$from_draft = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assertSame( 'Alpha', $from_draft['previous']['title'] );
		$this->assertSame( 'Gamma', $from_draft['next']['title'] );
		$this->assert_neighbor_url( $items['Gamma'], $from_draft['next']['url'], array(
			'pos' => '2',
		) );
		$next_vars = $this->query_vars_from_url( $from_draft['next']['url'] );
		$this->assertArrayNotHasKey( 'p', $next_vars );
		$this->assertArrayNotHasKey( 'post_type', $next_vars );
	}

	/**
	 * `name` is the public title filter. It must stay on the next URL, or the
	 * following page walks the unfiltered collection and `pos` points elsewhere.
	 */
	public function test_title_filter_name_stays_on_the_neighbor_url() {
		$this->set_permalink_structure( '/%postname%/' );

		$collection = $this->create_collection( 'Named adjacent' );
		$first      = $this->create_dated_item( $collection, 'Shared', '2020-01-01 00:00:00' );
		$this->create_dated_item( $collection, 'Other', '2020-01-02 00:00:00' );
		$middle     = $this->create_dated_item( $collection, 'Shared', '2020-01-03 00:00:00' );
		$last       = $this->create_dated_item( $collection, 'Shared', '2020-01-04 00:00:00' );

		$args = $this->list_args( $collection->get_id(), 1 );
		$args['orderby'] = 'date';
		$args['order']   = 'ASC';
		$args['name']    = 'Shared';
		$this->open_item_from_list( $middle, $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assert_neighbor_url( $last, $adjacent['next']['url'], array(
			'pos'  => '2',
			'name' => 'Shared',
		) );
		$this->assert_neighbor_url( $first, $adjacent['previous']['url'], array(
			'pos'  => '0',
			'name' => 'Shared',
		) );

		$this->go_to( $adjacent['next']['url'] );
		$from_next = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assert_neighbor_url( $middle, $from_next['previous']['url'], array(
			'pos'  => '1',
			'name' => 'Shared',
		) );
	}

	/**
	 * page_id and tainacan_repository_archive belong to the page that hosts the
	 * list. Carrying either one makes the item URL open that page, or the
	 * repository archive, instead of the item.
	 */
	public function test_host_page_query_vars_do_not_follow_the_neighbor() {
		$this->set_permalink_structure( '' );

		$collection = $this->create_collection( 'Host page adjacent' );
		$items      = $this->create_items( $collection, array(
			'Alpha' => 'publish',
			'Beta'  => 'publish',
			'Gamma' => 'publish',
		) );

		$args = $this->list_args( $collection->get_id(), 1 );
		$args['page_id'] = 42;
		$args['tainacan_repository_archive'] = 1;
		$this->open_item_from_list( $items['Beta'], $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();
		$vars     = $this->query_vars_from_url( $adjacent['next']['url'] );

		$this->assertSame( 'Gamma', $adjacent['next']['title'] );
		$this->assertArrayNotHasKey( 'page_id', $vars );
		$this->assertArrayNotHasKey( 'tainacan_repository_archive', $vars );
		$this->assertSame( '2', (string) $vars['pos'] );
	}

	/**
	 * A draft under plain permalinks already uses "?post_type=&p=". The list
	 * arguments have to be joined with "&".
	 */
	public function test_plain_permalink_draft_keeps_a_single_query_string() {
		$this->set_permalink_structure( '' );

		$collection = $this->create_collection( 'Plain draft adjacent' );
		$items      = $this->create_items( $collection, array(
			'Alpha' => 'publish',
			'Beta'  => 'draft',
			'Gamma' => 'publish',
		) );

		$args = $this->list_args( $collection->get_id(), 0 );
		$args['status'] = 'any';
		$this->open_item_from_list( $items['Alpha'], $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assertSame( 'Beta', $adjacent['next']['title'] );
		$this->assert_neighbor_url( $items['Beta'], $adjacent['next']['url'], array(
			'pos'       => '1',
			'post_type' => $collection->get_db_identifier(),
			'p'         => (string) $items['Beta']->get_id(),
		) );
		$this->assertSame( false, strpos( $adjacent['next']['url'], '?pos=' ) );
	}

	/**
	 * In a repository list the neighbor can belong to another collection,
	 * so its query var is a different key from the current item's.
	 */
	public function test_repository_list_uses_the_neighbor_collection_query_var() {
		$this->set_permalink_structure( '' );

		$first  = $this->create_collection( 'Repository A' );
		$second = $this->create_collection( 'Repository B' );
		$alpha  = $this->create_items( $first, array( 'Alpha' => 'publish' ) );
		$beta   = $this->create_items( $second, array( 'Beta' => 'publish' ) );

		$args = $this->list_args( 0, 0 );
		$args['source_list'] = 'repository';
		unset( $args['source_entity_id'] );
		$this->open_item_from_list( $alpha['Alpha'], $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assertSame( 'Beta', $adjacent['next']['title'] );
		$this->assert_neighbor_url( $beta['Beta'], $adjacent['next']['url'], array(
			'pos'                          => '1',
			'source_list'                  => 'repository',
			$second->get_db_identifier()   => $beta['Beta']->get_slug(),
		) );
		$vars = $this->query_vars_from_url( $adjacent['next']['url'] );
		$this->assertArrayNotHasKey( $first->get_db_identifier(), $vars );
	}

	/**
	 * An anonymous visitor asking for drafts walks the public list instead.
	 * The draft title is not disclosed, and status does not follow the link.
	 */
	public function test_anonymous_visitor_does_not_receive_a_draft_neighbor() {
		$this->set_permalink_structure( '/%postname%/' );

		$collection = $this->create_collection( 'Anonymous draft adjacent' );
		$items      = $this->create_items( $collection, array(
			'Alpha' => 'publish',
			'Beta'  => 'draft',
			'Gamma' => 'publish',
		) );

		$args = $this->list_args( $collection->get_id(), 0 );
		$args['status'] = 'draft';
		wp_set_current_user( 0 );
		$this->open_item_from_list( $items['Alpha'], $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assertSame( 'Gamma', $adjacent['next']['title'] );
		$this->assertArrayNotHasKey( 'status', $this->query_vars_from_url( $adjacent['next']['url'] ) );
	}

	/**
	 * An author keeps their own draft and does not receive someone else's.
	 */
	public function test_author_sees_their_own_draft_and_not_another_authors() {
		$this->set_permalink_structure( '/%postname%/' );

		$collection = $this->create_collection( 'Author draft adjacent' );
		$author_id  = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$author     = get_user_by( 'id', $author_id );
		$author->add_cap( 'tnc_col_' . $collection->get_id() . '_edit_items' );

		$items = $this->create_items( $collection, array(
			'Able'  => 'draft',
			'Mine'  => 'draft',
			'Theirs' => 'draft',
		) );
		wp_update_post( array(
			'ID'          => $items['Able']->get_id(),
			'post_author' => $author_id,
		) );
		wp_update_post( array(
			'ID'          => $items['Mine']->get_id(),
			'post_author' => $author_id,
		) );

		$args = $this->list_args( $collection->get_id(), 1 );
		$args['status'] = 'draft';
		wp_set_current_user( $author_id );
		$this->open_item_from_list( $items['Mine'], $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();

		$this->assertSame( 'Able', $adjacent['previous']['title'] );
		$this->assertNull( $adjacent['next'] );
		$this->assertSame( 'draft', $this->query_vars_from_url( $adjacent['previous']['url'] )['status'] );
	}

	/**
	 * A metakey the visitor cannot read is dropped, value included, so the
	 * public order is what decides the neighbor.
	 */
	public function test_anonymous_visitor_does_not_filter_by_a_private_metadata_section() {
		$this->set_permalink_structure( '/%postname%/' );

		$collection = $this->create_collection( 'Private meta adjacent' );
		$section    = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'       => 'Private',
				'collection' => $collection,
				'status'     => 'private',
			),
			true
		);
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'                => 'Secret',
				'collection'          => $collection,
				'metadata_type'       => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $section->get_id(),
			),
			true
		);
		$items = $this->create_items( $collection, array(
			'Alpha' => 'publish',
			'Beta'  => 'publish',
			'Gamma' => 'publish',
		) );

		$args = $this->list_args( $collection->get_id(), 0 );
		$args['metakey']     = (string) $metadatum->get_id();
		$args['metavalue']   = 'secret';
		$args['metacompare'] = 'LIKE';
		wp_set_current_user( 0 );
		$this->open_item_from_list( $items['Alpha'], $args );

		$adjacent = \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();
		$vars     = $this->query_vars_from_url( $adjacent['next']['url'] );

		$this->assertSame( 'Beta', $adjacent['next']['title'] );
		$this->assertArrayNotHasKey( 'metakey', $vars );
		$this->assertArrayNotHasKey( 'metavalue', $vars );
		$this->assertArrayNotHasKey( 'metacompare', $vars );
	}

	/**
	 * @param string $name Collection name.
	 * @return \Tainacan\Entities\Collection
	 */
	private function create_collection( $name ) {
		return $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => $name,
				'status' => 'publish',
			),
			true
		);
	}

	/**
	 * @param \Tainacan\Entities\Collection $collection Collection that owns the items.
	 * @param array                          $titles    Map of title to post status, in list order.
	 * @return array<string, \Tainacan\Entities\Item>
	 */
	private function create_items( $collection, $titles ) {
		$items = array();
		foreach ( $titles as $title => $status ) {
			$items[ $title ] = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => $title,
					'status'     => $status,
					'collection' => $collection,
				),
				true
			);
		}
		return $items;
	}

	/**
	 * @param \Tainacan\Entities\Collection $collection Collection that owns the item.
	 * @param string                         $title      Item title.
	 * @param string                         $date       MySQL post date.
	 * @return \Tainacan\Entities\Item
	 */
	private function create_dated_item( $collection, $title, $date ) {
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => $title,
				'status'     => 'publish',
				'collection' => $collection,
			),
			true
		);
		wp_update_post( array(
			'ID'            => $item->get_id(),
			'post_date'     => $date,
			'post_date_gmt' => $date,
		) );
		return $item;
	}

	/**
	 * @param int $collection_id Collection the list belongs to.
	 * @param int $position      Zero-based position of the open item.
	 * @return array
	 */
	private function list_args( $collection_id, $position ) {
		return array(
			'pos'              => $position,
			'source_list'      => 'collection',
			'source_entity_id' => $collection_id,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'ref'              => "hello world's",
			'extra'            => array(
				array( 'note' => 'hello world' ),
			),
		);
	}

	/**
	 * Opens an item the way the list does: the item permalink plus the list query.
	 *
	 * @param \Tainacan\Entities\Item $item Item being visited.
	 * @param array                    $args List query arguments.
	 */
	private function open_item_from_list( $item, $args ) {
		$permalink = get_permalink( $item->get_id() );
		$extra     = http_build_query( $args );
		$separator = ( false === strpos( $permalink, '?' ) ) ? '?' : '&';
		$this->go_to( $permalink . $separator . $extra );
	}

	/**
	 * @param string $url Absolute URL.
	 * @return array
	 */
	private function query_vars_from_url( $url ) {
		$vars  = array();
		$query = wp_parse_url( $url, PHP_URL_QUERY );
		wp_parse_str( is_string( $query ) ? $query : '', $vars );
		return $vars;
	}

	/**
	 * @param \Tainacan\Entities\Item $item     Expected neighbor.
	 * @param string                  $url      Built navigation URL.
	 * @param array                   $expected Query arguments that must survive the round trip.
	 */
	private function assert_neighbor_url( $item, $url, $expected ) {
		$this->assertTrue( is_string( $url ) );
		$this->assertSame( 1, substr_count( $url, '?' ) );
		$this->assertStringStartsWith( get_permalink( $item->get_id() ), $url );

		$vars = $this->query_vars_from_url( $url );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $vars );
			$this->assertSame( $value, $vars[ $key ] );
		}
	}
}
