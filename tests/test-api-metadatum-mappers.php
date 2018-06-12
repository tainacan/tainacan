<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadatum_Mappers_Controller extends TAINACAN_UnitApiTestCase {

    protected function create_meta_requirements() {
        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name' => 'testItemMetadatumMappers',
                'description' => 'No description',
            ),
            true,
            true
            );
        
        $type = $this->tainacan_metadatum_factory->create_metadatum('text');
        
        $metadatum = $this->tainacan_entity_factory->create_entity(
            'metadatum',
            array(
                'name'              => 'test_MetadatumMappers',
                'description'       => 'descricao',
                'collection'        => $collection,
                'metadatum_type'		=> $type,
                'exposer_mapping'	=> [
                    'dublin-core' => 'language'
                ]
            ),
            true,
            true
            );
        
        $metadatum2 = $this->tainacan_entity_factory->create_entity(
            'metadatum',
            array(
                'name'              => 'test_MetadatumMappers2',
                'description'       => 'descricao2',
                'collection'        => $collection,
                'metadatum_type'		=> $type
            ),
            true,
            true
            );
        
        $item = $this->tainacan_entity_factory->create_entity(
            'item',
            array(
                'title'       => 'item_teste_MetadatumMappers',
                'description' => 'adasdasdsaadsf',
                'collection'  => $collection
            ),
            true,
            true
            );
        $this->collection = $collection;
        $this->item = $item;
        $this->metadatum = $metadatum;
        return ['collection' => $collection, 'item' => $item, 'metadatum' => $metadatum, 'metadatum2' => $metadatum2];
    }
    
	public function test_get_metadatum_mappers(){

		$metadatum_mapper_request = new \WP_REST_Request('GET', $this->namespace . '/metadatum-mappers');

		$metadatum_mapper_response = $this->server->dispatch($metadatum_mapper_request);

		$data = $metadatum_mapper_response->get_data();

		$Tainacan_Metadata = \Tainacan\Exposers\Exposers::get_instance();

		$metadatum_mappers = $Tainacan_Metadata->get_mappers("OBJECT");

		$this->assertEquals(count($metadatum_mappers), count($data));
		
	    for ($i = 0; $i < count($data); $i++) {
	        $this->assertEquals($metadatum_mappers[$i]->slug, $data[$i]['slug']);
	        $this->assertEquals($metadatum_mappers[$i]->name, $data[$i]['name']);
	        $this->assertEquals($metadatum_mappers[$i]->allow_extra_metadata, $data[$i]['allow_extra_metadata']);
	        $this->assertEquals($metadatum_mappers[$i]->context_url, $data[$i]['context_url']);
	        $this->assertEquals($metadatum_mappers[$i]->metadata, $data[$i]['metadata']);
	        $this->assertEquals($metadatum_mappers[$i]->prefix, $data[$i]['prefix']);
	        $this->assertEquals($metadatum_mappers[$i]->sufix, $data[$i]['sufix']);
	        $this->assertEquals($metadatum_mappers[$i]->header, $data[$i]['header']);
		}
	}
	
	public function test_update_metadatum_mappers(){
	    extract($this->create_meta_requirements());
	    
	    $dc = new \Tainacan\Exposers\Mappers\Dublin_Core();
	    
	    $metadatum_mapper_request = new \WP_REST_Request('POST', $this->namespace . '/metadatum-mappers');
	    $metadatum_mapper_json = json_encode([
	        'metadata_mappers'       => [
	            ['metadatum_id' => $metadatum->get_id(), 'mapper_metadata' => 'contributor'],
	            ['metadatum_id' => $metadatum2->get_id(), 'mapper_metadata' => 'coverage']
	        ],
	        'exposer_map'          => $dc->slug
	    ]);
	    $metadatum_mapper_request->set_body($metadatum_mapper_json);
	    $metadatum_mapper_response = $this->server->dispatch($metadatum_mapper_request);
	    $this->assertEquals(200, $metadatum_mapper_response->get_status());
	    $data = $metadatum_mapper_response->get_data();
	    
	    $this->assertEquals('contributor', $data[0]['exposer_mapping']['dublin-core']);
	    $this->assertEquals('coverage', $data[1]['exposer_mapping']['dublin-core']);
	    
	    $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
	    
	    $db_metadatum = $Tainacan_Metadata->fetch($metadatum->get_id());
	    $exposer_mapping = $db_metadatum->get('exposer_mapping');
	    $this->assertEquals('contributor', $exposer_mapping['dublin-core']);
	    
	    $db_metadatum = $Tainacan_Metadata->fetch($metadatum2->get_id());
	    $exposer_mapping = $db_metadatum->get('exposer_mapping');
	    $this->assertEquals('coverage', $exposer_mapping['dublin-core']);
	    
	}
	
}

?>