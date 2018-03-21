<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

class CoreFieldTypes extends TAINACAN_UnitApiTestCase {


	function test_core_field_types() {

		global $Tainacan_Item_Metadata, $Tainacan_Items;

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'test',
			),
			true
		);

		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'metadado',
				'description' => 'title',
				'collection'  => $collection,
				'field_type'  => 'Tainacan\Field_Types\Core_Title'
			),
			true
		);

		$fieldDescription = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'        => 'metadado_desc',
				'description' => 'description',
				'collection'  => $collection,
				'field_type'  => 'Tainacan\Field_Types\Core_Description'
			),
			true
		);


		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item test',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true
		);


		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $i, $field );
		$item_metadata->set_value( 'changed title' );
		$item_metadata->validate();

		$Tainacan_Item_Metadata->insert( $item_metadata );

		$checkItem = $Tainacan_Items->fetch( $i->get_id() );

		$this->assertEquals( 'changed title', $checkItem->get_title() );

		$check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $checkItem, $field );
		$this->assertEquals( 'changed title', $check_item_metadata->get_value() );


		// description
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $i, $fieldDescription );
		$item_metadata->set_value( 'changed description' );
		$item_metadata->validate();

		$Tainacan_Item_Metadata->insert( $item_metadata );

		$checkItem = $Tainacan_Items->fetch( $i->get_id() );

		$this->assertEquals( 'changed description', $checkItem->get_description() );

		$check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity( $checkItem, $fieldDescription );
		$this->assertEquals( 'changed description', $check_item_metadata->get_value() );

	}

	protected function get_fields( $collection ) {
		$request_fields = new \WP_REST_Request( 'GET', $this->namespace . '/collection/' . $collection->get_id() . '/fields' );

		$response = $this->server->dispatch( $request_fields );

		$data = $response->get_data();

		return $data;
	}

	public function test_update_core_fields_status() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'test',
			),
			true
		);

		// GET
		$data1 = self::get_fields( $collection );

		// PATCH
		$field_id = $data1[0]['id'];

		$body = json_encode( array(
			'status' => 'private'
		) );

		$request = new \WP_REST_Request( 'PATCH', $this->namespace . '/collection/' . $collection->get_id() . '/fields/' . $field_id );
		$request->set_body( $body );

		$response = $this->server->dispatch( $request );

		$data2 = $response->get_data();
		$this->assertEquals('private', $data2['status']);

		// GET
		$data3     = self::get_fields( $collection );

		$this->assertCount(2, $data3);

		$statuses = [$data3[0]['status'], $data3[1]['status']];

		$this->assertContains('private', $statuses);
		$this->assertContains('publish', $statuses);

	}

}