<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Field_Types_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_field_types(){

		$ftype_request = new \WP_REST_Request('GET', $this->namespace . '/field-types');

		$ftype_response = $this->server->dispatch($ftype_request);

		$data = $ftype_response->get_data();

		$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

		$field_types = $Tainacan_Fields->fetch_field_types('NAME');

		$this->assertEquals(count($field_types), count($data));

		foreach ($data as $ftype){
			$this->assertContains($ftype['name'], $field_types);
		}
	}
}

?>