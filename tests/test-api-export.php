<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Export_Controller extends TAINACAN_UnitApiTestCase {
	
	protected function create_requirements() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'testeItemExport',
				'description' => 'No description',
			),
			true,
			true
		);
		
		$type = $this->tainacan_field_factory->create_field('text');
		
		$field = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'              => 'teste_Export',
				'description'       => 'descricao',
				'collection'        => $collection,
				'field_type'		=> $type,
				'exposer_mapping'	=> [
					'dublin-core' => 'language'
				]
			),
			true,
			true
		);
		
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_Export',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true,
			true
		);
		
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $field);
		
		$item_metadata->set_value('teste_export_field_value');
		
		$item_metadata->validate();
		
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
		
		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_Export2',
				'description' => 'adasdasdsa2',
				'collection'  => $collection
			),
			true,
			true
		);
		
		$item_metadata2 = new \Tainacan\Entities\Item_Metadata_Entity($item2, $field);
		
		$item_metadata2->set_value('teste_export_field_value2');
		
		$item_metadata2->validate();
		
		$item_metadata2 = $Tainacan_Item_Metadata->insert($item_metadata2);
		
		$item3 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_Export3',
				'description' => 'adasdasdsa3',
				'collection'  => $collection
			),
			true,
			true
		);
		
		$item_metadata3 = new \Tainacan\Entities\Item_Metadata_Entity($item3, $field);
		
		$item_metadata3->set_value('teste_export_field_value3');
		
		$item_metadata3->validate();
		
		$item_metadata3 = $Tainacan_Item_Metadata->insert($item_metadata3);
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
		        'name' => 'testeItemExport2',
		        'description' => 'No description',
		    ),
		    true,
		    true
		    );
		
		$type2 = $this->tainacan_field_factory->create_field('text');
		
		$field2 = $this->tainacan_entity_factory->create_entity(
		    'field',
		    array(
		        'name'              => 'teste_Export2',
		        'description'       => 'descricao2',
		        'collection'        => $collection2,
		        'field_type'		=> $type2,
		        'exposer_mapping'	=> [
		            'dublin-core' => 'contributor'
		        ]
		    ),
		    true,
		    true
		    );
		
		$item_c2 = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
		        'title'       => 'item_c2_teste_Export',
		        'description' => 'adasdasdsa_c2',
		        'collection'  => $collection2
		    ),
		    true,
		    true
		    );
		
		$item_c2_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item_c2, $field2);
		
		$item_c2_metadata->set_value('teste_export_field_value');
		
		$item_c2_metadata->validate();
		
		$item_c2_metadata = $Tainacan_Item_Metadata->insert($item_c2_metadata);
		
		$item_c2_2 = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
		        'title'       => 'item_c2_teste_Export2',
		        'description' => 'adasdasdsa2_c2',
		        'collection'  => $collection2
		    ),
		    true,
		    true
		    );
		
		$item_c2_metadata2 = new \Tainacan\Entities\Item_Metadata_Entity($item_c2_2, $field2);
		
		$item_c2_metadata2->set_value('teste_export_field_value2');
		
		$item_c2_metadata2->validate();
		
		$item_c2_metadata2 = $Tainacan_Item_Metadata->insert($item_c2_metadata2);
		
		$item_c2_3 = $this->tainacan_entity_factory->create_entity(
		    'item',
		    array(
		        'title'       => 'item_c2_teste_Export3',
		        'description' => 'adasdasdsa3_c2',
		        'collection'  => $collection2
		    ),
		    true,
		    true
		    );
		
		$item_c2_metadata3 = new \Tainacan\Entities\Item_Metadata_Entity($item_c2_3, $field2);
		
		$item_c2_metadata3->set_value('teste_export_field_value3');
		
		$item_c2_metadata3->validate();
		
		$item_c2_metadata3 = $Tainacan_Item_Metadata->insert($item_c2_metadata3);
		
		return [
		    'collection' => $collection,
		    'items' => [$item, $item2, $item3],
		    'field' => $field,
		    'items_metadatas' => [$item_metadata, $item_metadata2, $item_metadata3],
		    'collections' => [$collection, $collection2],
		    'items2' => [$item_c2, $item_c2_2, $item_c2_3],
		    'items_metadatas2' => [$item_c2_metadata, $item_c2_metadata2, $item_c2_metadata3],
		];
	}
	
	public function test_export_a_collection() {
		extract($this->create_requirements());
		
		$item_exposer_json = json_encode([
			'exposer-type'       => 'Xml',
			'exposer-map'     => 'Value',
			'export-background' => false
		]);
		
		$query = [
			'orderby' => 'id',
			'order'	  => 'asc',
		];
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/export/collection/' . $collection->get_id() );
		$request->set_query_params($query);
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertInstanceOf('SimpleXMLElement', $xml = @simplexml_load_string($data));
		
		$this->assertEquals(3, $xml->count());
		$i = 0;
		foreach ($xml->children() as $xml_item ) {
			$fields = $items[$i]->get_fields();
			foreach ($fields as $field_meta) {
				$field = $field_meta->get_field();
				$this->assertEquals($field_meta->get_value(), $xml_item->{$field->get_name()});
				//echo "{$field->get_name()}:{$field_meta->get_value()}"; // uncomment if need debug
			}
			$i++;
		}
	}
	
	/**
	 * @group export_all
	 */
	public function test_export_all() {
	    extract($this->create_requirements());
	    
	    $item_exposer_json = json_encode([
	        'exposer-type'       => 'Xml',
	        'exposer-map'     => 'Value',
	        'export-background' => false
	    ]);
	    
	    $query = [
	        'orderby' => 'id',
	        'order'	  => 'asc',
	    ];
	    
	    $request  = new \WP_REST_Request('GET', $this->namespace . '/export' );
	    $request->set_query_params($query);
	    $request->set_body($item_exposer_json);
	    $response = $this->server->dispatch($request);
	    $this->assertEquals(200, $response->get_status());
	    $data = $response->get_data();
	    
	    $this->assertInstanceOf('SimpleXMLElement', $xml = @simplexml_load_string($data));
	    
	    var_dump($data);
	    /*
	    $this->assertEquals(3, $xml->count());
	    $i = 0;
	    foreach ($xml->children() as $xml_item ) {
	        $fields = $items[$i]->get_fields();
	        foreach ($fields as $field_meta) {
	            $field = $field_meta->get_field();
	            $this->assertEquals($field_meta->get_value(), $xml_item->{$field->get_name()});
	            //echo "{$field->get_name()}:{$field_meta->get_value()}"; // uncomment if need debug
	        }
	        $i++;
	    }*/
	}
}

?>
