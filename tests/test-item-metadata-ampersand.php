<?php

namespace Tainacan\Tests;

use Tainacan\Entities\Item_Metadata_Entity;
use Tainacan\Repositories;

/**
 * Item metadata values containing "&" are stored HTML-encoded ("&amp;") by wp_kses().
 * Plain-text outputs must return the "&" as entered, while the stored value and the
 * HTML outputs stay unchanged (issue #1286).
 *
 * @group api
 */
class Item_Metadata_Ampersand extends TAINACAN_UnitApiTestCase {

	private $amp_collection;
	private $amp_item;

	public function setUp(): void {
		parent::setUp();
		$this->amp_collection = $this->tainacan_entity_factory->create_entity( 'collection', [
			'name' => 'Ampersand collection',
			'status' => 'publish',
		], true );
		$this->amp_item = $this->tainacan_entity_factory->create_entity( 'item', [
			'title' => 'Ampersand item',
			'description' => 'Ampersand description',
			'collection' => $this->amp_collection,
			'status' => 'publish',
		], true );
	}

	private function create_metadatum( $type, $extra = [] ) {
		return $this->tainacan_entity_factory->create_entity( 'metadatum', array_merge( [
			'name' => 'Ampersand ' . $type . ' ' . uniqid(),
			'collection_id' => $this->amp_collection->get_id(),
			'metadata_type' => 'Tainacan\Metadata_Types\\' . $type,
			'status' => 'publish',
		], $extra ), true );
	}

	private function save_value( $metadatum, $value ) {
		$item_metadata = $this->tainacan_item_metadata_factory->create_item_metadata( $this->amp_item, $metadatum, $value );
		$this->assertInstanceOf( Item_Metadata_Entity::class, $item_metadata );
		return new Item_Metadata_Entity( $this->amp_item, $metadatum );
	}

	private function read_rest( $metadatum ) {
		$request = new \WP_REST_Request( 'GET', $this->namespace . '/item/' . $this->amp_item->get_id() . '/metadata/' . $metadatum->get_id() );
		$request->set_param( 'context', 'edit' );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 200, $response->get_status() );
		return $response->get_data();
	}

	public function test_rest_value_returns_ampersand_for_text_metadata() {
		$text = $this->create_metadatum( 'Text' );
		$this->save_value( $text, 'Smith & Jones' );
		$this->assertSame( 'Smith & Jones', $this->read_rest( $text )['value'] );

		$url = $this->create_metadatum( 'Text' );
		$this->save_value( $url, 'https://example.org/page?p=1&id=2' );
		$this->assertSame( 'https://example.org/page?p=1&id=2', $this->read_rest( $url )['value'] );

		$textarea = $this->create_metadatum( 'Textarea' );
		$this->save_value( $textarea, 'Humb. & Bonpl.' );
		$this->assertSame( 'Humb. & Bonpl.', $this->read_rest( $textarea )['value'] );

		$multiple = $this->create_metadatum( 'Text', [ 'multiple' => 'yes' ] );
		$this->save_value( $multiple, [ 'A & B', 'C & D' ] );
		$this->assertSame( [ 'A & B', 'C & D' ], $this->read_rest( $multiple )['value'] );
	}

	public function test_rest_value_returns_ampersand_for_core_metadata() {
		$title = $this->amp_collection->get_core_title_metadatum();
		$description = $this->amp_collection->get_core_description_metadatum();
		$this->save_value( $title, 'Smith & Jones' );
		$this->save_value( $description, 'Humb. & Bonpl.' );

		$this->assertSame( 'Smith & Jones', $this->read_rest( $title )['value'] );
		$this->assertSame( 'Humb. & Bonpl.', $this->read_rest( $description )['value'] );
	}

	public function test_rest_value_returns_ampersand_for_compound_children() {
		$compound = $this->create_metadatum( 'Compound' );
		$child = $this->create_metadatum( 'Text', [ 'parent' => $compound->get_id() ] );

		$child_metadata = new Item_Metadata_Entity( $this->amp_item, $child );
		$child_metadata->set_value( 'Smith & Jones' );
		$this->assertTrue( $child_metadata->validate() );
		Repositories\Item_Metadata::get_instance()->insert( $child_metadata );

		$values = $this->read_rest( $compound )['value'];
		$child_values = array_values( array_filter( $values, function( $v ) use ( $child ) {
			return is_array( $v ) && isset( $v['metadatum_id'] ) && (int) $v['metadatum_id'] === (int) $child->get_id();
		} ) );
		$this->assertCount( 1, $child_values );
		$this->assertSame( 'Smith & Jones', $child_values[0]['value'] );
		$this->assertSame( 'Smith &amp; Jones', $child_values[0]['value_as_html'] );
	}

	public function test_rich_text_value_stays_html() {
		update_option( 'tainacan_option_allow_rich_text_editor', true );
		$rich = $this->create_metadatum( 'Textarea', [ 'metadata_type_options' => [ 'use_rich_text_editor' => 'yes' ] ] );
		$this->save_value( $rich, '<p>Smith &amp; Jones</p>' );

		$data = $this->read_rest( $rich );
		$this->assertSame( '<p>Smith &amp; Jones</p>', $data['value'] );
		$this->assertSame( 'Smith & Jones', trim( $data['value_as_string'] ) );
	}

	public function test_rich_text_option_without_site_wide_setting_returns_plain_text() {
		update_option( 'tainacan_option_allow_rich_text_editor', false );
		$rich = $this->create_metadatum( 'Textarea', [ 'metadata_type_options' => [ 'use_rich_text_editor' => 'yes' ] ] );
		$this->save_value( $rich, 'Smith & Jones' );

		// The admin shows a plain textarea when the site-wide setting is off.
		$this->assertSame( 'Smith & Jones', $this->read_rest( $rich )['value'] );
	}

	public function test_greater_than_is_returned_as_typed() {
		$metadatum = $this->create_metadatum( 'Text' );
		$item_metadata = $this->save_value( $metadatum, 'a > b' );
		$this->assertSame( 'a &gt; b', get_post_meta( $this->amp_item->get_id(), $metadatum->get_id(), true ) );

		$data = $this->read_rest( $metadatum );
		$this->assertSame( 'a > b', $data['value'] );
		$this->assertSame( 'a > b', $data['value_as_string'] );
		$this->assertSame( 'a &gt; b', $item_metadata->get_value_as_html() );
	}

	private function create_related_item( $text_value, $title = 'Related item' ) {
		$related_collection = $this->tainacan_entity_factory->create_entity( 'collection', [
			'name' => 'Related collection ' . uniqid(),
			'status' => 'publish',
		], true );
		$related_text = $this->tainacan_entity_factory->create_entity( 'metadatum', [
			'name' => 'Related text ' . uniqid(),
			'collection_id' => $related_collection->get_id(),
			'metadata_type' => 'Tainacan\Metadata_Types\Text',
			'status' => 'publish',
		], true );
		$related_item = $this->tainacan_entity_factory->create_entity( 'item', [
			'title' => $title,
			'collection' => $related_collection,
			'status' => 'publish',
		], true );
		$this->tainacan_item_metadata_factory->create_item_metadata( $related_item, $related_text, $text_value );

		$relationship = $this->create_metadatum( 'Relationship', [
			'metadata_type_options' => [
				'collection_id' => $related_collection->get_id(),
				'search' => $related_text->get_id(),
			],
		] );

		return [ $related_collection, $related_item, $relationship ];
	}

	public function test_relationship_link_label_keeps_ampersand_encoded_in_html() {
		[ , $related_item, $relationship ] = $this->create_related_item( 'Smith&notes' );
		$item_metadata = $this->save_value( $relationship, $related_item->get_id() );

		$html = $item_metadata->get_value_as_html();
		$this->assertStringContainsString( '>Smith&amp;notes</a>', $html );
		$this->assertStringNotContainsString( '>Smith&notes</a>', $html );
	}

	public function test_relationship_facet_label_is_decoded() {
		[ $related_collection, $related_item, $relationship ] = $this->create_related_item( 'Any value' );
		$this->tainacan_item_metadata_factory->create_item_metadata( $related_item, $related_collection->get_core_title_metadatum(), 'Smith & Jones' );
		// Precondition: the item title is stored encoded, like every metadata value.
		$this->assertSame( 'Smith &amp; Jones', get_post( $related_item->get_id() )->post_title );
		$this->tainacan_item_metadata_factory->create_item_metadata( $this->amp_item, $relationship, $related_item->get_id() );

		$response = Repositories\Metadata::get_instance()->fetch_all_metadatum_values( $relationship->get_id(), [
			'collection_id' => $this->amp_collection->get_id(),
		] );

		$this->assertCount( 1, $response['values'] );
		$this->assertSame( 'Smith & Jones', $response['values'][0]['label'] );
	}

	public function test_taxonomy_facet_hierarchy_path_is_decoded() {
		$taxonomy = $this->tainacan_entity_factory->create_entity( 'taxonomy', [
			'name' => 'Ampersand taxonomy',
			'allow_insert' => 'yes',
			'status' => 'publish',
		], true );
		$root = $this->tainacan_entity_factory->create_entity( 'term', [
			'taxonomy' => $taxonomy->get_db_identifier(),
			'name' => 'R&D',
		], true );
		$child = $this->tainacan_entity_factory->create_entity( 'term', [
			'taxonomy' => $taxonomy->get_db_identifier(),
			'name' => 'Labs',
			'parent' => $root->get_id(),
		], true );
		// Precondition: WordPress stores term names encoded.
		$this->assertSame( 'R&amp;D', get_term( $root->get_id() )->name );

		$metadatum = $this->create_metadatum( 'Taxonomy', [
			'metadata_type_options' => [ 'taxonomy_id' => $taxonomy->get_id(), 'allow_new_terms' => 'yes' ],
			'multiple' => 'yes',
		] );
		$this->tainacan_item_metadata_factory->create_item_metadata( $this->amp_item, $metadatum, [ $child->get_id() ] );

		$response = Repositories\Metadata::get_instance()->fetch_all_metadatum_values( $metadatum->get_id(), [
			'parent_id' => $root->get_id(),
		] );

		$this->assertCount( 1, $response['values'] );
		$this->assertSame( 'R&D>', $response['values'][0]['hierarchy_path'] );
	}

	public function test_oai_dublin_core_title_is_decoded_and_not_duplicated() {
		$this->save_value( $this->amp_collection->get_core_title_metadatum(), 'Smith & Jones' );
		$this->assertSame( 'Smith &amp; Jones', get_post( $this->amp_item->get_id() )->post_title );
		$mapped = $this->create_metadatum( 'Text', [ 'exposer_mapping' => [ 'dublin-core' => 'dc:title' ] ] );
		$this->save_value( $mapped, 'Smith & Jones' );

		$request = new \WP_REST_Request( 'GET', $this->namespace . '/oai' );
		$request->set_param( 'verb', 'ListRecords' );
		$request->set_param( 'metadataPrefix', 'oai_dc' );
		$request->set_param( 'set', (string) $this->amp_collection->get_id() );
		$body = $this->server->dispatch( $request )->get_data();

		// The XML writer escapes "&" once, so the harvester reads "Smith & Jones".
		$this->assertStringContainsString( '<dc:title>Smith &amp; Jones</dc:title>', $body );
		$this->assertStringNotContainsString( '&amp;amp;', $body );
		// The core title and the metadatum mapped to dc:title carry the same text, so they are de-duplicated.
		$this->assertSame( 1, substr_count( $body, '<dc:title>' ) );
	}

	public function test_stored_value_and_html_output_are_unchanged() {
		$metadatum = $this->create_metadatum( 'Text' );
		$item_metadata = $this->save_value( $metadatum, 'Smith & Jones' );
		$this->assertSame( 'Smith &amp; Jones', get_post_meta( $this->amp_item->get_id(), $metadatum->get_id(), true ) );
		$this->assertSame( 'Smith &amp; Jones', $item_metadata->get_value() );
		$this->assertSame( 'Smith &amp; Jones', $item_metadata->get_value_as_html() );

		$legacy = $this->create_metadatum( 'Text' );
		$legacy_metadata = $this->save_value( $legacy, 'Smith&notes' );
		$this->assertSame( 'Smith&amp;notes', $legacy_metadata->get_value_as_html() );
		$this->assertSame( 'Smith&amp;notes', $this->read_rest( $legacy )['value_as_html'] );
	}

	public function test_value_as_string_returns_ampersand() {
		$metadatum = $this->create_metadatum( 'Text' );
		$item_metadata = $this->save_value( $metadatum, 'Smith & Jones' );
		$this->assertSame( 'Smith & Jones', $item_metadata->get_value_as_string() );
		$this->assertSame( 'Smith & Jones', $this->read_rest( $metadatum )['value_as_string'] );

		$url = $this->create_metadatum( 'Text' );
		$url_metadata = $this->save_value( $url, 'https://example.org/page?p=1&id=2' );
		$this->assertSame( 'https://example.org/page?p=1&id=2', $url_metadata->get_value_as_string() );

		$url_type = $this->create_metadatum( 'URL' );
		$url_type_metadata = $this->save_value( $url_type, 'https://example.org/page?p=1&id=2' );
		$this->assertSame( 'https://example.org/page?p=1&id=2', $url_type_metadata->get_value_as_string() );
	}

	public function test_escaped_markup_is_never_decoded() {
		$metadatum = $this->create_metadatum( 'Text' );
		$item_metadata = $this->save_value( $metadatum, '&lt;script&gt;alert(1)&lt;/script&gt;' );
		$data = $this->read_rest( $metadatum );

		// "&gt;" may be decoded in plain-text outputs, but "&lt;" never is, so no tag can open.
		foreach ( [ $data['value'], $data['value_as_string'], $data['value_as_html'], $item_metadata->get_value_as_string() ] as $output ) {
			$this->assertStringNotContainsString( '<', $output );
			$this->assertStringContainsString( '&lt;script', $output );
		}
		$this->assertStringContainsString( '&lt;script&gt;', $data['value_as_html'] );
	}

	public function test_values_already_stored_encoded_are_decoded_on_output() {
		$metadatum = $this->create_metadatum( 'Text' );
		update_post_meta( $this->amp_item->get_id(), $metadatum->get_id(), 'Smith &amp; Jones' );

		$data = $this->read_rest( $metadatum );
		$this->assertSame( 'Smith & Jones', $data['value'] );
		$this->assertSame( 'Smith & Jones', $data['value_as_string'] );
	}

	public function test_facet_label_is_decoded_but_value_matches_stored_data() {
		$metadatum = $this->create_metadatum( 'Text' );
		$this->save_value( $metadatum, 'Smith & Jones' );

		$response = Repositories\Metadata::get_instance()->fetch_all_metadatum_values( $metadatum->get_id(), [
			'collection_id' => $this->amp_collection->get_id(),
			'count_items' => true,
		] );

		$this->assertCount( 1, $response['values'] );
		$facet = $response['values'][0];
		$this->assertSame( 'Smith & Jones', $facet['label'] );
		$this->assertSame( 'Smith &amp; Jones', $facet['value'] );
		$this->assertSame( 1, (int) $facet['total_items'] );
	}

	private function run_bulk_edit( $bulk_edit_data ) {
		$handler = \Tainacan\Generic_Process_Handler::get_instance();
		$process = $handler->initialize_generic_process( 'bulk_edit' );
		$process->create_bulk_edit( [
			'items_ids' => [ $this->amp_item->get_id() ],
			'collection_id' => $this->amp_collection->get_id(),
		] );
		$handler->save_process_instance( $process );
		$process->set_bulk_edit_data( $bulk_edit_data );

		$steps = 0;
		while ( false !== $process->run() && $steps < 100 ) {
			$steps++;
		}
	}

	public function test_bulk_edit_replace_matches_decoded_old_value() {
		$metadatum = $this->create_metadatum( 'Text' );
		$this->save_value( $metadatum, 'Smith & Jones' );

		$this->run_bulk_edit( [
			'method' => 'replace_value',
			'old_value' => 'Smith & Jones',
			'value' => 'Doe & Co',
			'metadatum_id' => $metadatum->get_id(),
		] );

		$this->assertSame( 'Doe &amp; Co', get_post_meta( $this->amp_item->get_id(), $metadatum->get_id(), true ) );
	}

	public function test_bulk_edit_remove_matches_decoded_value() {
		$metadatum = $this->create_metadatum( 'Text', [ 'multiple' => 'yes' ] );
		$this->save_value( $metadatum, [ 'A & B', 'C & D' ] );

		$this->run_bulk_edit( [
			'method' => 'remove_value',
			'old_value' => null,
			'value' => 'A & B',
			'metadatum_id' => $metadatum->get_id(),
		] );

		$this->assertSame( [ 'C &amp; D' ], get_post_meta( $this->amp_item->get_id(), $metadatum->get_id() ) );
	}
}
