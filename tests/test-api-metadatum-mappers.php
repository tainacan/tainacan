<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadatum_Mappers_Controller extends TAINACAN_UnitApiTestCase {

	public function test_get_metadatum_mappers(){

		$metadatum_mapper_request = new \WP_REST_Request('GET', $this->namespace . '/metadatum-mappers');

		$metadatum_mapper_response = $this->server->dispatch($metadatum_mapper_request);

		$data = $metadatum_mapper_response->get_data();

		$Tainacan_Metadata = \Tainacan\Exposers\Exposers::get_instance();

		$metadatum_mappers = $Tainacan_Metadata->get_mappers("OBJECT");

		$this->assertEquals(count($metadatum_mappers), count($data));
		
	    for ($i = 0; $i < count($data); $i++) {
	        $this->assertEquals($metadatum_mappers[$i]->slug, $data[$i]['slug']);
	        $this->assertEquals($metadatum_mappers[$i]->name, $data[$i]['name']);
	        $this->assertEquals($metadatum_mappers[$i]->allow_extra_metadata, $data[$i]['allow_extra_metadata']);
	        $this->assertEquals($metadatum_mappers[$i]->context_url, $data[$i]['context_url']);
	        $this->assertEquals($metadatum_mappers[$i]->metadata, $data[$i]['metadata']);
	        $this->assertEquals($metadatum_mappers[$i]->prefix, $data[$i]['prefix']);
	        $this->assertEquals($metadatum_mappers[$i]->sufix, $data[$i]['sufix']);
	        $this->assertEquals($metadatum_mappers[$i]->header, $data[$i]['header']);
		}
	}
}

?>