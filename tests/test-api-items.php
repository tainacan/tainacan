<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Items_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_item_in_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Javascript Frameworks',
				'description' => 'The best framework to javascript',
			),
			true
		);

		$item_json = json_encode([
			'title'       => 'Vue JS 2',
			'description' => 'The Progressive JavasScript Framework'
		]);

		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		$request->set_body($item_json);

		$response = $this->server->dispatch($request);

		$this->assertEquals(201, $response->get_status());

		$data = $response->get_data();

		$this->assertEquals('Vue JS 2', $data['title']);
	}

	public function test_fetch_items_from_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
                'status'      => 'publish'
			),
			true
		);

		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'SCRUM',
				'description' => 'Um framework ágil para gerenciamento de tarefas.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$request  = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data()['items'];

		$items_titles = [$data[0]['title'], $data[1]['title']];

		$this->assertContains($item1->get_title(), $items_titles);
		$this->assertContains($item2->get_title(), $items_titles);
	}

	public function test_delete_or_trash_item_from_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
                'status'      => 'publish'
			),
			true
		);
		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $collection
			),
			true
		);

		// Move to trash
		$delete_permanently = ['permanently' => false];

		$request  = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/items/' . $item1->get_id()
		);
		$request->set_query_params($delete_permanently);

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertEquals($item1->get_title(), $data['title']);

		$post_meta = get_post_meta($item1->get_id(), '_wp_trash_meta_status', true);

		$this->assertNotEmpty($post_meta);

		#######################################################################################

		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'SCRUM',
				'description' => 'Um framework ágil para gerenciamento de tarefas.',
				'collection'  => $collection
			),
			true
		);

		// Delete permanently
		$delete_permanently = ['permanently' => true];

		$request  = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/items/' . $item2->get_id()
		);
		$request->set_query_params($delete_permanently);

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();

		$this->assertEquals($item2->get_title(), $data['title']);

		$no_post = get_post($item2->get_id());

		$this->assertNull($no_post);
	}

	public function test_update_item(){
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'SCRUM e PMBOK',
				'description' => 'Unidos no Gerenciamento de Projetos',
				'collection'  => $collection,
			),
			true
		);

		$new_attributes = json_encode([
			'title' => 'SCRUM e XP',
			'description' => 'Direto da trincheiras',
		]);

		$request = new \WP_REST_Request(
			'PATCH', $this->namespace . '/items/' . $item->get_id()
		);

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertNotEquals($item->get_title(), $data['title']);
		$this->assertEquals('SCRUM e XP', $data['title']);
	}

	public function test_get_items_with_metadata_not_filled(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
				'status'      => 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadatum',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil para validação de ideias.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'SCRUM',
				'description' => 'Um framework ágil para gerenciamento de produto.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$itemMetaRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $metadatum);

		// Fills item metadata only for item 1
		$newMeta->set_value('test');
		$newMeta->validate();

		$itemMetaRepo->insert($newMeta);

		$attributes = [
			'metaquery' => [
				'key'     => $metadatum->get_id(),
				'compare' => 'NOT EXISTS'
			],
		];

		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/collection/'. $collection->get_id() .'/items'
		);

		$request->set_query_params($attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data()['items'];

		$this->assertCount(1, $data);
		$this->assertEquals('SCRUM', $data[0]['title']);
		$this->assertEquals($item2->get_id(), $data[0]['id']);
	}

	public function test_fetch_only() {

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
                'status'      => 'publish'
			),
			true
		);

		$private_meta = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'private_meta',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'private'
		    ),
		    true
		);
		
		$public_meta = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'public_meta',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish'
		    ),
		    true
		);
		
		$discarded = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'discarded',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish'
		    ),
		    true
	    );

		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'SCRUM',
				'description' => 'Um framework ágil para gerenciamento de tarefas.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$itemMetaRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $public_meta);
		$newMeta->set_value('test');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $private_meta);
		$newMeta->set_value('test');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $discarded);
		$newMeta->set_value('test');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);

		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item2, $public_meta);
		$newMeta->set_value('test');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item2, $private_meta);
		$newMeta->set_value('test');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item2, $discarded);
		$newMeta->set_value('test');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);


		$attributes = [
			'fetch_only' => [
				'meta' => [
					$public_meta->get_id(),
					$private_meta->get_id()
				]
			],
		];

		
		// First without fetch only
		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data()['items'];

		$this->assertEquals( 2, sizeof($data) );
		$this->assertEquals( 5, sizeof($data[0]['metadata']) );

		// Fetch only as admin
		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);
		$data = $response->get_data()['items'];


		$this->assertEquals( 2, sizeof($data) );
		$this->assertEquals( 2, sizeof($data[0]['metadata']) );

		
		////
		
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);

		// Fetch only as subscriber
		$request = new \WP_REST_Request(
			'GET', $this->namespace . '/items'
		);
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);
		$data = $response->get_data()['items'];

		$this->assertEquals( 2, sizeof($data) );
		$this->assertEquals( 1, sizeof($data[0]['metadata']) );
		$this->assertEquals( 'public_meta', $data[0]['metadata']['public_meta']['name'] );



	}

    /**
     * @group api_item_author
     */
	public function test_create_item_as_auhor()
    {

        //create user as tainacan author

        $new_user = $this->factory()->user->create(array('role' => 'tainacan-author'));
        //$new_user = $this->factory()->user->create(array( 'role' => 'administrator' ));
        wp_set_current_user($new_user);
        $user_id = get_current_user_id();
        $this->assertEquals($new_user, $user_id);

        // create collection as auto-draft

        $collection_JSON = json_encode([
            'name' => 'TesteJsonAdd',
            'description' => 'Teste JSON',
            "status" => "auto-draft"
        ]);

        $request = new \WP_REST_Request('POST', $this->namespace . '/collections');
        $request->set_body($collection_JSON);

        $response = $this->server->dispatch($request);
        $this->assertEquals(201, $response->get_status(), sprintf('response: %s', print_r($response, true)));

        $collection = $response->get_data();
        $id = $collection['id'];

        // publish collection

        $new_values = json_encode([
            "status" => "publish"
        ]);

        $request = new \WP_REST_Request(
            'PATCH', $this->namespace . '/collections/' . $id
        );

        $request->set_body($new_values);

        $response = $this->server->dispatch($request);

        $data = $response->get_data();
        $this->assertEquals('publish', $data['status']);

        // try create item in own collection

        $item_json = json_encode([
            'title' => 'SCRUM',
            'description' => 'Um framework ágil para gerenciamento de produto.',
            "status" => "auto-draft"
        ]);

        $request = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $id . '/items');
        $request->set_body($item_json);

        $response = $this->server->dispatch($request);

        //$this->assertEquals(403, $response->get_status());
        $this->assertEquals(201, $response->get_status());
    }
	
	/**
	 * @group duplicate
	 */
	function test_duplicate() {
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
                'status'      => 'publish'
			),
			true
		);

		
		$public_meta = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'public_meta',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish'
		    ),
		    true
		);
		
		$multiple_meta = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'public_meta',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple' => 'yes',
				'status' => 'publish'
		    ),
		    true
		);
		
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'taxonomy_public',
				'description'  => 'taxonomy_public',
				'status' => 'publish'
			),
			true
		);
		
		$taxonomy2 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'taxonomy_public2',
				'description'  => 'taxonomy_public2',
				'status' => 'publish'
			),
			true
		);
		
		$tax_meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadata-public',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy->get_id()
				],
				'multiple' => 'yes'
			),
			true
		);
		
		$tax_meta_single = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadata-public-single',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy2->get_id()
				],
				'multiple' => 'no'
			),
			true
		);
		
		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$itemMetaRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $public_meta);
		$newMeta->set_value('test');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		
		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $multiple_meta);
		$newMeta->set_value(['test1', 'test2']);
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		
		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $tax_meta);
		$newMeta->set_value(['blue', 'red']);
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		
		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $tax_meta_single);
		$newMeta->set_value('test term');
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $collection->get_id() . '/items/' . $item1->get_id() . '/duplicate');

		$response = $this->server->dispatch($request);

		$this->assertEquals(201, $response->get_status());

		$data = $response->get_data();

		$this->assertEquals('Lean Startup', $data['items'][0]['title']);
		
		$metadata_1 = $item1->get_metadata();
		
		$duplicated = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $data['items'][0]['id'] );
		
		$metadata_2 = $duplicated->get_metadata();
		
		foreach( $metadata_1 as $k => $m ) {
			$this->assertEquals( $m->get_value(), $metadata_2[$k]->get_value() );
		}
		
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $collection->get_id() . '/items/' . $item1->get_id() . '/duplicate');

		$params = json_encode([
			'copies' => 4,
			'status' => 'publish'
		]);
		
		$request->set_body($params);

		$response = $this->server->dispatch($request);
		
		$data = $response->get_data();
		
		$this->assertEquals(4, count($data['items']));
		
		foreach ( $data['items'] as $created_item ) {
			$duplicated = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $created_item['id'] );
			
			$this->assertEquals('publish', $created_item['status']);
			
			$metadata_2 = $duplicated->get_metadata();
			
			foreach( $metadata_1 as $k => $m ) {
				$this->assertEquals( $m->get_value(), $metadata_2[$k]->get_value() );
			}
		}
		
		
		// Create a required metadata 
		$required_meta = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'required_meta',
			    'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple' => 'no',
				'required' => 'yes',
				'status' => 'publish'
		    ),
		    true
		);
		
		// $metas = \Tainacan\Repositories\Metadata::get_instance()->fetch_by_collection($collection, [], 'OBJECT');
		// 
		// foreach ($metas as $m) {
		// 	var_dump($m->get_name());
		// }
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $collection->get_id() . '/items/' . $item1->get_id() . '/duplicate');

		$params = json_encode([
			'copies' => 3,
			'status' => 'publish'
		]);
		
		$request->set_body($params);

		$response = $this->server->dispatch($request);
		
		$data = $response->get_data();
		
		$this->assertEquals(3, count($data['items']));
		
		foreach ( $data['items'] as $created_item ) {
			$duplicated = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $created_item['id'] );
			
			// item does not validate because required meta is empty and stays draft
			$this->assertEquals('draft', $duplicated->get_status());
			$this->assertEquals('draft', $created_item['status']);

		}
		
		
		
		
	}
	
	/*
	* @group tax_query
	 */
	function test_support_tax_query_like() {
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
                'status'      => 'publish'
			),
			true
		);

		
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'taxonomy_public',
				'description'  => 'taxonomy_public',
				'status' => 'publish'
			),
			true
		);
		
		$tax_meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadata-public',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy->get_id()
				],
				'multiple' => 'yes'
			),
			true
		);
		
		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$itemMetaRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item1, $tax_meta);
		$newMeta->set_value(['blue', 'mellon']);
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		
		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'XXLean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item2, $tax_meta);
		$newMeta->set_value(['red', 'watermellon']);
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		
		$item3 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'XXLean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$newMeta = new \Tainacan\Entities\Item_Metadata_Entity($item3, $tax_meta);
		$newMeta->set_value(['red', 'blue']);
		$newMeta->validate();
		$itemMetaRepo->insert($newMeta);
		
		
		####################################################
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		
		$attributes = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomy->get_db_identifier(),
					'operator' => 'LIKE',
					'terms' => 'mellon'
				]
			]
		];
		
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data()['items'];
		
		$this->assertEquals(2, count($data));
		$ids = array_map(function($e) { return $e['id']; }, $data);
		$this->assertContains($item1->get_id(), $ids);
		$this->assertContains($item2->get_id(), $ids);
		
		####################################################
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		
		$attributes = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomy->get_db_identifier(),
					'operator' => 'LIKE',
					'terms' => 'red'
				]
			]
		];
		
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data()['items'];
		
		$this->assertEquals(2, count($data));
		$ids = array_map(function($e) { return $e['id']; }, $data);
		$this->assertContains($item2->get_id(), $ids);
		$this->assertContains($item3->get_id(), $ids);
		
		####################################################
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		
		$attributes = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomy->get_db_identifier(),
					'operator' => 'LIKE',
					'terms' => 'water'
				]
			]
		];
		
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data()['items'];
		
		$this->assertEquals(1, count($data));
		$ids = array_map(function($e) { return $e['id']; }, $data);
		$this->assertContains($item2->get_id(), $ids);
		
		####################################################
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		
		$attributes = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomy->get_db_identifier(),
					'operator' => 'NOT LIKE',
					'terms' => 'mellon'
				]
			]
		];
		
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data()['items'];
		
		$this->assertEquals(1, count($data));
		$ids = array_map(function($e) { return $e['id']; }, $data);
		$this->assertContains($item3->get_id(), $ids);
		
		####################################################
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		
		$attributes = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomy->get_db_identifier(),
					'operator' => 'NOT LIKE',
					'terms' => '__does_not_exists'
				]
			]
		];
		
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data()['items'];
		
		$this->assertEquals(3, count($data));
		
		####################################################
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/items');
		
		$attributes = [
			'taxquery' => [
				[
					'taxonomy' => $taxonomy->get_db_identifier(),
					'operator' => 'LIKE',
					'terms' => '__does_not_exists'
				]
			]
		];
		
		$request->set_query_params($attributes);
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data()['items'];
		
		$this->assertEquals(0, count($data));
		
	}
	
}

?>