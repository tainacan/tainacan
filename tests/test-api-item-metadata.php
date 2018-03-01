<?php

namespace Tainacan\Tests;

/**
 * @group api_item_meta
 */
class TAINACAN_REST_Item_Metadata_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_suggestion_item_metadata_in_a_collection(){
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
				'field_type'		=> $type,
				'accept_suggestion'	=> true
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
		
		$this->assertEquals($item->get_id() , $data['item']['id']);
		$this->assertEquals('TestValues', $data['value']);
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata' );
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		
		$this->assertEquals( 'TestValues', $data['value'] );
		
		// Test Suggestion
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValuesSuggestion',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $field->get_id() );
		$request->set_body($item__metadata_json);
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		$this->assertEquals( 'pending', $data['status'] );
		global $Tainacan_Logs;
		$query = $Tainacan_Logs->fetch(['post_status' => 'pending']);
		
		$log = false;
		while ($query->have_posts()) {
			$query->the_post();
			$post = get_post();
			$log = $Tainacan_Logs->get_entity_by_post($post);
		}
		
		$pending = $log->get_value();
		
		$this->assertEquals('TestValuesSuggestion', $pending->value);
		
		wp_set_current_user($this->user_id);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/logs/' . $log->get_id() . '/approve' );
		$response = $this->server->dispatch($request);
		
		var_dump($response);
	}

	
}

?>