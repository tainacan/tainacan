<?php

namespace Tainacan\Tests;

/**
 * Extra mapper fields must be usable as export columns, not as array keys.
 */
class TAINACAN_Exporter_Mapping extends TAINACAN_UnitTestCase {

	private function create_mapped_collection_with_extra_field() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Mapped export collection',
				'status' => 'publish',
			),
			true
		);

		$language = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'            => 'Language',
				'collection'      => $collection,
				'metadata_type'   => 'Tainacan\Metadata_Types\Text',
				'exposer_mapping' => array(
					'dublin-core' => 'dc:language',
				),
			),
			true,
			true
		);

		$citation = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'            => 'Citation',
				'collection'      => $collection,
				'metadata_type'   => 'Tainacan\Metadata_Types\Text',
				'exposer_mapping' => array(
					'dublin-core' => array(
						'slug'  => 'bibliographic-citation',
						'uri'   => 'http://purl.org/dc/terms/bibliographicCitation',
						'label' => 'Bibliographic Citation',
					),
				),
			),
			true,
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'Mapped export item',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata( $item, $language, 'pt-BR' );
		$this->tainacan_item_metadata_factory->create_item_metadata( $item, $citation, 'Doe, Jane. 2026.' );

		return array( $collection, $item );
	}

	private function create_mapped_csv_exporter( $collection ) {
		return new \Tainacan\Exporter\CSV(
			array(
				'mapping_selected' => 'dublin-core',
				'collections'      => array(
					array(
						'id' => $collection->get_id(),
					),
				),
			)
		);
	}

	public function test_mapped_export_includes_extra_mapper_fields() {
		list( $collection, $item ) = $this->create_mapped_collection_with_extra_field();
		$exporter = $this->create_mapped_csv_exporter( $collection );

		$slugs_method = new \ReflectionMethod( $exporter, 'get_mapped_metadata_slugs' );
		$slugs_method->setAccessible( true );
		$slugs = $slugs_method->invoke( $exporter );

		$this->assertContains( 'dc:language', $slugs );
		$this->assertContains( 'bibliographic-citation', $slugs );

		$map_method = new \ReflectionMethod( $exporter, 'map_item_metadata' );
		$map_method->setAccessible( true );
		$mapped = $map_method->invoke( $exporter, $item );

		$this->assertArrayHasKey( 'dc:language', $mapped );
		$this->assertArrayHasKey( 'bibliographic-citation', $mapped );
		$this->assertInstanceOf( \Tainacan\Entities\Item_Metadata_Entity::class, $mapped['dc:language'] );
		$this->assertInstanceOf( \Tainacan\Entities\Item_Metadata_Entity::class, $mapped['bibliographic-citation'] );
		$this->assertEquals( 'pt-BR', $mapped['dc:language']->get_value() );
		$this->assertEquals( 'Doe, Jane. 2026.', $mapped['bibliographic-citation']->get_value() );
	}

	public function test_mapped_csv_header_appends_extra_field() {
		list( $collection ) = $this->create_mapped_collection_with_extra_field();
		$exporter = $this->create_mapped_csv_exporter( $collection );

		$slugs_method = new \ReflectionMethod( $exporter, 'get_mapped_metadata_slugs' );
		$slugs_method->setAccessible( true );
		$slugs = $slugs_method->invoke( $exporter );

		$dc = new \Tainacan\Mappers\Dublin_Core();
		$this->assertCount( count( $dc->metadata ) + 1, $slugs );
		$this->assertContains( 'dc:language', $slugs );
		$this->assertContains( 'bibliographic-citation', $slugs );
		$this->assertSame( 'bibliographic-citation', end( $slugs ) );
	}
}
