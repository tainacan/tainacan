<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Filter_Types_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_filter_types(){

		$ftype_request = new \WP_REST_Request('GET', $this->namespace . '/filter-types');

		$ftype_response = $this->server->dispatch($ftype_request);

		$data = $ftype_response->get_data();

		global $Tainacan_Filters;

		$filter_types = $Tainacan_Filters->fetch_filter_types('NAME');

		$this->assertEquals(count($filter_types), count($data));

		foreach ($data as $ftype){
			$this->assertContains($ftype['name'], $filter_types);
		}
	}
}

?>