<?php

namespace Tainacan\Tests;

/**
 * @group api_item_meta
 */
class TAINACAN_REST_Item_Metadata_Controller extends TAINACAN_UnitApiTestCase {
	protected $item;
	protected $collection;
	protected $field;
	
	protected function create_meta_requirements() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'testeItemMetadata',
				'description' => 'No description',
			),
			true,
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
			true,
			true
		);
		
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_metadado',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true,
			true
		);
		$this->collection = $collection;
		$this->item = $item;
		$this->field = $field;
		return ['collection' => $collection, 'item' => $item, 'field' => $field];
	}
	
	public function test_create_suggestion_item_metadata_in_a_collection(){
		global $Tainacan_Fields, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());

		$item__metadata_json = json_encode([
			'values'       => 'TestValues_metadado',
		]);

		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $field->get_id() );
		$request->set_body($item__metadata_json);

		$response = $this->server->dispatch($request);

		$this->assertEquals(200, $response->get_status());

		$data = $response->get_data();
		
		$this->assertEquals($item->get_id() , $data['item']['id']);
		$this->assertEquals('TestValues_metadado', $data['value']);
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/'. $field->get_id() );
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		$this->assertEquals( 'TestValues_metadado', $data['value'] );
		
		// Test Suggestion
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValuesSuggestion_metadado',
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
		
		$this->assertEquals('TestValuesSuggestion_metadado', $pending->value);
		
		wp_set_current_user($this->user_id);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/logs/' . $log->get_id() . '/approve' );
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/'. $field->get_id()  );
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		$this->assertEquals( 'TestValuesSuggestion_metadado', $data['value'] );
		
	}
	
	function test_create_anonymous_suggestion_item_metadata_in_a_collection() {
		extract($this->create_meta_requirements());
		global $Tainacan_Fields, $Tainacan_Item_Metadata;
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValues_metadado',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $field->get_id() );
		$request->set_body($item__metadata_json);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		// Test Anonymous Suggestion
		wp_logout();
		wp_set_current_user(0);
		
		$this->assertEquals(0, get_current_user_id());
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValuesAnonymousSuggestion_metadado',
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
		
		$this->assertEquals('TestValuesAnonymousSuggestion_metadado', $pending->value);
		
		wp_set_current_user($this->user_id);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/logs/' . $log->get_id() . '/approve' );
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/'. $field->get_id()  );
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		$this->assertEquals( 'TestValuesAnonymousSuggestion_metadado', $data['value'] );
		
	}

	
}

?>