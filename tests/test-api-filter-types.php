<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Filter_Types_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_filter_types() {

		$ftype_request = new \WP_REST_Request('GET', $this->namespace . '/filter-types');

		$ftype_response = $this->server->dispatch($ftype_request);

		$data = $ftype_response->get_data();

		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

		$filter_types = $Tainacan_Filters->fetch_filter_types( );

		$this->assertEquals(count($filter_types), count($data));

		foreach ($data as $ftype) {
			$this->assertContains($ftype['className'], $filter_types);
		}
	}
}

?>