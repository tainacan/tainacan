<?php

namespace Tainacan\Tests;

/**
 * @group api_exposers
 */
class TAINACAN_REST_Exposers extends TAINACAN_UnitApiTestCase {
	protected $item;
	protected $collection;
	protected $field;
	
	protected function create_meta_requirements() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'testeItemExpose',
				'description' => 'No description',
			),
			true,
			true
		);
		
		$type = $this->tainacan_field_factory->create_field('text');
		
		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'              => 'teste_Expose',
				'description'       => 'descricao',
				'collection'        => $collection,
				'field_type'		=> $type,
				'exposer_mapping'	=> [
					'dublin-core' => [
						'name' => 'title',
						'label' => 'Title',
						'URI' => 'http://purl.org/dc/terms/title',
					]
				]
			),
			true,
			true
		);
		
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_Expose',
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
	
	public function test_exposers() {
		$this->create_meta_requirements();
		global $Tainacan_Fields, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValues_exposers',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $item->get_id() . '/metadata/' . $field->get_id() );
		$request->set_body($item__metadata_json);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$data = $response->get_data();
		
		$this->assertEquals($item->get_id() , $data['item']['id']);
		$this->assertEquals('TestValues_exposers', $data['value']);
		
		$item_exposer_json = json_encode([
			'exposer-type'       => 'Xml',
		]);
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/'. $field->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertInstanceOf('SimpleXMLElement', @simplexml_load_string($data));
		
		$item_exposer_json = json_encode([
			'exposer-map'       => 'Dublin Core',
		]);
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $item->get_id() . '/metadata/'. $field->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertEquals('TestValues_exposers', $data['dc:title']);
	}
}

?>