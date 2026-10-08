<?php

namespace Tainacan\Tests;

use Tainacan\Entities;
use Tainacan\Repositories;

/**
 * @group api
 */
class Core_Description_Rich_Text extends TAINACAN_UnitApiTestCase {

	private function create_item_with_core_description( $description, $collection = null ) {
		if ( ! $collection ) {
			$collection = $this->tainacan_entity_factory->create_entity( 'collection', [
				'name' => 'Descriptions',
				'status' => 'publish',
			], true );
		}

		$item = $this->tainacan_entity_factory->create_entity( 'item', [
			'title' => 'Description test',
			'description' => $description,
			'collection' => $collection,
			'status' => 'publish',
		], true );

		return [ $collection, $item, $collection->get_core_description_metadatum() ];
	}

	private function enable_rich_text_editor( $metadatum ) {
		update_option( 'tainacan_option_allow_rich_text_editor', true );
		$metadatum->set_metadata_type_options( [ 'use_rich_text_editor' => 'yes' ] );
		$this->assertTrue( $metadatum->validate() );
		Repositories\Metadata::get_instance()->insert( $metadatum );
	}

	private function read_description( $item, $metadatum, $context = 'edit' ) {
		$request = new \WP_REST_Request( 'GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $metadatum->get_id() );
		$request->set_param( 'context', $context );
		return $this->server->dispatch( $request );
	}

	private function save_description( $item, $metadatum, $value ) {
		$request = new \WP_REST_Request( 'PUT', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $metadatum->get_id() );
		$request->set_body( wp_json_encode( [ 'values' => $value ] ) );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param( 'context', 'edit' );
		return $this->server->dispatch( $request );
	}

	public function test_legacy_description_stays_stored_while_html_is_formatted_for_display() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( "First line\n\nSecond line" );
		$this->enable_rich_text_editor( $metadatum );

		$response = $this->read_description( $item, $metadatum );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( "First line\n\nSecond line", $data['value'] );
		$this->assertSame( '<p>First line</p>' . "\n" . '<p>Second line</p>' . "\n", $data['value_as_html'] );
		$this->assertArrayNotHasKey( 'saved_with_rich_text_editor', $data );
		$this->assertArrayNotHasKey( 'value_for_rich_text_editor', $data );
		$this->assertSame( "First line\n\nSecond line", get_post( $item->get_id() )->post_content );

		$public = $this->read_description( $item, $metadatum, 'view' )->get_data();
		$this->assertArrayNotHasKey( 'saved_with_rich_text_editor', $public );
		$this->assertArrayNotHasKey( 'value_for_rich_text_editor', $public );
		$this->assertSame( $data['value_as_html'], $public['value_as_html'] );
	}

	public function test_rich_html_is_stored_and_still_formatted_for_display() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( "First\n\nSecond" );
		$this->enable_rich_text_editor( $metadatum );

		$rich = '<p>First</p><p>Second</p>';
		$response = $this->save_description( $item, $metadatum, $rich );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $rich, get_post( $item->get_id() )->post_content );
		$this->assertSame( $rich, get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );

		$data = $this->read_description( $item, $metadatum )->get_data();
		$this->assertSame( $rich, $data['value'] );
		$this->assertStringContainsString( '<p>First</p>', $data['value_as_html'] );
		$this->assertStringContainsString( '<p>Second</p>', $data['value_as_html'] );

		$plain = "Second tab\n\nPlain";
		$response = $this->save_description( $item, $metadatum, $plain );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $plain, get_post( $item->get_id() )->post_content );
		$this->assertSame( $plain, get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_external_description_change_updates_the_queryable_copy() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Original' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Rich</p>' )->get_status() );

		$repository = Repositories\Items::get_instance();
		$item = $repository->fetch( $item->get_id() );
		$item->set_title( 'New title' );
		$this->assertTrue( $item->validate() );
		$repository->update( $item );
		$this->assertSame( '<p>Rich</p>', get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );

		$item = $repository->fetch( $item->get_id() );
		$item->set_description( "External\n\nText" );
		$this->assertTrue( $item->validate() );
		$repository->update( $item );
		$this->assertSame( "External\n\nText", get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_failed_mirror_write_restores_description() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Before</p>' )->get_status() );

		$block_mirror = static function ( $check, $object_id, $meta_key ) use ( $item, $metadatum ) {
			if ( (int) $object_id === $item->get_id() && (string) $meta_key === (string) $metadatum->get_id() ) {
				return true;
			}
			return $check;
		};
		add_filter( 'update_post_metadata', $block_mirror, 10, 3 );
		try {
			$response = $this->save_description( $item, $metadatum, 'After' );
		} finally {
			remove_filter( 'update_post_metadata', $block_mirror, 10 );
		}

		$this->assertSame( 500, $response->get_status() );
		$this->assertSame( '<p>Before</p>', get_post( $item->get_id() )->post_content );
		$this->assertSame( '<p>Before</p>', get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_duplication_copies_the_description() {
		[ $collection, $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Rich copy</p>' )->get_status() );

		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collection/' . $collection->get_id() . '/items/' . $item->get_id() . '/duplicate' );
		$request->set_body( wp_json_encode( [ 'copies' => 1 ] ) );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 201, $response->get_status() );
		$copy_id = $response->get_data()['items'][0]['id'];
		$this->assertSame( '<p>Rich copy</p>', get_post( $copy_id )->post_content );
	}

	public function test_mixed_legacy_description_has_single_links_and_preserves_special_text() {
		$value = '<p>Existing</p>' . "\nLoose line\n" . '<a href="https://example.org">ready</a>' . "\nVisit https://example.net/path?x=1&y=2.";
		[ , $item, $metadatum ] = $this->create_item_with_core_description( $value );
		$data = $this->read_description( $item, $metadatum )->get_data();
		$this->assertSame( get_post( $item->get_id() )->post_content, $data['value'] );
		$this->assertStringContainsString( '&amp;y=2', $data['value'] );
		$this->assertSame( 2, substr_count( $data['value_as_html'], '<a ' ) );
		$this->assertSame( 1, substr_count( $data['value_as_html'], '<p>Existing</p>' ) );
		$this->assertStringNotContainsString( '<a href="https://example.org"><a ', $data['value_as_html'] );
	}

	public function test_item_rest_update_replaces_description_and_its_copy() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Before</p>' )->get_status() );

		$request = new \WP_REST_Request( 'PUT', $this->namespace . '/items/' . $item->get_id() );
		$request->set_body( wp_json_encode( [ 'description' => "Outside\n\nEditor" ] ) );
		$request->set_header( 'Content-Type', 'application/json' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( "Outside\n\nEditor", get_post( $item->get_id() )->post_content );
		$this->assertSame( "Outside\n\nEditor", get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_edit_only_fields_and_writes_are_not_available_without_permission() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Public' );
		$this->enable_rich_text_editor( $metadatum );
		wp_set_current_user( 0 );

		$data = $this->read_description( $item, $metadatum, 'edit' )->get_data();
		$this->assertArrayNotHasKey( 'saved_with_rich_text_editor', $data );
		$this->assertArrayNotHasKey( 'value_for_rich_text_editor', $data );
		$this->assertSame( 401, $this->save_description( $item, $metadatum, '<p>Denied</p>' )->get_status() );
		$this->assertSame( 'Public', get_post( $item->get_id() )->post_content );
	}

	public function test_unsafe_markup_is_sanitized() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$payload = '<p>Safe</p><img src=x onerror="alert(1)"><a href="javascript:alert(1)">bad</a>';

		$this->assertSame( 200, $this->save_description( $item, $metadatum, $payload )->get_status() );
		$html = get_post( $item->get_id() )->post_content;
		$this->assertStringNotContainsString( 'onerror', $html );
		$this->assertStringNotContainsString( 'javascript:', $html );
		$this->assertStringContainsString( 'Safe', $html );
	}

	public function test_empty_description_does_not_create_a_queryable_empty_copy() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '' )->get_status() );
		$this->assertSame( '', get_post( $item->get_id() )->post_content );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), $metadatum->get_id() ) );
		$data = $this->read_description( $item, $metadatum )->get_data();
		$this->assertSame( '', $data['value_as_html'] );
	}


	public function test_failed_mirror_creation_removes_a_new_item() {
		global $wpdb;
		$collection = $this->tainacan_entity_factory->create_entity( 'collection', [
			'name' => 'Failed creation', 'status' => 'publish',
		], true );
		$metadatum_id = $collection->get_core_description_metadatum()->get_id();
		$item = $this->tainacan_entity_factory->create_entity( 'item', [
			'title' => 'Failed description copy fixture',
			'description' => 'Some content',
			'collection' => $collection,
			'status' => 'draft',
		], false );
		$this->assertTrue( $item->validate() );

		$block_mirror = static function ( $check, $object_id, $meta_key ) use ( $metadatum_id ) {
			return (string) $meta_key === (string) $metadatum_id ? true : $check;
		};
		add_filter( 'add_post_metadata', $block_mirror, 10, 3 );
		try {
			$result = Repositories\Items::get_instance()->insert( $item );
		} finally {
			remove_filter( 'add_post_metadata', $block_mirror, 10 );
		}
		$this->assertFalse( $result );
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_title = %s", 'Failed description copy fixture' ) );
		$this->assertSame( 0, $count );
	}
}
