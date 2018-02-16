<?php

namespace Tainacan\Tests;

/**
 * @group api_item_meta
 */
class TAINACAN_REST_Item_Metadata_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_item_metadata_in_a_collection(){
		global $Tainacan_Fields, $Tainacan_Item_Metadata;
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'testeItemMetadata',
				'description' => 'No description',
			),
			true
		);
		
		$type = $this->tainacan_field_factory->create_field('text');
		
		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'              => 'teste_metadado',
				'description'       => 'descricao',
				'collection'        => $collection,
				'field_type' => $type
			),
			true
		);
		
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true
		);

		$item__metadata_json = json_encode([
			'values'       => 'TestValues',
		]);

		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $field->get_id() );
		$request->set_body($item__metadata_json);

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();
		
		$this->assertEquals(9 , $data['item']['id']);
		$this->assertEquals('TestValues', $data['value']);
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata' );
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		
		$this->assertEquals( 'TestValues', $data['value'] );
	}

	
}

?>