<?php

namespace Tainacan\Tests;
use Tainacan\Repositories\Repository;
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 * @group architecture
 */
class Objects extends TAINACAN_UnitTestCase {
	function test_object_transformation() {
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeT',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);
		$test = get_post($x->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($x->get_db_identifier(), $entity->get_db_identifier());
		
		$collection = $this->tainacan_entity_factory->create_entity(
				'collection',
				array(
					'name'   => 'teste',
					'status' => 'publish'
				),
				true
				);
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
				'collection',
				array(
					'name'   => 'teste2',
					'status' => 'publish'
				),
				true
				);
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'   => 'metadado',
					'status' => 'publish',
					'collection' => $collection,
					'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				),
				true
				);
		
		$metadatum2 = $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'   => 'metadado2',
					'status' => 'publish',
					'collection' => $collection,
					'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				),
				true
				);
		
		$metadatum3 = $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'              => 'metadado3',
					'status'            => 'publish',
					'collection'        => $collection,
					'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				),
				true
				);
		
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		$i = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'orange',
					'collection' => $collection,
				),
				true
				);
		
        $this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum, 'value_1');
        
        $test = get_post($i->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($i->get_db_identifier(), $entity->get_db_identifier());
		
		$test = get_post($metadatum->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($metadatum->get_db_identifier(), $entity->get_db_identifier());
		
		$test = get_post($metadatum2->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($metadatum2->get_db_identifier(), $entity->get_db_identifier());
		
		$metadata = $i->get_metadata();
		$item_metadata = array_pop($metadata);
		$test = get_post($item_metadata->get_metadatum()->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($item_metadata->get_metadatum()->get_db_identifier(), $entity->get_db_identifier());
	}
    
    function test_delete_attributes() {
        
        $collection = $this->tainacan_entity_factory->create_entity(
				'collection',
				array(
					'name'   => 'test title',
                    'description' => 'test description',
					'status' => 'draft'
				),
				true
				);
                
        $collection->set_name('');
        $this->assertEquals('', $collection->get_name());
        
        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $this->assertTrue($collection->validate());
        $newCol = $Tainacan_Collections->insert($collection);
        $this->assertEquals('', $newCol->get_name());
        
        
	}
	
	function test_fetch_one() {


		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'teste',
				'status' => 'publish'
			),
			true
			);

		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'teste2',
				'status' => 'publish'
			),
			true
			);

		$collection3 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'teste3',
				'status' => 'publish'
			),
			true
			);


		
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

		$one = $Tainacan_Collections->fetch_one(['name' => 'teste2']);

		$this->assertTrue( $one instanceof \Tainacan\Entities\Collection );
		$this->assertEquals( 'teste2', $one->get_name() );

	}
}
