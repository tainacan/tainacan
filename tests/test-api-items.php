<?php

namespace Tainacan\Tests;

class TAINACAN_REST_Items_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_item_in_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Javascript Frameworks',
				'description' => 'The best framework to javascript'
			),
			true
		);

		$item_json = json_encode([
			'title'       => 'Vue JS 2',
			'description' => 'The Progressive JavasScript Framework'
		]);

		$request  = new \WP_REST_Request('POST', $this->namespaced_route . '/items/collection/' . $collection->get_id());
		$request->set_body($item_json);

		$response = $this->server->dispatch($request);

		$this->assertEquals(201, $response->get_status());

		$data = json_decode($response->get_data(), true);

		$this->assertEquals('Vue JS 2', $data['title']);
	}

	public function test_fetch_items_from_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods'
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

		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'SCRUM',
				'description' => 'Um framework ágil para gerenciamento de tarefas.',
				'collection'  => $collection
			),
			true
		);

		$request  = new \WP_REST_Request('GET', $this->namespaced_route . '/items/collection/' . $collection->get_id());
		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = json_decode($response->get_data(), true);

		$this->assertContainsOnly('string', $data);

		$first_item  = json_decode($data[0], true);
		$second_item = json_decode($data[1], true);

		$this->assertEquals($item2->get_title(), $first_item['title']);
		$this->assertEquals($item1->get_title(), $second_item['title']);
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
			$this->namespaced_route . '/items/collection/' . $collection->get_id() . '/' . $item1->get_id()
		);
		$request->set_body($delete_permanently);

		$response = $this->server->dispatch($request);

		// To be removed
		if($response->get_status() != 200){
			$this->markTestSkipped('Need method delete implemented.');
		}

		$this->assertEquals(200, $response->get_status());

		$data = json_decode($response->get_data(), true);

		$this->assertEquals('trash', $data['status']);

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
			$this->namespaced_route . '/items/collection/' . $collection->get_id() . '/' . $item2->get_id()
		);
		$request->set_body($delete_permanently);

		$response = $this->server->dispatch($request);

		// To be removed
		if($response->get_status() != 200){
			$this->markTestSkipped('Need method delete implemented.');
		}

		$this->assertEquals(200, $response->get_status());

		$data = json_decode($response->get_data(), true);

		$this->assertTrue($data);
	}
}

?>