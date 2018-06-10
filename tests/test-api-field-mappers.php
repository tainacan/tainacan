<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Field_Mappers_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_field_mappers(){

		$field_mapper_request = new \WP_REST_Request('GET', $this->namespace . '/field-mappers');

		$field_mapper_response = $this->server->dispatch($field_mapper_request);

		$data = $field_mapper_response->get_data();

		$Tainacan_Fields = \Tainacan\Exposers\Exposers::get_instance();

		$field_mappers = $Tainacan_Fields->get_mappers("OBJECT");

		$this->assertEquals(count($field_mappers), count($data));
		
	    for ($i = 0; $i < count($data); $i++) {
	        $this->assertEquals($field_mappers[$i]->slug, $data[$i]['slug']);
	        $this->assertEquals($field_mappers[$i]->name, $data[$i]['name']);
	        $this->assertEquals($field_mappers[$i]->allow_extra_fields, $data[$i]['allow_extra_fields']);
	        $this->assertEquals($field_mappers[$i]->context_url, $data[$i]['context_url']);
	        $this->assertEquals($field_mappers[$i]->metadata, $data[$i]['metadata']);
	        $this->assertEquals($field_mappers[$i]->prefix, $data[$i]['prefix']);
	        $this->assertEquals($field_mappers[$i]->sufix, $data[$i]['sufix']);
	        $this->assertEquals($field_mappers[$i]->header, $data[$i]['header']);
		}
	}
}

?>