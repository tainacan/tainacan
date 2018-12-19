<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Sample test case.
 */
class BGProcess extends TAINACAN_UnitApiTestCase {

	function test_table() {
		
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'tnc_bg_process';
		
		
		$table_exists = $wpdb->get_var(  "SHOW TABLES LIKE '".$table_name."'"  );
		
		$this->assertNotEquals(null, $table_exists);
		
		$column_exists = $wpdb->get_var(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'output'"  );
		
		$this->assertEquals('output', $column_exists, 'BG processes table misconfigured');
		
	}
	
	


}