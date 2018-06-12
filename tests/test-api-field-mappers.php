<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Field_Mappers_Controller extends TAINACAN_UnitApiTestCase {

    protected function create_meta_requirements() {
        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name' => 'testItemFieldMappers',
                'description' => 'No description',
            ),
            true,
            true
            );
        
        $type = $this->tainacan_field_factory->create_field('text');
        
        $field = $this->tainacan_entity_factory->create_entity(
            'field',
            array(
                'name'              => 'test_FieldMappers',
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
        
        $field2 = $this->tainacan_entity_factory->create_entity(
            'field',
            array(
                'name'              => 'test_FieldMappers2',
                'description'       => 'descricao2',
                'collection'        => $collection,
                'field_type'		=> $type
            ),
            true,
            true
            );
        
        $item = $this->tainacan_entity_factory->create_entity(
            'item',
            array(
                'title'       => 'item_teste_FieldMappers',
                'description' => 'adasdasdsaadsf',
                'collection'  => $collection
            ),
            true,
            true
            );
        $this->collection = $collection;
        $this->item = $item;
        $this->field = $field;
        return ['collection' => $collection, 'item' => $item, 'field' => $field, 'field2' => $field2];
    }
    
	public function test_get_field_mappers(){

		$field_mapper_request = new \WP_REST_Request('GET', $this->namespace . '/field-mappers');

		$field_mapper_response = $this->server->dispatch($field_mapper_request);

		$data = $field_mapper_response->get_data();

		$Tainacan_Fields = \Tainacan\Exposers\Exposers::get_instance();

		$field_mappers = $Tainacan_Fields->get_mappers("OBJECT");

		$this->assertEquals(count($field_mappers), count($data));
		
	    for ($i = 0; $i < count($data); $i++) {
	        $this->assertEquals($field_mappers[$i]->slug, $data[$i]['slug']);
	        $this->assertEquals($field_mappers[$i]->name, $data[$i]['name']);
	        $this->assertEquals($field_mappers[$i]->allow_extra_fields, $data[$i]['allow_extra_fields']);
	        $this->assertEquals($field_mappers[$i]->context_url, $data[$i]['context_url']);
	        $this->assertEquals($field_mappers[$i]->metadata, $data[$i]['metadata']);
	        $this->assertEquals($field_mappers[$i]->prefix, $data[$i]['prefix']);
	        $this->assertEquals($field_mappers[$i]->sufix, $data[$i]['sufix']);
	        $this->assertEquals($field_mappers[$i]->header, $data[$i]['header']);
		}
	}
	
	public function test_update_field_mappers(){
	    extract($this->create_meta_requirements());
	    
	    $dc = new \Tainacan\Exposers\Mappers\Dublin_Core();
	    
	    $field_mapper_request = new \WP_REST_Request('POST', $this->namespace . '/field-mappers');
	    $field_mapper_json = json_encode([
	        'fields_mappers'       => [
	            ['field_id' => $field->get_id(), 'mapper_metadata' => 'contributor'],
	            ['field_id' => $field2->get_id(), 'mapper_metadata' => 'coverage']
	        ],
	        'exposer_map'          => $dc->slug
	    ]);
	    $field_mapper_request->set_body($field_mapper_json);
	    $field_mapper_response = $this->server->dispatch($field_mapper_request);
	    $this->assertEquals(200, $field_mapper_response->get_status());
	    $data = $field_mapper_response->get_data();
	    
	    $this->assertEquals('contributor', $data[0]['exposer_mapping']['dublin-core']);
	    $this->assertEquals('coverage', $data[1]['exposer_mapping']['dublin-core']);
	    
	    $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
	    
	    $db_field = $Tainacan_Fields->fetch($field->get_id());
	    $exposer_mapping = $db_field->get('exposer_mapping');
	    $this->assertEquals('contributor', $exposer_mapping['dublin-core']);
	    
	    $db_field = $Tainacan_Fields->fetch($field2->get_id());
	    $exposer_mapping = $db_field->get('exposer_mapping');
	    $this->assertEquals('coverage', $exposer_mapping['dublin-core']);
	    
	}
}

?>