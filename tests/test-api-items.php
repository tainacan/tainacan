<?php

namespace Tainacan\Tests;

class TAINACAN_REST_Items_Controller extends TAINACAN_UnitApiTestCase {

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
			'description' => 'Um processo ágio de criação de novos negócios.',
			'collection'  => $collection
		),
		true
	);

	$item2 = $this->tainacan_entity_factory->create_entity(
		'item',
		array(
			'title'       => 'SCRUM',
			'description' => 'Um framework ágio para gerenciamento de tarefas.',
			'collection'  => $collection
		),
		true
	);

	$request  = new \WP_REST_Request('GET', $this->namespaced_route . '/items/collection/' . $collection->get_id());
	$response = $this->server->dispatch($request);

	$this->assertEquals(201, $response->get_status());

	$data = json_decode($response->get_data(), true);

	$this->assertContainsOnly('string', $data);

	$first_item  = json_decode($data[0], true);
	$second_item = json_decode($data[1], true);

	$this->assertEquals($item2->get_title(), $first_item['title']);
	$this->assertEquals($item1->get_title(), $second_item['title']);
}

}

?>