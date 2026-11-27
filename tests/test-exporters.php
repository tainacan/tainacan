<?php

namespace Tainacan\Tests;

/**
 * @group exporter
 */
class TAINACAN_Exporters extends TAINACAN_UnitTestCase {

	public function test_xlsx_exporter_is_registered_only_on_php_81_or_later() {
		$handler = \Tainacan\Exporter_Handler::get_instance();
		$xlsx = $handler->get_exporter( 'xlsx' );

		if ( version_compare( PHP_VERSION, '8.1', '>=' ) ) {
			$this->assertIsArray( $xlsx );
			$this->assertSame( '\Tainacan\Exporter\XLSX_Exporter', $xlsx['class_name'] );
		} else {
			$this->assertNull( $xlsx );
			$this->assertFalse( $handler->initialize_exporter( 'xlsx' ) );
		}
	}

	public function test_csv_exporter_is_always_registered() {
		$csv = \Tainacan\Exporter_Handler::get_instance()->get_exporter( 'csv' );

		$this->assertIsArray( $csv );
		$this->assertSame( 'csv', $csv['slug'] );
	}

	public function test_csv_export_keeps_separator_of_dates_with_list_formatting() {
		$upload_dir = wp_upload_dir();
		wp_mkdir_p( trailingslashit( $upload_dir['basedir'] ) . 'tainacan/exporter' );

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'CSV export collection',
				'status' => 'publish',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'            => 'Dates',
				'collection'      => $collection,
				'metadata_type'   => 'Tainacan\Metadata_Types\Date',
				'multiple'        => 'yes',
				'html_formatting' => 'list',
				'status'          => 'publish',
			),
			true,
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'CSV export item',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata( $item, $metadatum, array( '2020-01-01', '2021-06-15' ) );

		$exporter = new \Tainacan\Exporter\CSV(
			array(
				'collections' => array(
					array(
						'id'          => $collection->get_id(),
						'total_items' => 1,
					),
				),
			)
		);
		$exporter->process_collections();

		$files_method = new \ReflectionMethod( $exporter, 'get_output_files' );
		$files_method->setAccessible( true );
		$files = $files_method->invoke( $exporter );
		$file = reset( $files );
		$content = file_get_contents( $file['filename'] );

		$this->assertStringContainsString( '2020-01-01||2021-06-15', $content );
		$this->assertStringNotContainsString( '<li>', $content );
	}
}
