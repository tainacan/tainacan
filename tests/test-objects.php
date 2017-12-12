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
		
		$type = $this->tainacan_field_factory->create_field('text');
		
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
		
		$metadata = $this->tainacan_entity_factory->create_entity(
				'metadata',
				array(
					'name'   => 'metadado',
					'status' => 'publish',
					'collection' => $collection,
					'field_type' => $type
				),
				true
				);
		
		$metadata2 = $this->tainacan_entity_factory->create_entity(
				'metadata',
				array(
					'name'   => 'metadado2',
					'status' => 'publish',
					'collection' => $collection,
					'field_type' => $type
				),
				true
				);
		
		$metadata3 = $this->tainacan_entity_factory->create_entity(
				'metadata',
				array(
					'name'              => 'metadado3',
					'status'            => 'publish',
					'collection'        => $collection,
					'field_type' => $type
				),
				true
				);
		
		global $Tainacan_Items;
		
		$i = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'orange',
					'collection' => $collection,
					'add_metadata' => [
						[$metadata, 'value_1']
					]
				),
				true
				);
		$test = get_post($i->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($i->get_db_identifier(), $entity->get_db_identifier());
		
		$test = get_post($metadata->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($metadata->get_db_identifier(), $entity->get_db_identifier());
		
		$test = get_post($metadata2->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($metadata2->get_db_identifier(), $entity->get_db_identifier());
		
		$metadatas = $i->get_metadata();
		//var_dump($metadatas);
		$item_metadata = array_pop($metadatas);
		$test = get_post($item_metadata->get_metadata()->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($item_metadata->get_metadata()->get_db_identifier(), $entity->get_db_identifier());
	}
}
