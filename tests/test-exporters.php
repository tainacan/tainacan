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
}
