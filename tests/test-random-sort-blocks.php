<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_Random_Sort_Blocks extends TAINACAN_UnitApiTestCase {

	public function test_api_fetch_items_with_orderby_rand() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'   => 'Random Sort Test Collection',
				'status' => 'publish'
			],
			true
		);

		for ( $i = 0; $i < 3; $i++ ) {
			$this->tainacan_entity_factory->create_entity(
				'item',
				[
					'title'      => "Random Sort Item $i",
					'collection' => $collection,
					'status'     => 'publish'
				],
				true
			);
		}

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/collection/' . $collection->get_id() . '/items'
		);
		$request->set_query_params( [ 'orderby' => 'rand' ] );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 3, $data['items'] );
	}
}
