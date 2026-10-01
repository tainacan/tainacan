<?php

namespace Tainacan\Tests;

use Tainacan\Repositories;

/**
 * @group api
 */
class Rich_Text_Metadata extends TAINACAN_UnitApiTestCase {
	private function create_item_and_metadatum( $options = [] ) {
		$collection = $this->tainacan_entity_factory->create_entity( 'collection', [
			'name' => 'Rich text collection',
			'status' => 'publish',
		], true );
		$item = $this->tainacan_entity_factory->create_entity( 'item', [
			'title' => 'Rich text item',
			'collection' => $collection,
			'status' => 'publish',
		], true );
		$metadatum = $this->tainacan_entity_factory->create_entity( 'metadatum', array_merge( [
			'name' => 'Rich text field',
			'collection' => $collection,
			'status' => 'publish',
			'metadata_type' => 'Tainacan\\Metadata_Types\\Rich_Text',
		], $options ), true );
		return [ $item, $metadatum ];
	}

	private function put_value( $item, $metadatum, $value ) {
		$request = new \WP_REST_Request( 'PUT', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $metadatum->get_id() );
		$request->set_body( wp_json_encode( [ 'values' => $value ] ) );
		$request->set_header( 'Content-Type', 'application/json' );
		return $this->server->dispatch( $request );
	}

	private function get_value( $item, $metadatum ) {
		$request = new \WP_REST_Request( 'GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $metadatum->get_id() );
		return $this->server->dispatch( $request );
	}

	public function test_rich_text_is_a_registered_non_core_metadata_type() {
		$types = Repositories\Metadata::get_instance()->fetch_metadata_types();
		$this->assertContains( 'Tainacan\\Metadata_Types\\Rich_Text', $types );

		$type = new \Tainacan\Metadata_Types\Rich_Text();
		$this->assertSame( 'long_string', $type->get_primitive_type() );
		$this->assertSame( 'tainacan-rich-text', $type->get_component() );
		$this->assertFalse( $type->get_core() );
	}

	public function test_rich_text_definition_can_be_created_through_rest_api() {
		$collection = $this->tainacan_entity_factory->create_entity( 'collection', [ 'name' => 'Rich text definitions', 'status' => 'publish' ], true );
		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collection/' . $collection->get_id() . '/metadata' );
		$request->set_body( wp_json_encode( [
			'name' => 'Essay',
			'metadata_type' => 'Tainacan\\Metadata_Types\\Rich_Text',
		] ) );
		$request->set_header( 'Content-Type', 'application/json' );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 201, $response->get_status() );
		$this->assertSame( 'tainacan-rich-text', $response->get_data()['metadata_type_object']['component'] );
	}

	public function test_rest_round_trip_keeps_safe_html_without_converting_it_again() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum();
		update_option( 'tainacan_option_allow_rich_text_editor', false );
		$html = '<p>First <strong>line</strong></p>' . "\n" . '<p>Second <a href="https://example.org">link</a></p>';
		$response = $this->put_value( $item, $metadatum, $html );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $html, get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );

		$data = $this->get_value( $item, $metadatum )->get_data();
		$this->assertSame( $html, $data['value'] );
		$this->assertSame( $html, $data['value_as_html'] );
		$this->assertArrayNotHasKey( 'saved_with_rich_text_editor', $data );
		$this->assertArrayNotHasKey( 'value_for_rich_text_editor', $data );
	}

	public function test_rest_write_removes_executable_markup_but_preserves_safe_links() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum();
		$response = $this->put_value( $item, $metadatum, '<p onclick="alert(1)">Safe</p><script>alert(1)</script><a href="javascript:alert(1)">Bad</a><a href="https://example.org">Good</a>' );
		$this->assertSame( 200, $response->get_status() );
		$value = $this->get_value( $item, $metadatum )->get_data()['value'];
		$this->assertStringNotContainsString( 'onclick', $value );
		$this->assertStringNotContainsString( '<script', $value );
		$this->assertStringNotContainsString( 'javascript:', $value );
		$this->assertStringContainsString( 'href="https://example.org"', $value );
	}

	public function test_multiple_values_render_as_html_without_plain_text_conversion() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum( [ 'multiple' => 'yes', 'html_formatting' => 'list' ] );
		$values = [ '<p>First</p>', '<p>Second <em>part</em></p>' ];
		$this->assertSame( 200, $this->put_value( $item, $metadatum, $values )->get_status() );
		$data = $this->get_value( $item, $metadatum )->get_data();
		$this->assertSame( $values, $data['value'] );
		$this->assertSame( '<ul><li><p>First</p></li><li><p>Second <em>part</em></p></li></ul>', $data['value_as_html'] );
	}

	public function test_visually_empty_editor_markup_does_not_fill_required_metadata() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum( [ 'required' => 'yes' ] );
		$this->assertSame( 400, $this->put_value( $item, $metadatum, '<p><br></p>' )->get_status() );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), $metadatum->get_id() ) );
	}

	public function test_visually_empty_editor_markup_clears_optional_metadata() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum();
		$this->assertSame( 200, $this->put_value( $item, $metadatum, '<p>Filled</p>' )->get_status() );
		$this->assertSame( 200, $this->put_value( $item, $metadatum, '<p><br></p>' )->get_status() );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), $metadatum->get_id() ) );
	}
}
