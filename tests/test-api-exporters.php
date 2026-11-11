<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Exporters_Controller extends TAINACAN_UnitApiTestCase {

	private function run_exporter( $slug, $options = [], $collections = [] ) {
		$Tainacan_Exporter_Handler = \Tainacan\Exporter_Handler::get_instance();
		$exporter = $Tainacan_Exporter_Handler->initialize_exporter( $slug );
		$exporter->set_options( $options );
		$exporter->set_collections( $collections );
		$Tainacan_Exporter_Handler->save_exporter_instance( $exporter );

		$request = new \WP_REST_Request( 'POST', $this->namespace . '/exporters/session/' . $exporter->get_id() . '/run' );
		return $this->server->dispatch( $request );
	}

	public function test_run_rejects_vocabulary_exporter_without_taxonomy() {
		$response = $this->run_exporter( 'vocabularycsv' );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The source taxonomy is required.', $response->get_data()['error_message'] );
	}

	public function test_run_rejects_vocabulary_exporter_with_unknown_taxonomy() {
		$response = $this->run_exporter( 'vocabularycsv', [ 'select_taxonomy' => 'tnc_tax_999999' ] );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The selected taxonomy was not found.', $response->get_data()['error_message'] );
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
		$response = $this->run_exporter( 'csv' );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The source collection is required.', $response->get_data()['error_message'] );
	}

	public function test_run_rejects_csv_exporter_with_empty_delimiter() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status' => 'publish'
			),
			true
		);

		$response = $this->run_exporter( 'csv', [ 'delimiter' => '' ], [ [ 'id' => $collection->get_id(), 'total_items' => 0 ] ] );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'The CSV delimiter must be a single character.', $response->get_data()['error_message'] );
	}
}
