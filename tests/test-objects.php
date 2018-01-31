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
		
		$field = $this->tainacan_entity_factory->create_entity(
				'field',
				array(
					'name'   => 'metadado',
					'status' => 'publish',
					'collection' => $collection,
					'field_type' => $type
				),
				true
				);
		
		$field2 = $this->tainacan_entity_factory->create_entity(
				'field',
				array(
					'name'   => 'metadado2',
					'status' => 'publish',
					'collection' => $collection,
					'field_type' => $type
				),
				true
				);
		
		$field3 = $this->tainacan_entity_factory->create_entity(
				'field',
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
						[$field, 'value_1']
					]
				),
				true
				);
		$test = get_post($i->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($i->get_db_identifier(), $entity->get_db_identifier());
		
		$test = get_post($field->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($field->get_db_identifier(), $entity->get_db_identifier());
		
		$test = get_post($field2->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($field2->get_db_identifier(), $entity->get_db_identifier());
		
		$fields = $i->get_field();
		//var_dump($fields);
		$item_metadata = array_pop($fields);
		$test = get_post($item_metadata->get_field()->get_id());
		$entity = Repository::get_entity_by_post($test);
		$this->assertEquals($item_metadata->get_field()->get_db_identifier(), $entity->get_db_identifier());
	}
}
