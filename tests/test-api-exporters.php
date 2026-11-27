<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Exporters_Controller extends TAINACAN_UnitApiTestCase {

	private function count_bg_processes() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}tnc_bg_process" );
	}

	private function run_exporter( $slug, $options = [], $collections = [] ) {
		if ( 'xlsx' === $slug && version_compare( PHP_VERSION, '8.1', '<' ) ) {
			$this->markTestSkipped( 'PhpSpreadsheet 5.x requires PHP 8.1 or later.' );
		}

		$Tainacan_Exporter_Handler = \Tainacan\Exporter_Handler::get_instance();
		$exporter = $Tainacan_Exporter_Handler->initialize_exporter( $slug );
		$exporter->set_options( $options );
		$exporter->set_collections( $collections );
		$Tainacan_Exporter_Handler->save_exporter_instance( $exporter );

		$request = new \WP_REST_Request( 'POST', $this->namespace . '/exporters/session/' . $exporter->get_id() . '/run' );
		return $this->server->dispatch( $request );
	}

	private function create_collection_entry() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status' => 'publish'
			),
			true
		);

		return [ [ 'id' => $collection->get_id(), 'total_items' => 0 ] ];
	}

	public function test_run_rejects_vocabulary_exporter_without_taxonomy() {
		$before = $this->count_bg_processes();
		$response = $this->run_exporter( 'vocabularycsv' );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The source taxonomy is required.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_rejects_vocabulary_exporter_with_unknown_taxonomy() {
		$before = $this->count_bg_processes();
		$response = $this->run_exporter( 'vocabularycsv', [ 'select_taxonomy' => 'tnc_tax_999999' ] );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The selected taxonomy was not found.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_queues_vocabulary_exporter_with_taxonomy() {
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'genero',
				'status' => 'publish'
			),
			true
		);

		$response = $this->run_exporter( 'vocabularycsv', [ 'select_taxonomy' => $taxonomy->get_db_identifier() ] );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'bg_process_id', $response->get_data() );
	}

	public function test_run_rejects_csv_exporter_without_collection() {
		$before = $this->count_bg_processes();
		$response = $this->run_exporter( 'csv' );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The source collection is required.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_rejects_csv_exporter_with_empty_delimiter() {
		$collections = $this->create_collection_entry();
		$before = $this->count_bg_processes();

		$response = $this->run_exporter( 'csv', [ 'delimiter' => '' ], $collections );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The CSV delimiter must be a single character.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_rejects_csv_exporter_with_multibyte_delimiter() {
		$collections = $this->create_collection_entry();
		$before = $this->count_bg_processes();

		$response = $this->run_exporter( 'csv', [ 'delimiter' => ';;' ], $collections );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The CSV delimiter must be a single character.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_rejects_csv_exporter_with_invalid_enclosure() {
		$collections = $this->create_collection_entry();
		$before = $this->count_bg_processes();

		foreach ( [ '', '""' ] as $enclosure ) {
			$response = $this->run_exporter( 'csv', [ 'enclosure' => $enclosure ], $collections );

			$this->assertEquals( 400, $response->get_status() );
			$this->assertEquals( 'The enclosure must be a single character.', $response->get_data()['error_message'] );
			$this->assertEquals( $before, $this->count_bg_processes() );
		}
	}

	public function test_run_rejects_csv_exporter_with_matching_delimiter_and_enclosure() {
		$collections = $this->create_collection_entry();
		$before = $this->count_bg_processes();

		$response = $this->run_exporter( 'csv', [ 'delimiter' => ',', 'enclosure' => ',' ], $collections );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The delimiter and the enclosure must be different characters.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_rejects_xlsx_exporter_without_collection() {
		$before = $this->count_bg_processes();
		$response = $this->run_exporter( 'xlsx' );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The source collection is required.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_rejects_xlsx_exporter_with_invalid_delimiter() {
		$collections = $this->create_collection_entry();
		$before = $this->count_bg_processes();

		foreach ( [ '', ';;' ] as $delimiter ) {
			$response = $this->run_exporter( 'xlsx', [ 'delimiter' => $delimiter ], $collections );

			$this->assertEquals( 400, $response->get_status() );
			$this->assertEquals( 'The compound children delimiter must be a single character.', $response->get_data()['error_message'] );
			$this->assertEquals( $before, $this->count_bg_processes() );
		}
	}

	public function test_run_rejects_xlsx_exporter_with_invalid_enclosure() {
		$collections = $this->create_collection_entry();
		$before = $this->count_bg_processes();

		$response = $this->run_exporter( 'xlsx', [ 'enclosure' => '' ], $collections );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The enclosure must be a single character.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}

	public function test_run_rejects_xlsx_exporter_with_matching_delimiter_and_enclosure() {
		$collections = $this->create_collection_entry();
		$before = $this->count_bg_processes();

		$response = $this->run_exporter( 'xlsx', [ 'delimiter' => ',', 'enclosure' => ',' ], $collections );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The delimiter and the enclosure must be different characters.', $response->get_data()['error_message'] );
		$this->assertEquals( $before, $this->count_bg_processes() );
	}
}
