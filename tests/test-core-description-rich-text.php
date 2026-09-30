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

	private function save_description( $item, $metadatum, $value, $mode = null ) {
		$request = new \WP_REST_Request( 'PUT', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $metadatum->get_id() );
		$body = [ 'values' => $value ];
		if ( null !== $mode ) {
			$body['edited_with_rich_text_editor'] = $mode;
		}
		$request->set_body( wp_json_encode( $body ) );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_param( 'context', 'edit' );
		return $this->server->dispatch( $request );
	}

	public function test_legacy_description_is_prepared_only_for_editing_without_persisting() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( "First line\n\nSecond line" );
		$this->enable_rich_text_editor( $metadatum );

		$response = $this->read_description( $item, $metadatum );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertFalse( $data['saved_with_rich_text_editor'] );
		$this->assertSame( "First line\n\nSecond line", $data['value'] );
		$this->assertSame( '<p>First line</p>' . "\n" . '<p>Second line</p>' . "\n", $data['value_for_rich_text_editor'] );
		$this->assertSame( $data['value_for_rich_text_editor'], $data['value_as_html'] );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor' ) );

		$public = $this->read_description( $item, $metadatum, 'view' )->get_data();
		$this->assertArrayNotHasKey( 'saved_with_rich_text_editor', $public );
		$this->assertArrayNotHasKey( 'value_for_rich_text_editor', $public );
	}

	public function test_rich_then_plain_saves_transition_the_item_marker_and_html() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( "First\n\nSecond" );
		$this->enable_rich_text_editor( $metadatum );

		$rich = '<p>First</p><p>Second</p>';
		$response = $this->save_description( $item, $metadatum, $rich, true );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'yes', get_post_meta( $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor', true ) );
		$this->assertSame( $rich, get_post( $item->get_id() )->post_content );
		$this->assertSame( $rich, get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );

		$data = $this->read_description( $item, $metadatum )->get_data();
		$this->assertTrue( $data['saved_with_rich_text_editor'] );
		$this->assertSame( $rich, $data['value_for_rich_text_editor'] );
		$this->assertSame( $rich, $data['value_as_html'] );

		$plain = '<p>Existing</p>' . "\nLoose line";
		$response = $this->save_description( $item, $metadatum, $plain, false );
		$this->assertSame( 200, $response->get_status() );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor' ) );
		$this->assertSame( $plain, get_post( $item->get_id() )->post_content );
		$this->assertSame( $plain, get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_external_description_change_clears_marker_but_other_item_edits_do_not() {
		[ $collection, $item, $metadatum ] = $this->create_item_with_core_description( 'Original' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Rich</p>', true )->get_status() );

		$repository = Repositories\Items::get_instance();
		$item = $repository->fetch( $item->get_id() );
		$item->set_title( 'New title' );
		$this->assertTrue( $item->validate() );
		$repository->update( $item );
		$this->assertSame( 'yes', get_post_meta( $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor', true ) );

		$item = $repository->fetch( $item->get_id() );
		$item->set_description( "External\n\nText" );
		$this->assertTrue( $item->validate() );
		$repository->update( $item );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor' ) );
		$this->assertSame( "External\n\nText", get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_editor_mode_is_rejected_when_disabled_or_sent_to_another_metadatum() {
		[ $collection, $item, $metadatum ] = $this->create_item_with_core_description( 'Original' );
		$this->enable_rich_text_editor( $metadatum );

		$this->assertSame( 400, $this->save_description( $item, $metadatum, '<p>Wrong</p>', 'yes' )->get_status() );
		update_option( 'tainacan_option_allow_rich_text_editor', false );
		$this->assertSame( 409, $this->save_description( $item, $metadatum, '<p>Wrong</p>', true )->get_status() );
		$this->assertSame( 'Original', get_post( $item->get_id() )->post_content );

		$title = $collection->get_core_title_metadatum();
		$this->assertSame( 400, $this->save_description( $item, $title, 'Wrong title', false )->get_status() );
		$this->assertSame( 'Description test', Repositories\Items::get_instance()->fetch( $item->get_id() )->get_title() );
	}

	public function test_rich_marker_isolated_between_two_items() {
		[ $collection, $first, $metadatum ] = $this->create_item_with_core_description( 'First' );
		[ , $second ] = $this->create_item_with_core_description( 'Second', $collection );
		$this->enable_rich_text_editor( $metadatum );

		$this->assertSame( 200, $this->save_description( $first, $metadatum, '<p>First</p>', true )->get_status() );
		$this->assertTrue( $this->read_description( $first, $metadatum )->get_data()['saved_with_rich_text_editor'] );
		$this->assertFalse( $this->read_description( $second, $metadatum )->get_data()['saved_with_rich_text_editor'] );
		$this->assertSame( 'Second', get_post( $second->get_id() )->post_content );
	}

	public function test_failed_mirror_write_restores_description_and_marker() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Before</p>', true )->get_status() );

		$block_mirror = static function ( $check, $object_id, $meta_key ) use ( $item, $metadatum ) {
			if ( (int) $object_id === $item->get_id() && (string) $meta_key === (string) $metadatum->get_id() ) {
				return true;
			}
			return $check;
		};
		add_filter( 'update_post_metadata', $block_mirror, 10, 3 );
		try {
			$response = $this->save_description( $item, $metadatum, 'After', false );
		} finally {
			remove_filter( 'update_post_metadata', $block_mirror, 10 );
		}

		$this->assertSame( 500, $response->get_status() );
		$this->assertSame( '<p>Before</p>', get_post( $item->get_id() )->post_content );
		$this->assertSame( '<p>Before</p>', get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
		$this->assertSame( 'yes', get_post_meta( $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor', true ) );
	}

	public function test_duplication_preserves_the_marker_with_the_copied_description() {
		[ $collection, $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Rich copy</p>', true )->get_status() );

		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collection/' . $collection->get_id() . '/items/' . $item->get_id() . '/duplicate' );
		$request->set_body( wp_json_encode( [ 'copies' => 1 ] ) );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 201, $response->get_status() );
		$copy_id = $response->get_data()['items'][0]['id'];
		$this->assertSame( '<p>Rich copy</p>', get_post( $copy_id )->post_content );
		$this->assertSame( 'yes', get_post_meta( $copy_id, '_tainacan_core_description_saved_with_rich_text_editor', true ) );
	}

	public function test_mixed_legacy_description_has_single_links_and_preserves_special_text() {
		$value = '<p>Existing</p>' . "\nLoose line\n" . '<a href="https://example.org">ready</a>' . "\nVisit https://example.net/path?x=1&y=2.";
		[ , $item, $metadatum ] = $this->create_item_with_core_description( $value );
		$data = $this->read_description( $item, $metadatum )->get_data();
		$this->assertSame( get_post( $item->get_id() )->post_content, $data['value'] );
		$this->assertStringContainsString( '&amp;y=2', $data['value'] );
		$this->assertSame( 2, substr_count( $data['value_for_rich_text_editor'], '<a ' ) );
		$this->assertSame( 1, substr_count( $data['value_for_rich_text_editor'], '<p>Existing</p>' ) );
		$this->assertStringNotContainsString( '<a href="https://example.org"><a ', $data['value_for_rich_text_editor'] );
	}

	public function test_item_rest_update_replaces_description_without_retaining_rich_marker() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Before</p>', true )->get_status() );

		$request = new \WP_REST_Request( 'PUT', $this->namespace . '/items/' . $item->get_id() );
		$request->set_body( wp_json_encode( [ 'description' => "Outside\n\nEditor" ] ) );
		$request->set_header( 'Content-Type', 'application/json' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor' ) );
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
		$this->assertSame( 401, $this->save_description( $item, $metadatum, '<p>Denied</p>', true )->get_status() );
		$this->assertSame( 'Public', get_post( $item->get_id() )->post_content );
	}

	public function test_unsafe_markup_is_sanitized_in_both_editor_modes() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$payload = '<p>Safe</p><img src=x onerror="alert(1)"><a href="javascript:alert(1)">bad</a>';

		foreach ( [ true, false ] as $mode ) {
			$this->assertSame( 200, $this->save_description( $item, $metadatum, $payload, $mode )->get_status() );
			$html = get_post( $item->get_id() )->post_content;
			$this->assertStringNotContainsString( 'onerror', $html );
			$this->assertStringNotContainsString( 'javascript:', $html );
			$this->assertStringContainsString( 'Safe', $html );
		}
	}

	public function test_later_plain_save_wins_with_its_own_marker() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>First tab</p>', true )->get_status() );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, "Second tab\n\nPlain", false )->get_status() );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor' ) );
		$this->assertSame( "Second tab\n\nPlain", get_post( $item->get_id() )->post_content );
		$this->assertSame( "Second tab\n\nPlain", get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_failed_marker_removal_restores_direct_item_description() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->enable_rich_text_editor( $metadatum );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '<p>Before</p>', true )->get_status() );

		$block_marker_delete = static function ( $check, $object_id, $meta_key ) use ( $item ) {
			if ( (int) $object_id === $item->get_id() && $meta_key === '_tainacan_core_description_saved_with_rich_text_editor' ) {
				return true;
			}
			return $check;
		};
		add_filter( 'delete_post_metadata', $block_marker_delete, 10, 3 );
		try {
			$request = new \WP_REST_Request( 'PUT', $this->namespace . '/items/' . $item->get_id() );
			$request->set_body( wp_json_encode( [ 'description' => 'Should roll back' ] ) );
			$request->set_header( 'Content-Type', 'application/json' );
			$response = $this->server->dispatch( $request );
		} finally {
			remove_filter( 'delete_post_metadata', $block_marker_delete, 10 );
		}

		$this->assertSame( 500, $response->get_status() );
		$this->assertSame( '<p>Before</p>', get_post( $item->get_id() )->post_content );
		$this->assertSame( '<p>Before</p>', get_post_meta( $item->get_id(), $metadatum->get_id(), true ) );
		$this->assertSame( 'yes', get_post_meta( $item->get_id(), '_tainacan_core_description_saved_with_rich_text_editor', true ) );
	}

	public function test_empty_description_does_not_create_a_queryable_empty_copy() {
		[ , $item, $metadatum ] = $this->create_item_with_core_description( 'Before' );
		$this->assertSame( 200, $this->save_description( $item, $metadatum, '', false )->get_status() );
		$this->assertSame( '', get_post( $item->get_id() )->post_content );
		$this->assertFalse( metadata_exists( 'post', $item->get_id(), $metadatum->get_id() ) );
		$data = $this->read_description( $item, $metadatum )->get_data();
		$this->assertSame( '', $data['value_as_html'] );
		$this->assertSame( '', $data['value_for_rich_text_editor'] );
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
