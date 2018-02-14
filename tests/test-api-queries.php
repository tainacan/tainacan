<?php

namespace Tainacan\Tests;

/**
 * @group queries
 * **/
class TAINACAN_REST_Queries extends TAINACAN_UnitApiTestCase {

	public function test_collections_queries(){
		$this->tainacan_entity_factory->create_entity(
			'collection',
			[],
			true
		);
	}
}

?>