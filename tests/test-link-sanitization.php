<?php

namespace Tainacan\Tests;

use Tainacan\Repositories;

/**
 * @group api
 * @group link-sanitization
 */
class Link_Sanitization extends TAINACAN_UnitApiTestCase {
	private $link_collection;
	private $link_item;
	private $link = '<a href="https://example.org">Linked</a>';

	public function setUp(): void {
		parent::setUp();
		$this->link_collection = $this->tainacan_entity_factory->create_entity( 'collection', [ 'name' => 'Links', 'status' => 'publish' ], true );
		$this->link_item = $this->tainacan_entity_factory->create_entity( 'item', [ 'title' => 'Item', 'collection' => $this->link_collection, 'status' => 'publish' ], true );
	}

	private function field( $type = 'Textarea', $multiple = 'no' ) {
		return $this->tainacan_entity_factory->create_entity( 'metadatum', [
			'name' => 'Links field', 'collection' => $this->link_collection, 'status' => 'publish',
			'metadata_type' => 'Tainacan\\Metadata_Types\\' . $type, 'multiple' => $multiple,
		], true );
	}

	private function put( $field, $value ) {
		$request = new \WP_REST_Request( 'PUT', $this->namespace . '/item/' . $this->link_item->get_id() . '/metadata/' . $field->get_id() );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( [ 'values' => $value ] ) );
		return $this->server->dispatch( $request );
	}

	public function test_non_title_metadata_preserves_safe_links_and_removes_executable_markup() {
		foreach ( [ 'Textarea', 'Text' ] as $type ) {
			foreach ( [ 'no', 'yes' ] as $multiple ) {
				$field = $this->field( $type, $multiple );
				$input = $this->link . '<script>alert(1)</script><a href="javascript:alert(1)" onclick="alert(1)">Unsafe</a>';
				$this->assertSame( 200, $this->put( $field, $multiple === 'yes' ? [ $input, '0' ] : $input )->get_status() );
				$values = get_post_meta( $this->link_item->get_id(), $field->get_id(), $multiple !== 'yes' );
				$value = $multiple === 'yes' ? $values[0] : $values;
				$this->assertStringContainsString( $this->link, $value );
				foreach ( [ '<script', 'javascript:', 'onclick' ] as $unsafe ) {
					$this->assertStringNotContainsString( $unsafe, $value );
				}
				if ( $multiple === 'yes' ) $this->assertSame( '0', $values[1] );
			}
		}
	}

	public function test_textual_document_and_field_properties_preserve_links() {
		$this->link_item->set_document_type( 'text' );
		$this->link_item->set_document( $this->link . '\\ literal' );
		$this->assertTrue( $this->link_item->validate() );
		$saved = Repositories\Items::get_instance()->insert( $this->link_item );
		$this->assertSame( $this->link . '\\ literal', $saved->get_document() );
		$field = $this->field();
		$field->set_placeholder( $this->link );
		$this->assertTrue( $field->validate() );
		$saved_field = Repositories\Metadata::get_instance()->insert( $field );
		$this->assertSame( $this->link, $saved_field->get_placeholder() );
	}

	public function test_entity_titles_remove_links_but_descriptions_keep_them() {
		$field = $this->field();
		$entities = [
			[ 'collection', [] ], [ 'item', [ 'collection' => $this->link_collection ] ],
			[ 'taxonomy', [] ], [ 'metadatum', [ 'collection' => $this->link_collection, 'metadata_type' => 'Tainacan\\Metadata_Types\\Text' ] ],
			[ 'metadata_section', [ 'collection' => $this->link_collection ] ],
			[ 'filter', [ 'collection' => $this->link_collection, 'metadatum_id' => $field->get_id(), 'filter_type' => 'Tainacan\\Filter_Types\\Text' ] ],
			[ 'log', [] ],
		];
		foreach ( $entities as [ $type, $options ] ) {
			$property = in_array( $type, [ 'item', 'log' ], true ) ? 'title' : 'name';
			$entity = $this->tainacan_entity_factory->create_entity( $type, array_merge( $options, [ $property => $this->link, 'description' => $this->link ] ), true );
			$this->assertNotEmpty( $entity->get_id(), $type );
			if ( $type === 'log' ) $entity = Repositories\Logs::get_instance()->fetch( $entity->get_id() );
			$this->assertSame( 'Linked', $entity->get( $property ), $type );
			$this->assertSame( $this->link, $entity->get_description(), $type );
		}
	}

	public function test_core_title_rest_write_removes_links_from_title_and_mirror() {
		$field = $this->link_collection->get_core_title_metadatum();
		$this->assertSame( 200, $this->put( $field, $this->link )->get_status() );
		$this->assertSame( 'Linked', get_post( $this->link_item->get_id() )->post_title );
		$this->assertSame( 'Linked', get_post_meta( $this->link_item->get_id(), $field->get_id(), true ) );
	}

	public function test_rest_metadata_query_preserves_link_values_and_matches_saved_content() {
		$field = $this->field();
		$this->assertSame( 200, $this->put( $field, $this->link )->get_status() );
		foreach ( [ [ 'key' => $field->get_id(), 'value' => $this->link ], [ [ 'key' => $field->get_id(), 'value' => [ $this->link ], 'compare' => 'IN' ] ] ] as $query ) {
			$request = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $this->link_collection->get_id() . '/items' );
			$request->set_query_params( [ 'metaquery' => $query ] );
			$response = $this->server->dispatch( $request );
			$this->assertSame( 200, $response->get_status() );
			$this->assertContains( $this->link_item->get_id(), array_column( $response->get_data()['items'], 'id' ) );
		}
	}

	public function test_rest_title_queries_remove_links_before_matching() {
		$field = $this->link_collection->get_core_title_metadatum();
		$this->assertSame( 200, $this->put( $field, $this->link )->get_status() );
		foreach ( [ [ 'title' => $this->link ], [ 'name' => $this->link ], [ 'metaquery' => [ 'key' => $field->get_id(), 'value' => $this->link ] ] ] as $query ) {
			$request = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $this->link_collection->get_id() . '/items' );
			$request->set_query_params( $query );
			$response = $this->server->dispatch( $request );
			$this->assertSame( 200, $response->get_status() );
			$this->assertContains( $this->link_item->get_id(), array_column( $response->get_data()['items'], 'id' ) );
		}
	}

	public function test_term_names_remove_links_on_create_and_update() {
		$taxonomy = $this->tainacan_entity_factory->create_entity( 'taxonomy', [ 'name' => 'Terms', 'status' => 'publish' ], true );
		$term = $this->tainacan_entity_factory->create_entity( 'term', [ 'name' => $this->link, 'taxonomy' => $taxonomy->get_db_identifier(), 'description' => $this->link ], true );
		$this->assertSame( 'Linked', $term->get_name() );
		$this->assertSame( $this->link, $term->get_description() );
		$term->set_name( '<a href="https://example.org">Updated</a>' );
		$this->assertTrue( $term->validate() );
		$this->assertSame( 'Updated', Repositories\Terms::get_instance()->insert( $term )->get_name() );
	}

	public function test_rest_taxonomy_query_sanitizes_names_but_preserves_other_safe_values() {
		$taxonomy = $this->tainacan_entity_factory->create_entity( 'taxonomy', [ 'name' => 'Queries', 'status' => 'publish' ], true );
		foreach ( [ 'name' => 'Linked', 'slug' => $this->link ] as $field => $expected ) {
			foreach ( [ $this->link, [ $this->link ] ] as $input ) {
				$captured = null;
				$capture = function ( $args ) use ( &$captured ) { $captured = $args; return $args; };
				add_filter( 'tainacan-api-prepare-items-args', $capture, 20 );
				try {
					$request = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $this->link_collection->get_id() . '/items' );
					$request->set_query_params( [ 'taxquery' => [ [ 'taxonomy' => $taxonomy->get_db_identifier(), 'metadatum' => $field, 'terms' => $input ] ] ] );
					$this->assertSame( 200, $this->server->dispatch( $request )->get_status() );
					$this->assertSame( is_array( $input ) ? [ $expected ] : $expected, ($captured['tax_query'][0]['terms'] ?? $captured['tax_query']['terms'] ?? null), wp_json_encode( $captured ) );
				} finally {
					remove_filter( 'tainacan-api-prepare-items-args', $capture, 20 );
				}
			}
		}
	}
}
