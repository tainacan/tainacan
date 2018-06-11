<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadata_Types_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_metadata_types(){

		$ftype_request = new \WP_REST_Request('GET', $this->namespace . '/metadata-types');

		$ftype_response = $this->server->dispatch($ftype_request);

		$data = $ftype_response->get_data();

		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		$metadata_types = $Tainacan_Metadata->fetch_metadata_types('NAME');

		$this->assertEquals(count($metadata_types), count($data));

		foreach ($data as $ftype){
			$this->assertContains($ftype['name'], $metadata_types);
		}
	}
}

?>