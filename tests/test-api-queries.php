<?php

namespace Tainacan\Tests;

/**
 * @group api
 * **/
class TAINACAN_REST_Queries extends TAINACAN_UnitApiTestCase {

	public function test_queries(){

		// Populate the database

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

		$collectionA = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'A',
				'description' => 'Collection A',
				'status'      => 'publish'
			],
			true
		);

		// Create Taxonomy
		$taxonomyA = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'            => 'Tax A',
				'description'     => 'A taxonomy',
				'allow_insert'    => 'yes',
				'status'          => 'publish',
				'collections_ids' => [
					$collectionA->get_id(),
					$collectionC->get_id(),
					$collectionB->get_id()
				]
			),
			true
		);
		// Create Term

		$termA = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomyA->get_db_identifier(),
				'name'     => 'Term A',
				'user'     => get_current_user_id(),
			),
			true
		);
		//

		// Create Items
		$itemA1 = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title'       => 'Item A-1',
				'description' => 'Item in collection A',
				'status'      => 'publish',
				'collection'  => $collectionA,
			],
			true
		);

		$itemA2 = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title'       => 'Item A-2',
				'description' => 'Item in collection A',
				'status'      => 'private',
				'collection'  => $collectionA,
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

		// Create Metadata and Metadatum Type

		$metadata_type = $this->tainacan_metadatum_factory->create_metadatum('text');

		$metadatumA1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'        => 'Metadatum A-1',
				'description' => 'Simple metadatum in collection A',
				'status'      => 'publish',
				'collection'  => $collectionA,
				'metadata_type'  => $metadata_type
			],
			true
		);

		$metadatumA2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'        => 'Metadatum A-2',
				'description' => 'Simple metadatum in collection A',
				'status'      => 'publish',
				'collection'  => $collectionA,
				'metadata_type'  => $metadata_type
			],
			true
		);

		$metadatumA3 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name'        => 'Metadatum A-3',
				'description' => 'Multiple metadatum in a collection A',
				'status'      => 'publish',
				'collection'  => $collectionA,
				'metadata_type'  => $metadata_type,
				'multiple'    => 'yes'
			],
			true
		);

		// Create a Item Metadata
		$itemA1_metadata1 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA1, $metadatumA1, 'E');
		$itemA1_metadata2 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA1, $metadatumA2, 'X');
		$itemA1_metadata3 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA1, $metadatumA3, ['Y', 'Z']);

		$itemA2_metadata1 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA2, $metadatumA1, 'D');
		$itemA2_metadata2 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA2, $metadatumA2, 'Q');
		$itemA2_metadata3 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA2, $metadatumA3, ['R', 'S']);

		$itemA3_metadata1 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA3, $metadatumA1, 'G');
		$itemA3_metadata2 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA3, $metadatumA2, 'T');
		$itemA3_metadata3 = $this->tainacan_item_metadata_factory->create_item_metadata($itemA3, $metadatumA3, ['Q', 'V']);

		//

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
		 *
		 * Fetch items from a collection desc ordered by metadatumA1 and its only in range A to F.
		 *
		 * */

		$meta_query = [
			'metakey'   => $metadatumA1->get_id(),
			'orderby'   => 'meta_value',
			'order'     => 'DESC',
			'metaquery' => array(
				array(
					'key'     => $metadatumA1->get_id(),
					'value'   => array( 'A', 'F' ),
					'compare' => 'BETWEEN'
				),
			),
		];

		$meta_query_request = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collectionA->get_id() . '/items');

		$meta_query_request->set_query_params($meta_query);

		$meta_query_response = $this->server->dispatch($meta_query_request);
		$data3 = $meta_query_response->get_data()['items'];

		$this->assertCount(2, $data3);

		$metadatumA1_slug = $metadatumA1->get_slug();

		$values = [$data3[0]['metadata'][$metadatumA1_slug]['value'], $data3[1]['metadata'][$metadatumA1_slug]['value']];

		$this->assertNotContains('G', $values);

		// E have to come first, because DESC
		$this->assertEquals('E', $data3[0]['metadata'][$metadatumA1_slug]['value']);
		$this->assertEquals('D', $data3[1]['metadata'][$metadatumA1_slug]['value']);


		/* Date Query:
		 *
		 * Fetch posts for today
		 *
		 * */

		$today = getdate();

		$date_query = [
			'datequery' => [
				[
					'year'  => $today['year'],
					'month' => $today['mon'],
					'day'   => $today['mday']
				]
			]
		];

		$date_query_request_collections = new \WP_REST_Request('GET', $this->namespace . '/collections');
		$date_query_request_collections->set_query_params($date_query);

		$date_query_response_collections = $this->server->dispatch($date_query_request_collections);
		$data4 = $date_query_response_collections->get_data();

		$this->assertCount(3, $data4);

		// If we change the date query for a date different of today, it should return nothing
		$date_query['datequery'][0]['year'] = 1995;

		$date_query_request_collections->set_query_params($date_query);

		$date_query_response_collections = $this->server->dispatch($date_query_request_collections);
		$data5 = $date_query_response_collections->get_data();

		$this->assertCount(0, $data5);

		/* Tax Query
		 *
		 * Fetch items under a taxonomy with a specific term
		 *
		 * */

		$tax_query = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomyA->get_db_identifier(),
					'metadatum'    => 'slug',
					'terms'    => 'term-a'
				]
			]
		];

		wp_set_post_terms($itemA1->get_id(), $termA->get_term_id(), $taxonomyA->get_db_identifier());
		wp_set_post_terms($itemA2->get_id(), $termA->get_term_id(), $taxonomyA->get_db_identifier());

		$tax_query_request_collections = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collectionA->get_id() . '/items');
		$tax_query_request_collections->set_query_params($tax_query);

		$tax_query_response_collections = $this->server->dispatch($tax_query_request_collections);
		$data6 = $tax_query_response_collections->get_data()['items'];

		$this->assertCount(2, $data6);

		$itemsA1_A2 = [$data6[0]['title'], $data6[1]['title']];

		$this->assertContains('Item A-1', $itemsA1_A2);
		$this->assertContains('Item A-2', $itemsA1_A2);
		$this->assertNotContains('Item A-3', $itemsA1_A2);
	}
}

?>