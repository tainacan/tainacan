<?php

namespace Tainacan\Tests;
use Tainacan\Metadata_Types;
/**
 * Class Metadatum
 *
 * @package Test_Tainacan
 */

/**
 * Metadatum test case.
 * @group metadata
 */
class MetadataSection extends TAINACAN_UnitTestCase {

	/**
	 * Test insert a regular metadatum with type
	 */
	function test_add() {
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
			true
		);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		$section = $Tainacan_Metadata_Section->fetch($metadata_section->get_id());

		$this->assertEquals($test->get_name(), 'metadado');
		$this->assertEquals($test->get_description(), 'descricao');
		$this->assertEquals($test->get_collection_id(), $collection->get_id());
		$this->assertEquals($test->get_metadata_section_id(), $metadata_section->get_id());

		$metadata_list = $metadata_section->get_metadata_list();
		$this->assertEquals(count($metadata_list), 1);
		$this->assertEquals($test->get_id(), $metadata_list[0]);
		
		$this->assertTrue((bool) $test->get_accept_suggestion());
	}

	// function test_remove() {
	// 	return;
	// }

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
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'metadata_section_id' => $metadata_section_a->get_id(),
			),
			true
		);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		
		$section_a = $Tainacan_Metadata_Section->fetch($metadata_section_a->get_id());
		$section_b = $Tainacan_Metadata_Section->fetch($metadata_section_b->get_id());

		$metadata_list_a = $section_a->get_metadata_list();
		$metadata_list_b = $section_b->get_metadata_list();
		$this->assertContains($test->get_id(), $metadata_list_a);
		$this->assertNotContains($test->get_id(), $metadata_list_b);

		$test->set_metadata_section_id($metadata_section_b->get_id());
		$this->assertTrue($test->validate(), json_encode($test->get_errors()));
		$Tainacan_Metadata->update($test);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		
		$metadata_list_a = $section_a->get_metadata_list();
		$metadata_list_b = $section_b->get_metadata_list();
		$this->assertNotContains($test->get_id(), $metadata_list_a);
		$this->assertContains($test->get_id(), $metadata_list_b);

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

		$metadatum1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'metadatum1',
				'description' => 'descricao',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'status' => 'publish'
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
				'status' => 'publish'
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
				'status' => 'publish'
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
				'status' => 'publish'
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
				'status' => 'publish'
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
				'status' => 'publish'
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
				'metadata_list' => [$metadatum1->get_id(), $metadatum3->get_id(), $metadatum2->get_id()]
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
				'metadata_list' => [$metadatum_a->get_id(), $metadatum_b->get_id(), $metadatum_c->get_id()]
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

	}

}