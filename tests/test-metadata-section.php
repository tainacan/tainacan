<?php

namespace Tainacan\Tests;
use Tainacan\Metadata_Types;
/**
 * Class Metadatum
 *
 * @package Test_Tainacan
 */

/**
 * MetadataSection test case.
 * @group metadata
 */
class MetadataSection extends TAINACAN_UnitTestCase {

	/**
	 * Test create a metadata section
	 */
	function test_create() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Metadata_Section = \Tainacan\Repositories\Metadata_Sections::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste'
			),
			true
		);

		$metadata_section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadado',
				'description' => 'descricao',
				'collection' => $collection,
				'accept_suggestion' => true,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section->get_id(),
			),
			true,
			true
		);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		$Tainacan_Metadata_Section->fetch($metadata_section->get_id());

		$this->assertEquals($test->get_name(), 'metadado');
		$this->assertEquals($test->get_description(), 'descricao');
		$this->assertEquals($test->get_collection_id(), $collection->get_id());
		$this->assertEquals($test->get_metadata_section_id(), $metadata_section->get_id());

		$metadata_list = $metadata_section->get_metadata_object_list();
		$this->assertEquals(count($metadata_list), 1);
		$this->assertEquals($test->get_id(), $metadata_list[0]->get_id());
		
		$this->assertTrue((bool) $test->get_accept_suggestion());
	}

	/**
	 * Test remove a metadata section
	 */
	function test_remove() {
		$Tainacan_Metadata_Section = \Tainacan\Repositories\Metadata_Sections::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste'
			),
			true
		);

		$metadata_section_to_delete = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$metadata_section_no_delete = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section',
				'description' => 'Section Description',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadado',
				'description' => 'descricao',
				'collection' => $collection,
				'accept_suggestion' => true,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section_no_delete->get_id(),
			),
			true,
			true
		);

		// $Tainacan_Metadata->fetch($metadatum->get_id());
		$section_id = $metadata_section_to_delete->get_id();
		$section = $Tainacan_Metadata_Section->fetch($section_id);
		$section = $Tainacan_Metadata_Section->delete($section);
		$section_empty = $Tainacan_Metadata_Section->fetch($section_id);
		$this->assertEquals($section_id, $section->get_id() );
		$this->assertTrue(empty($section_empty));

		$this->setExpectedException(\Exception::class);
		$this->expectExceptionMessage('The metadata section must not contain metadata before deleted');
		$section = $Tainacan_Metadata_Section->fetch($metadata_section_no_delete->get_id());
		$Tainacan_Metadata_Section->delete($section);

	}

	function test_change_section() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Metadata_Section = \Tainacan\Repositories\Metadata_Sections::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste'
			),
			true
		);

		$metadata_section_a = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section A',
				'description' => 'Section Description',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$metadata_section_b = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section B',
				'description' => 'Section Description',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadado',
				'description' => 'descricao',
				'collection' => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section_a->get_id(),
			),
			true
		);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		
		$section_a = $Tainacan_Metadata_Section->fetch($metadata_section_a->get_id());
		$section_b = $Tainacan_Metadata_Section->fetch($metadata_section_b->get_id());

		$metadata_list_a = $section_a->get_metadata_object_list();
		$metadata_list_b = $section_b->get_metadata_object_list();
		$metadata_list_a = array_map(function($e) {
			return $e->_toArray();
		}, $metadata_list_a);
		$metadata_list_b = array_map(function($e) {
			return $e->_toArray();
		}, $metadata_list_b);

		$this->assertContains($test->get_id(), array_column($metadata_list_a, 'id'));
		$this->assertNotContains($test->get_id(), array_column($metadata_list_b, 'id'));

		$test->set_metadata_section_id($metadata_section_b->get_id());
		$this->assertTrue($test->validate(), json_encode($test->get_errors()));
		$Tainacan_Metadata->update($test);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		
		$metadata_list_a = $section_a->get_metadata_object_list();
		$metadata_list_b = $section_b->get_metadata_object_list();
		$metadata_list_a = array_map(function($e) {
			return $e->_toArray();
		}, $metadata_list_a);
		$metadata_list_b = array_map(function($e) {
			return $e->_toArray();
		}, $metadata_list_b);

		$this->assertNotContains($test->get_id(), array_column($metadata_list_a, 'id'));
		$this->assertContains($test->get_id(), array_column($metadata_list_b, 'id'));

	}

	/**
	 *
	 */
	function test_ordenation_metadata(){
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste'
			),
			true
		);

		$metadata_section_1 = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section 1',
				'description' => 'Section 1 Description',
				'collection' => $collection,
				'status'      => 'publish',
			),
			true
		);

		$metadata_section_a = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'        => 'Section A',
				'description' => 'Section A Description',
				'collection' => $collection,
				'status'      => 'publish',
			),
			true
		);

		$metadatum1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadatum1',
				'description' => 'descricao',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish',
				'metadata_section_id' => $metadata_section_1->get_id()
			),
			true
		);

		$metadatum2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadatum2',
				'description' => 'metadatum2',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish',
				'metadata_section_id' => $metadata_section_1->get_id()
			),
			true
		);

		$metadatum3 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadatum3',
				'description' => 'metadatum3',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish',
				'metadata_section_id' => $metadata_section_1->get_id()
			),
			true
		);

		$metadatum_a = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadatum_a',
				'description' => 'descricao_a',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish',
				'metadata_section_id' => $metadata_section_a->get_id()
			),
			true
		);

		$metadatum_b = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadatum_b',
				'description' => 'metadatum_b',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish',
				'metadata_section_id' => $metadata_section_a->get_id()
			),
			true
		);

		$metadatum_c = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadatum_c',
				'description' => 'metadatum_c',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish',
				'metadata_section_id' => $metadata_section_a->get_id()
			),
			true
		);

		$collection->set_metadata_order(
			[
				array( 'id' => $metadatum3->get_id(), 'enabled' => false ),
				array( 'id' => $metadatum2->get_id(), 'enabled' => true ),
				array( 'id' => $metadatum1->get_id(), 'enabled' => true )
			]
		);

		$update_collection = $Tainacan_Collections->update( $collection );
		$metadata_ordinate = $Tainacan_Metadata->fetch_by_collection( $update_collection );
		$metadata_ordinate_enabled = $Tainacan_Metadata->fetch_by_collection( $update_collection, [ 'include_disabled' => true ] );

		$this->assertEquals( 'metadatum2', $metadata_ordinate[0]->get_name() );
		$this->assertEquals( 'metadatum3', $metadata_ordinate_enabled[0]->get_name() );
		$this->assertFalse($metadata_ordinate_enabled[0]->get_enabled_for_collection());
		$this->assertTrue($metadata_ordinate_enabled[1]->get_enabled_for_collection());
		$this->assertTrue($metadata_ordinate_enabled[2]->get_enabled_for_collection());

		$collection->set_metadata_section_order(
			[
				array( 'id' => $metadata_section_1->get_id(), 'enabled' => true, 'metadata_order' => [
					array( 'id' => $metadatum2->get_id(), 'enabled' => true ),
					array( 'id' => $metadatum3->get_id(), 'enabled' => false ),
					array( 'id' => $metadatum1->get_id(), 'enabled' => true )
				] ),
				array( 'id' => $metadata_section_a->get_id(), 'enabled' => false, 'metadata_order' => [
					array( 'id' => $metadatum_c->get_id(), 'enabled' => false ),
					array( 'id' => $metadatum_b->get_id(), 'enabled' => true ),
					array( 'id' => $metadatum_a->get_id(), 'enabled' => true )
				])
			]
		);

		$update_collection = $Tainacan_Collections->update( $collection );
		$metadata_ordinate = $Tainacan_Metadata->fetch_by_collection( $update_collection );
		$metadata_ordinate_enabled = $Tainacan_Metadata->fetch_by_collection( $update_collection, [ 'include_disabled' => true ] );

		$this->assertEquals( 'metadatum2', $metadata_ordinate[0]->get_name() );
		$this->assertEquals( 'metadatum1', $metadata_ordinate[1]->get_name() );
		$this->assertEquals( 4, count($metadata_ordinate) );

		$this->assertEquals( 8, count($metadata_ordinate_enabled) );
		$this->assertEquals( 'metadatum3', $metadata_ordinate_enabled[1]->get_name() );
		$this->assertTrue($metadata_ordinate_enabled[0]->get_enabled_for_collection());
		$this->assertFalse($metadata_ordinate_enabled[1]->get_enabled_for_collection());
		$this->assertTrue($metadata_ordinate_enabled[2]->get_enabled_for_collection());

		//changing the metadata section of the metadata without changing the ordering
		$metadatum2->set_metadata_section_id($metadata_section_a->get_id());
		$this->assertTrue($metadatum2->validate(), json_encode($metadatum2->get_errors()));
		$Tainacan_Metadata->update($metadatum2);

		$metadatum_c->set_metadata_section_id($metadata_section_1->get_id());
		$this->assertTrue($metadatum_c->validate(), json_encode($metadatum_c->get_errors()));
		$Tainacan_Metadata->update($metadatum_c);

		$metadata_ordinate_enabled = $Tainacan_Metadata->fetch_by_collection( $update_collection, [ 'include_disabled' => true ] );
		$this->assertEquals( 8, count($metadata_ordinate_enabled) );
		$this->assertEquals( 'metadatum3', $metadata_ordinate_enabled[0]->get_name() );
		$this->assertEquals( 'metadatum_b', $metadata_ordinate_enabled[3]->get_name() );
		$this->assertEquals( 'metadatum2', $metadata_ordinate_enabled[5]->get_name() );
		$this->assertEquals( 'metadatum_c', $metadata_ordinate_enabled[2]->get_name() );

	}

}