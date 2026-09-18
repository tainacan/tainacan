<?php

namespace Tainacan\Tests;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Regression coverage for the PhpSpreadsheet-backed XLSX exporter.
 *
 * @group exporter
 * @package Test_Tainacan
 */
class TAINACAN_XLSX_Exporter extends TAINACAN_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
			$this->markTestSkipped( 'PhpSpreadsheet 5.x requires PHP 8.1 or later.' );
		}

		$upload_dir = wp_upload_dir();
		wp_mkdir_p( trailingslashit( $upload_dir['basedir'] ) . 'tainacan/exporter' );
	}

	private function create_collection_with_item( $title = 'XLSX export item', $value = 'Spreadsheet value' ) {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'XLSX export collection',
				'status' => 'publish',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Caption',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish',
			),
			true,
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => $title,
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata( $item, $metadatum, $value );

		return array( $collection, $item, $metadatum );
	}

	private function create_xlsx_exporter( $collection, $total_items = 1 ) {
		$exporter = new \Tainacan\Exporter\XLSX_Exporter();
		$exporter->add_collection(
			array(
				'id'          => $collection->get_id(),
				'total_items' => $total_items,
			)
		);

		return $exporter;
	}

	private function load_exported_sheet( $path ) {
		$this->assertFileExists( $path );

		$reader = IOFactory::createReader( 'Xlsx' );
		$reader->setReadDataOnly( true );
		$spreadsheet = $reader->load( $path );
		$values = $spreadsheet->getActiveSheet()->toArray( null, true, true, true );
		$spreadsheet->disconnectWorksheets();

		return $values;
	}

	private function flatten_rows( $rows ) {
		$flattened = array();
		foreach ( $rows as $row ) {
			$flattened = array_merge( $flattened, array_map( 'strval', array_values( $row ) ) );
		}
		return $flattened;
	}

	public function test_xlsx_export_round_trip_preserves_item_values() {
		list( $collection, $item ) = $this->create_collection_with_item( 'Painted vase', 'Blue glaze' );
		$exporter = $this->create_xlsx_exporter( $collection, 1 );

		$result = $exporter->process_collections();
		$this->assertFalse( $result, 'A single-item export should finish in one batch.' );

		$path = $exporter->get_file_path();
		$rows = $this->load_exported_sheet( $path );

		$this->assertNotEmpty( $rows );
		$header = array_shift( $rows );
		$header_blob = implode( ' ', $header );
		$this->assertContains( 'special_item_id', $header );
		$this->assertStringContainsString( 'Title', $header_blob );

		$flattened = $this->flatten_rows( $rows );
		$this->assertContains( (string) $item->get_id(), $flattened );
		$this->assertContains( 'Painted vase', $flattened );
		$this->assertContains( 'Blue glaze', $flattened );
	}

	public function test_xlsx_writer_resumes_from_existing_file() {
		list( $collection ) = $this->create_collection_with_item( 'First row item', 'First value' );
		$exporter = $this->create_xlsx_exporter( $collection, 1 );
		$exporter->process_collections();

		$path = $exporter->get_file_path();
		$first_pass_rows = $this->load_exported_sheet( $path );
		$this->assertGreaterThanOrEqual( 2, count( $first_pass_rows ) );

		$exporter->initialize_writer();
		$exporter->addRowToSheet( array( 'resume-marker', 'second-batch' ) );
		$exporter->finalize_writer();

		$resumed_rows = $this->load_exported_sheet( $path );
		$this->assertGreaterThan( count( $first_pass_rows ), count( $resumed_rows ) );

		$flattened = $this->flatten_rows( $resumed_rows );
		$this->assertContains( 'resume-marker', $flattened );
		$this->assertContains( 'second-batch', $flattened );
	}
}
