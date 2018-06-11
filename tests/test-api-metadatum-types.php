<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadatum_Types_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_metadatum_types(){

		$ftype_request = new \WP_REST_Request('GET', $this->namespace . '/metadatum-types');

		$ftype_response = $this->server->dispatch($ftype_request);

		$data = $ftype_response->get_data();

		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		$metadatum_types = $Tainacan_Metadata->fetch_metadatum_types('NAME');

		$this->assertEquals(count($metadatum_types), count($data));

		foreach ($data as $ftype){
			$this->assertContains($ftype['name'], $metadatum_types);
		}
	}
}

?>