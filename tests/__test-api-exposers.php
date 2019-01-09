<?php

namespace Tainacan\Tests;

/**
 * @group api_exposers
 */
class TAINACAN_REST_Exposers extends TAINACAN_UnitApiTestCase {
	protected $item;
	protected $collection;
	/**
	 * @var \Tainacan\Entities\Metadatum
	 */
	protected $metadatum;
	/**
	 * @var \Tainacan\Entities\Metadatum
	 */
	protected $metadatum2;
	
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
					'dublin-core' => 'dc:language'
				]
			),
			true,
			true
		);
		
		$metadatum2 = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
		        'name'              => 'space meta',
		        'description'       => 'meta with space',
		        'collection'        => $collection,
		        'metadata_type'		=> $type,
		        'exposer_mapping'	=> [
		            'dublin-core' => 'dc:subject'
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
		$this->metadatum2 = $metadatum2;
		return ['collection' => $collection, 'item' => $item, 'metadatum' => $metadatum, 'metadatum2' => $metadatum2];
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
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
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
		
		$item__metadata_json = json_encode([
		    'values'       => '',
		]);
		
		$request  = new \WP_REST_Request('POST', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/' . $this->metadatum2->get_id() );
		$request->set_body($item__metadata_json);
		
		$response = $this->server->dispatch($request);
		
		$this->assertEquals(200, $response->get_status());
		
		$item_exposer_json = json_encode([
			\Tainacan\Exposers_Handler::TYPE_PARAM       => 'Xml',
		]);
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertInstanceOf('SimpleXMLElement', @simplexml_load_string($data));
		
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum2->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$this->assertInstanceOf('SimpleXMLElement', @simplexml_load_string($data));
		
		$item_exposer_json = json_encode([
			\Tainacan\Exposers_Handler::MAPPER_PARAM       => 'Dublin Core',
		]);
		$request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		$this->assertEquals('TestValues_exposers', $data['dc:language']);
		
		$item_exposer_json = json_encode([
			\Tainacan\Exposers_Handler::TYPE_PARAM       => 'Xml',
			\Tainacan\Exposers_Handler::MAPPER_PARAM       => 'Dublin Core',
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
		$this->assertEquals('', $dc->subject);
		
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
	        \Tainacan\Exposers_Handler::MAPPER_PARAM       => 'dublin-core',
	    ]);
	    $request  = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata/'. $this->metadatum->get_id() );
	    $request->set_body($item_exposer_json);
	    $response = $this->server->dispatch($request);
	    $this->assertEquals(200, $response->get_status());
	    $data = $response->get_data();
	    $this->assertEquals('TestValues_exposers_slug', $data['dc:language']);
	    
	    $item_exposer_json = json_encode([
	        \Tainacan\Exposers_Handler::TYPE_PARAM       => 'xml',
	        \Tainacan\Exposers_Handler::MAPPER_PARAM       => 'dublin-core',
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
			\Tainacan\Exposers_Handler::TYPE_PARAM       => 'OAI-PMH',
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
			\Tainacan\Exposers_Handler::TYPE_PARAM       => 'Html',
			\Tainacan\Exposers_Handler::MAPPER_PARAM       => 'Value'
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
			\Tainacan\Exposers_Handler::TYPE_PARAM       => 'Csv',
		]);
		$request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
		$request->set_body($item_exposer_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(200, $response->get_status());
		$data = $response->get_data();
		
		$lines = explode(PHP_EOL, $data);
		$csv_lines = [];
		foreach ($lines as $line) {
			$csv_lines[] = str_getcsv($line, ';');
		}
		array_walk($csv_lines, function(&$a) use ($csv_lines) {
			if(count($a) == count($csv_lines[0])) {
				$a = array_combine($csv_lines[0], $a);
			} else {
			    
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
			\Tainacan\Exposers_Handler::MAPPER_PARAM       => 'Value',
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
	        \Tainacan\Exposers_Handler::MAPPER_PARAM  => 'Dublin Core',
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
	            $this->assertEquals($mapper->metadata[$metadatum->get_slug()]['URI'], $metadatum->get_semantic_uri());
	        }
	        $this->assertEquals($mapper->metadata[$metadatum->get_slug()]['label'], $metadatum->get_name());
	    }
	    
	}
	
	/**
	 * @group json_dl_exposer
	 */
	public function test_jsondl_exposer() {
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
	        \Tainacan\Exposers_Handler::TYPE_PARAM       => 'json-ld',
	        \Tainacan\Exposers_Handler::MAPPER_PARAM       => 'dublin-core',
	    ]);
	    $request = new \WP_REST_Request('GET', $this->namespace . '/item/' . $this->item->get_id() . '/metadata' );
	    $request->set_body($item_exposer_json);
	    $response = $this->server->dispatch($request);
	    $this->assertEquals(200, $response->get_status());
	    $data = json_decode($response->get_data());
	    
	    $this->assertEquals('http://purl.org/dc/elements/1.1/', $data->{'@context'}->dc);
	    $this->assertEquals(get_locale(), $data->{'@context'}->{'@language'});
	    $this->assertEquals($this->item->get('description'), $data->{'dc:description'}->{'@value'});
	    $this->assertEquals($this->item->get('title'), $data->{'dc:title'}->{'@value'});
	    $this->assertEquals('TestValues_exposers', $data->{'dc:language'}->{'@value'});
	    
	}
	
	/**
	 * @group exposer_urls
	 */
	/*public function test_exposer_urls() {
	    global $Tainacan_Metadata, $Tainacan_Item_Metadata;
	    
	    extract($this->create_meta_requirements());
	    
	    $id = $item->get_id();
	    $base_url = "{$this->namespace}/items/{$id}/";
	    $urls = \Tainacan\Exposers_Handler::get_exposer_urls($base_url);
	    var_dump($urls);return;
	    foreach ($urls as $type => $type_urls) {
	        foreach ($type_urls as $url) {
    	        $request = new \WP_REST_Request('GET', $url);
    	        $response = $this->server->dispatch($request);
    	        $this->assertEquals(200, $response->get_status(), $url);
	        }
	    }
	} */// TODO automate test this
	
}

?>