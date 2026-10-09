<?php

namespace Tainacan\Tests;

use Tainacan\Repositories;

/**
 * @group api
 */
class Textarea_Rich_Text_Option extends TAINACAN_UnitApiTestCase {
	private function create_item_and_metadatum( $options = [] ) {
		$collection = $this->tainacan_entity_factory->create_entity( 'collection', [
			'name' => 'Textarea collection',
			'status' => 'publish',
		], true );
		$item = $this->tainacan_entity_factory->create_entity( 'item', [
			'title' => 'Textarea item',
			'collection' => $collection,
			'status' => 'publish',
		], true );
		$metadatum = $this->tainacan_entity_factory->create_entity( 'metadatum', array_merge( [
			'name' => 'Long text',
			'collection' => $collection,
			'status' => 'publish',
			'metadata_type' => 'Tainacan\\Metadata_Types\\Textarea',
			'metadata_type_options' => [ 'use_rich_text_editor' => 'yes' ],
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

	public function test_rich_text_is_not_a_separate_metadata_type() {
		$types = Repositories\Metadata::get_instance()->fetch_metadata_types();
		$this->assertNotContains( 'Tainacan\\Metadata_Types\\Rich_Text', $types );

		$type = new \Tainacan\Metadata_Types\Textarea();
		$this->assertSame( 'no', $type->get_options()['use_rich_text_editor'] );
		$this->assertSame( 'tainacan-textarea', $type->get_component() );
	}

	public function test_textarea_can_enable_the_rich_text_editor_option() {
		$collection = $this->tainacan_entity_factory->create_entity( 'collection', [ 'name' => 'Textarea definitions', 'status' => 'publish' ], true );
		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collection/' . $collection->get_id() . '/metadata' );
		$request->set_body( wp_json_encode( [
			'name' => 'Essay',
			'metadata_type' => 'Tainacan\\Metadata_Types\\Textarea',
			'metadata_type_options' => [ 'use_rich_text_editor' => 'yes' ],
		] ) );
		$request->set_header( 'Content-Type', 'application/json' );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 201, $response->get_status() );
		$this->assertSame( 'tainacan-textarea', $response->get_data()['metadata_type_object']['component'] );
		$this->assertSame( 'yes', $response->get_data()['metadata_type_options']['use_rich_text_editor'] );
	}

	public function test_editor_html_stays_stored_and_display_still_formats_it() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum();
		update_option( 'tainacan_option_allow_rich_text_editor', false );
		$html = '<p>First <strong>line</strong></p><p>Second <a href="https://example.org">link</a></p>';
		$response = $this->put_value( $item, $metadatum, $html );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $html, get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );

		$data = $this->get_value( $item, $metadatum )->get_data();
		$this->assertSame( $html, $data['value'] );
		$this->assertStringContainsString( '<p>First <strong>line</strong></p>', $data['value_as_html'] );
		$this->assertStringContainsString( '<a href="https://example.org">link</a>', $data['value_as_html'] );
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

	public function test_multiple_values_keep_each_entry() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum( [ 'multiple' => 'yes', 'html_formatting' => 'list' ] );
		$values = [ '<p>First</p>', '<p>Second <em>part</em></p>' ];
		$this->assertSame( 200, $this->put_value( $item, $metadatum, $values )->get_status() );
		$data = $this->get_value( $item, $metadatum )->get_data();
		$this->assertSame( $values, $data['value'] );
		$this->assertStringContainsString( '<li>', $data['value_as_html'] );
		$this->assertStringContainsString( '<p>First</p>', $data['value_as_html'] );
		$this->assertStringContainsString( '<p>Second <em>part</em></p>', $data['value_as_html'] );
	}

	public function test_empty_paragraph_markup_is_stored_as_sent() {
		[ $item, $metadatum ] = $this->create_item_and_metadatum( [ 'required' => 'yes' ] );
		$this->assertSame( 200, $this->put_value( $item, $metadatum, '<p><br></p>' )->get_status() );
		$this->assertSame( '<p><br></p>', get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}
}
