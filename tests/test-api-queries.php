<?php

namespace Tainacan\Tests;

/**
 * @group queries
 * **/
class TAINACAN_REST_Queries extends TAINACAN_UnitApiTestCase {

	public function test_queries(){

		// Populate the database
		$collectionA = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'A',
				'description' => 'Collection A',
				'status'      => 'publish'
			],
			true
		);

		// Create Items
		$itemA1 = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title'       => 'Item A-1',
				'description' => 'Item in collection A',
				'status'      => 'publish',
				'collection'  => $collectionA
			],
			true
		);

		$itemA2 = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title'       => 'Item A-2',
				'description' => 'Item in collection A',
				'status'      => 'private',
				'collection'  => $collectionA
			],
			true
		);

		$itemA3 = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title'       => 'Item A-3',
				'description' => 'Item in collection A',
				'status'      => 'publish',
				'collection'  => $collectionA
			],
			true
		);

		// Create Metadata and Field Type

		$field_type = $this->tainacan_field_factory->create_field('text');

		$fieldA1 = $this->tainacan_entity_factory->create_entity(
			'field',
			[
				'name'        => 'Field A-1',
				'description' => 'Simple field in collection A',
				'status'      => 'publish',
				'collection'  => $collectionA,
				'field_type'  => $field_type
			],
			true
		);

		$fieldA2 = $this->tainacan_entity_factory->create_entity(
			'field',
			[
				'name'        => 'Field A-2',
				'description' => 'Simple field in collection A',
				'status'      => 'publish',
				'collection'  => $collectionA,
				'field_type'  => $field_type
			],
			true
		);

		$fieldA3 = $this->tainacan_entity_factory->create_entity(
			'field',
			[
				'name'        => 'Field A-3',
				'description' => 'Multiple field in a collection A',
				'status'      => 'publish',
				'collection'  => $collectionA,
				'field_type'  => $field_type,
				'multiple'    => 'yes'
			],
			true
		);

		// Create a Item Metadata
		$itemA1_metadata1 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA1, $fieldA1, 'E');
		$itemA1_metadata2 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA1, $fieldA2, 'X');
		$itemA1_metadata3 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA1, $fieldA3, ['Y', 'Z']);

		$itemA2_metadata1 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA2, $fieldA1, 'D');
		$itemA2_metadata2 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA2, $fieldA2, 'Q');
		$itemA2_metadata3 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA2, $fieldA3, ['R', 'S']);

		$itemA3_metadata1 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA3, $fieldA1, 'G');
		$itemA3_metadata2 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA3, $fieldA2, 'T');
		$itemA3_metadata3 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA3, $fieldA3, ['Q', 'V']);

		//

		$collectionB = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'B',
				'description' => 'Collection B',
				'status'      => 'publish'
			],
			true
		);

		$collectionC = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'C',
				'description' => 'Collection C',
				'status'      => 'private'
			],
			true
		);


		// Fetch a collection with a specific name
		$name_query = ['name' => 'B'];

		$name_request = new \WP_REST_Request('GET', $this->namespace . '/collections');

		$name_request->set_query_params($name_query);

		$name_response = $this->server->dispatch($name_request);
		$data1 = $name_response->get_data();

		$this->assertCount(1, $data1);
		$this->assertEquals($collectionB->get_name(), $data1[0]['name']);



		// Search collection with a specific keyword and not other keyword
		$search_query = ['search' => 'Collection -A'];

		$search_request = new \WP_REST_Request('GET', $this->namespace . '/collections');

		$search_request->set_query_params($search_query);

		$search_response = $this->server->dispatch($search_request);
		$data2 = $search_response->get_data();

		$this->assertCount(2, $data2);

		$names = [$data2[0]['name'], $data2[1]['name']];
		$this->assertNotContains('A', $names);



		/* Meta Query:
			Fetch items from a collection desc ordered by fieldA1 and its only in range A to Y.
		*/

		$meta_query = [
			'metakey'   => $fieldA1->get_id(),
			'orderby'   => 'meta_value',
			'order'     => 'DESC',
			'metaquery' => array(
				array(
					'key'     => $fieldA1->get_id(),
					'value'   => array( 'A', 'F' ),
					'compare' => 'BETWEEN'
				),
			),
		];

		$meta_query_request = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collectionA->get_id() . '/items');

		$meta_query_request->set_query_params($meta_query);

		$meta_query_response = $this->server->dispatch($meta_query_request);
		$data3 = $meta_query_response->get_data();

		$this->assertCount(2, $data3);
	}
}

?>