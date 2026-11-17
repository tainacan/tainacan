<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Facets_Controller extends TAINACAN_UnitApiTestCase {

	/**
	 * With context=extended, a relationship value whose referenced item can no
	 * longer be fetched as an Item (here: its collection was removed, so
	 * Items::fetch() returns []) must be returned without the `entity` data
	 * instead of calling _toArray() on a non-entity and failing with 500.
	 */
	public function test_extended_facet_skips_entity_of_unfetchable_related_item() {
		$collection_author = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Facets authors',
				'status' => 'publish'
			),
			true
		);

		$collection_book = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Facets books',
				'status' => 'publish'
			),
			true
		);

		$metadatum_author = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'                  => 'author',
				'collection'            => $collection_book,
				'metadata_type'         => 'Tainacan\Metadata_Types\Relationship',
				'status'                => 'publish',
				'metadata_type_options' => [
					'collection_id' => $collection_author->get_id(),
					'search'        => ''
				]
			),
			true
		);

		$author = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'Facets author',
				'collection' => $collection_author,
				'status'     => 'publish'
			),
			true
		);

		$book = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'Facets book',
				'collection' => $collection_book,
				'status'     => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata( $book, $metadatum_author, $author->get_id() );

		$route = $this->namespace . '/collection/' . $collection_book->get_id() . '/facets/' . $metadatum_author->get_id();

		$find_author_value = function( $data ) use ( $author ) {
			foreach ( $data['values'] as $value ) {
				if ( (int) $value['value'] === $author->get_id() ) {
					return $value;
				}
			}
			return null;
		};

		// Control: while the author is fetchable, the extended context adds its entity.
		$request = new \WP_REST_Request( 'GET', $route );
		$request->set_query_params( array( 'context' => 'extended' ) );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$value = $find_author_value( $response->get_data() );
		$this->assertNotNull( $value, 'The related author should be a facet value' );
		$this->assertArrayHasKey( 'entity', $value );

		// Remove only the author collection row, leaving the author item and the
		// book's relationship value in place (an orphaned reference).
		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'ID' => $collection_author->get_id() ) );
		clean_post_cache( $collection_author->get_id() );
		$this->assertSame( [], \tainacan_items()->fetch( $author->get_id() ), 'Test setup failed: the author should no longer be fetchable as an Item' );

		$request = new \WP_REST_Request( 'GET', $route );
		$request->set_query_params( array( 'context' => 'extended' ) );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$value = $find_author_value( $response->get_data() );
		$this->assertNotNull( $value, 'The orphaned value is still listed' );
		$this->assertArrayNotHasKey( 'entity', $value );
	}
}
