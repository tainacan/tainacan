<?php

namespace Tainacan\Tests;

/**
 * @group api_exposers
 */
class TAINACAN_REST_Exposers extends TAINACAN_UnitApiTestCase {
	protected $item;
	protected $collection;
	protected $metadatum;
	
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
		
		$type = $this->tainacan_metadatum_factory->create_metadatum('text');
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'teste_Expose',
				'description'       => 'descricao',
				'collection'        => $collection,
				'metadata_type'		=> $type,
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
				'title'       => 'item_teste_Expose',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true,
			true
		);
		$this->collection = $collection;
		$this->item = $item;
		$this->metadatum = $metadatum;
		return ['collection' => $collection, 'item' => $item, 'metadatum' => $metadatum];
	}
	
	/**
	 * @group value_exposer
	 */
	public function test_value_exposer() {
		global $Tainacan_Metadata, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValues_exposers',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $this->metadatum->get_id() );
		$request->set_body($item__metadata_json);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$data = $response->get_data();
		
		$this->assertEquals($this->item->get_id(), $data['item']['id']);
		$this->assertEquals('TestValues_exposers', $data['value']);
		
		$item_exposer_json = json_encode([
			'exposer_map'       => 'Value',
		]);
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertEquals('TestValues_exposers', $data['teste_Expose']);
		
		$request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertEquals('adasdasdsa', $data['Description']);
		$this->assertEquals('item_teste_Expose', $data['Title']);
		$this->assertEquals('TestValues_exposers', $data['teste_Expose']);
	}
	
	/**
	 * @group xml_exposer
	 */
	public function test_xml_exposer() {
		global $Tainacan_Metadata, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValues_exposers',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $this->metadatum->get_id() );
		$request->set_body($item__metadata_json);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$data = $response->get_data();
		
		$this->assertEquals($this->item->get_id(), $data['item']['id']);
		$this->assertEquals('TestValues_exposers', $data['value']);
		
		$item_exposer_json = json_encode([
			'exposer-type'       => 'Xml',
		]);
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertInstanceOf('SimpleXMLElement', @simplexml_load_string($data));
		
		$item_exposer_json = json_encode([
			'exposer_map'       => 'Dublin Core',
		]);
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		$this->assertEquals('TestValues_exposers', $data['dc:language']);
		
		$item_exposer_json = json_encode([
			'exposer-type'       => 'Xml',
			'exposer_map'       => 'Dublin Core',
		]);
		$request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$xml = new \SimpleXMLElement($data);
		$rdf = $xml->children(\Tainacan\Exposers\Mappers\Dublin_Core::XML_RDF_NAMESPACE);
		$dc = $rdf->children(\Tainacan\Exposers\Mappers\Dublin_Core::XML_DC_NAMESPACE);
		
		$this->assertEquals('adasdasdsa', $dc->description);
		$this->assertEquals('item_teste_Expose', $dc->title);
		$this->assertEquals('TestValues_exposers', $dc->language);
		
	}
	
	/**
	 * @group exposers-slug
	 */
	public function test_exposer_map_by_slug() {
	    global $Tainacan_Metadata, $Tainacan_Item_Metadata;
	    extract($this->create_meta_requirements());
	    
	    $item__metadata_json = json_encode([
	        'values'       => 'TestValues_exposers_slug',
	    ]);
	    
	    $request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $this->metadatum->get_id() );
	    $request->set_body($item__metadata_json);
	    
	    $response = $this->server->dispatch($request);
	    
	    $this->assertEquals(200, $response->get_status());
	    
	    $data = $response->get_data();
	    
	    $this->assertEquals($this->item->get_id(), $data['item']['id']);
	    $this->assertEquals('TestValues_exposers_slug', $data['value']);
	    
	    $item_exposer_json = json_encode([
	        'exposer_map'       => 'dublin-core',
	    ]);
	    $request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
	    $request->set_body($item_exposer_json);
	    $response = $this->server->dispatch($request);
	    $this->assertEquals(200, $response->get_status());
	    $data = $response->get_data();
	    $this->assertEquals('TestValues_exposers_slug', $data['dc:language']);
	    
	    $item_exposer_json = json_encode([
	        'exposer-type'       => 'xml',
	        'exposer_map'       => 'dublin-core',
	    ]);
	    $request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
	    $request->set_body($item_exposer_json);
	    $response = $this->server->dispatch($request);
	    $this->assertEquals(200, $response->get_status());
	    $data = $response->get_data();
	    
	    $xml = new \SimpleXMLElement($data);
	    $rdf = $xml->children(\Tainacan\Exposers\Mappers\Dublin_Core::XML_RDF_NAMESPACE);
	    $dc = $rdf->children(\Tainacan\Exposers\Mappers\Dublin_Core::XML_DC_NAMESPACE);
	    
	    $this->assertEquals('adasdasdsa', $dc->description);
	    $this->assertEquals('item_teste_Expose', $dc->title);
	    $this->assertEquals('TestValues_exposers_slug', $dc->language);
	}
	
	/**
	 * @group oai-pmh
	 */
	public function test_oai_pmh() {
		global $Tainacan_Metadata, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());
		
		$item_exposer_json = json_encode([
			'exposer-type'       => 'OAI-PMH',
		]);
		$request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$xml = new \SimpleXMLElement($data);
		$dc = $xml->children(\Tainacan\Exposers\Mappers\Dublin_Core::XML_DC_NAMESPACE);
		$this->assertEquals('adasdasdsa', $dc->description);
		$this->assertEquals('item_teste_Expose', $dc->title);
	}
	
	/**
	 * @group exposer-type-html
	 */
	public function test_html_type() {
		global $Tainacan_Metadata, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());
		
		$item_exposer_json = json_encode([
			'exposer-type'       => 'Html',
			'exposer_map'       => 'Value'
		]);
		$request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		// Parse HTML reponse
		$doc = new \DOMDocument();
		$this->assertTrue($doc->loadHTML($data));
		
		$headers = $doc->getElementsByTagName('th');
		$values = $doc->getElementsByTagName('td');
		
		$htmlheaders = [];
		
		foreach($headers as $nodeHeader) {
			$htmlheaders[] = trim($nodeHeader->textContent);
		}
		
		$htmlValues = [];
		$row = 0;
		$col = 0;
		foreach ($values as $nodeValue) {
			if(!array_key_exists($row, $htmlValues)) $htmlValues[$row] = [];
			$htmlValues[$row][$htmlheaders[$col]] = trim($nodeValue->textContent);
			$col++;
			if(count($htmlValues[$row]) == count($htmlheaders)) {
				$row++;
				$col = 0;
			}
		}
		// End of Parse HTML reponse
		
		$this->assertEquals('adasdasdsa', $htmlValues[0]['Description']);
		$this->assertEquals('', $htmlValues[0]['teste_Expose']);
		$this->assertEquals('item_teste_Expose', $htmlValues[0]['Title']);
	}
	
	/**
	 * @group exposer-type-csv
	 */
	public function test_csv_type() {
		global $Tainacan_Metadata, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValues_exposers',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $this->metadatum->get_id() );
		$request->set_body($item__metadata_json);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$item_exposer_json = json_encode([
			'exposer-type'       => 'Csv',
		]);
		$request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		file_put_contents('/tmp/1.csv', $data);
		
		$lines = explode(PHP_EOL, $data);
		$csv_lines = [];
		foreach ($lines as $line) {
			$csv_lines[] = str_getcsv($line, ';');
		}
		array_walk($csv_lines, function(&$a) use ($csv_lines) {
			if(count($a) == count($csv_lines)) {
				$a = array_combine($csv_lines[0], $a);
			}
		});
		array_shift($csv_lines);
		
		$this->assertEquals('adasdasdsa', $csv_lines[0]['Description']);
		$this->assertEquals('TestValues_exposers', $csv_lines[0]['teste_Expose']);
		$this->assertEquals('item_teste_Expose', $csv_lines[0]['Title']);
	}
	
	/**
	 * @group items_exposer
	 */
	public function test_items_exposer() {
		global $Tainacan_Metadata, $Tainacan_Item_Metadata;
		
		extract($this->create_meta_requirements());
		
		$item__metadata_json = json_encode([
			'values'       => 'TestValues_exposers',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $this->metadatum->get_id() );
		$request->set_body($item__metadata_json);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$data = $response->get_data();
		
		$this->assertEquals($this->item->get_id(), $data['item']['id']);
		$this->assertEquals('TestValues_exposers', $data['value']);
		
		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_Expose2',
				'description' => 'adasdasdsa2',
				'collection'  => $this->collection
			),
			true,
			true
		);
		
		$item3 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_Expose3',
				'description' => 'adasdasdsa3',
				'collection'  => $this->collection
			),
			true,
			true
		);
		
		$item_exposer_json = json_encode([
			'exposer_map'       => 'Value',
		]);
		
		$request = new \WP_REST_Request('GET', $this->namespace . '/items/' . $this->item->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertEquals('adasdasdsa', $data['Description']);
		$this->assertEquals('item_teste_Expose', $data['Title']);
		$this->assertEquals('TestValues_exposers', $data['teste_Expose']);
	}
	
	/**
	 * @group mapped_new_collection
	 */
	public function test_mapped_new_collection() {
	    $collection_JSON = json_encode([
	        'exposer_map'  => 'Dublin Core',
	        'name'         => 'TesteJsonAddDublin_Core',
	        'description'  => 'Teste JSON Dublin Core mapped',
	        'status'       => 'publish'
	    ]);
	    
	    $mapper = new \Tainacan\Exposers\Mappers\Dublin_Core();
	    
	    $request = new \WP_REST_Request('POST', $this->namespace . '/collections');
	    $request->set_body($collection_JSON);
	    $response = $this->server->dispatch($request);
	    $this->assertEquals(201, $response->get_status(), sprintf('response: %s', print_r($response, true)));
	    $collection_array = $response->get_data();
	    $id = $collection_array['id'];
	    $Tainacan_collections = \Tainacan\Repositories\Collections::get_instance();
	    $collection = $Tainacan_collections->fetch($id);
	    
	    $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
	    $metadata = $Tainacan_Metadata->fetch_by_collection( $collection, [ 'order' => 'id' ], 'OBJECT' );
	    
	    $this->assertEquals(count($mapper->metadata), count($metadata));
	    foreach ($metadata as $metadatum) {
	        $this->assertTrue(array_key_exists($metadatum->get_slug(), $mapper->metadata));
	        if(! array_key_exists('core_metadatum', $mapper->metadata[$metadatum->get_slug()]) || $mapper->metadata[$metadatum->get_slug()]['core_metadatum'] == false) {
	           $this->assertEquals($mapper->metadata[$metadatum->get_slug()]['URI'], $metadatum->get_description());
	        }
	        $this->assertEquals($mapper->metadata[$metadatum->get_slug()]['label'], $metadatum->get_name());
	    }
	    
	}
	
}

?>