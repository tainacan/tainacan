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
		$data = $response->get_data();

		$items_titles = [$data[0]['title'], $data[1]['title']];

		$this->assertContains($item1->get_title(), $items_titles);
		$this->assertContains($item2->get_title(), $items_titles);
	}

	public function test_delete_or_trash_item_from_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

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
		$delete_permanently = json_encode(['is_permanently' => false]);

		$request  = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/items/' . $item1->get_id()
		);
		$request->set_body($delete_permanently);

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
		$delete_permanently = json_encode(['is_permanently' => true]);

		$request  = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/items/' . $item2->get_id()
		);
		$request->set_body($delete_permanently);

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
}

?>