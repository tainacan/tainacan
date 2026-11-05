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

	/**
	 * A child term satisfies a conditional section whose rule is an ancestor.
	 * A parent does not satisfy a rule written on its child, and a flat taxonomy stays an exact match.
	 */
	function test_conditional_section_follows_term_ancestors() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Conditional hierarchy',
				'status' => 'publish',
			),
			true
		);

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'        => 'Materials',
				'status'      => 'publish',
				'collections' => array( $collection ),
			),
			true
		);

		$parent = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Painting',
			),
			true
		);
		$child = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Oil',
				'parent'   => $parent->get_id(),
			),
			true
		);
		$grandchild = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Linseed',
				'parent'   => $child->get_id(),
			),
			true
		);
		$sibling = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Watercolor',
				'parent'   => $parent->get_id(),
			),
			true
		);
		$other = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Sculpture',
			),
			true
		);

		$this->assertSame( array(), $parent->_toArray()['ancestors'] );
		$this->assertSame( array( $child->get_id(), $parent->get_id() ), $grandchild->_toArray()['ancestors'] );

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Technique',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => array(
					'taxonomy_id' => $taxonomy->get_id(),
				),
			),
			true
		);

		$sections = \Tainacan\Repositories\Metadata_Sections::get_instance();
		$section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'       => 'Painting details',
				'status'     => 'publish',
				'collection' => $collection,
			),
			true
		);
		$section->set_is_conditional_section( 'yes' );
		$section->set_conditional_section_rules( array(
			$metadatum->get_id() => array( (string) $parent->get_id() ),
		) );
		$this->assertTrue( $section->validate(), json_encode( $section->get_errors() ) );
		$sections->update( $section );

		$render = function( $value ) use ( $collection, $metadatum, $section ) {
			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'Conditional item',
					'collection' => $collection,
					'status'     => 'publish',
				),
				true
			);
			$this->tainacan_item_metadata_factory->create_item_metadata( $item, $metadatum, $value );
			return $item->get_metadata_section_as_html( $section, array( 'hide_empty' => false ) );
		};

		$this->assertNotFalse( strpos( $render( $parent ), 'Painting details' ) );
		$this->assertNotFalse( strpos( $render( $child ), 'Painting details' ) );
		$this->assertNotFalse( strpos( $render( $grandchild ), 'Painting details' ) );
		$this->assertSame( '', $render( $other ) );

		$section->set_conditional_section_rules( array(
			$metadatum->get_id() => array( (string) $child->get_id() ),
		) );
		$this->assertTrue( $section->validate(), json_encode( $section->get_errors() ) );
		$sections->update( $section );

		$this->assertSame( '', $render( $parent ) );
		$this->assertSame( '', $render( $sibling ) );
		$this->assertNotFalse( strpos( $render( $child ), 'Painting details' ) );
		$this->assertNotFalse( strpos( $render( $grandchild ), 'Painting details' ) );

		$metadatum->set_multiple( 'yes' );
		$this->assertTrue( $metadatum->validate(), json_encode( $metadatum->get_errors() ) );
		\Tainacan\Repositories\Metadata::get_instance()->update( $metadatum );

		$section->set_conditional_section_rules( array(
			$metadatum->get_id() => array( (string) $parent->get_id() ),
		) );
		$this->assertTrue( $section->validate(), json_encode( $section->get_errors() ) );
		$sections->update( $section );

		$this->assertNotFalse( strpos( $render( array( $other, $child ) ), 'Painting details' ) );
		$this->assertNotFalse( strpos( $render( array( $other, $sibling ) ), 'Painting details' ) );
		$this->assertSame( '', $render( array( $other ) ) );

		$flat = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'Flat labels',
				'status'       => 'publish',
				'hierarchical' => 'no',
				'collections'  => array( $collection ),
			),
			true
		);
		$flat_a = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $flat->get_db_identifier(),
				'name'     => 'Alpha',
			),
			true
		);
		$flat_b = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $flat->get_db_identifier(),
				'name'     => 'Beta',
			),
			true
		);
		$flat_metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Label',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => array(
					'taxonomy_id' => $flat->get_id(),
				),
			),
			true
		);
		$flat_section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'       => 'Alpha details',
				'status'     => 'publish',
				'collection' => $collection,
			),
			true
		);
		$flat_section->set_is_conditional_section( 'yes' );
		$flat_section->set_conditional_section_rules( array(
			$flat_metadatum->get_id() => array( (string) $flat_a->get_id() ),
		) );
		$this->assertTrue( $flat_section->validate(), json_encode( $flat_section->get_errors() ) );
		$sections->update( $flat_section );
		$render_flat = function( $value ) use ( $collection, $flat_metadatum, $flat_section ) {
			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'Flat item',
					'collection' => $collection,
					'status'     => 'publish',
				),
				true
			);
			$this->tainacan_item_metadata_factory->create_item_metadata( $item, $flat_metadatum, $value );
			return $item->get_metadata_section_as_html( $flat_section, array( 'hide_empty' => false ) );
		};

		$this->assertNotFalse( strpos( $render_flat( $flat_a ), 'Alpha details' ) );
		$this->assertSame( '', $render_flat( $flat_b ) );
		$this->assertSame( array(), $flat_a->_toArray()['ancestors'] );
	}

	/**
	 * A select matches the stored option exactly, and every rule must match.
	 */
	function test_conditional_section_select_matches_exactly_and_requires_every_rule() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Conditional select',
				'status' => 'publish',
			),
			true
		);
		$code = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Code',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Selectbox',
				'metadata_type_options' => array(
					'options' => array( '7', '42' ),
				),
			),
			true
		);
		$room = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Room',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Selectbox',
				'metadata_type_options' => array(
					'options' => array( 'A', 'B' ),
				),
			),
			true
		);
		$section = $this->tainacan_entity_factory->create_entity(
			'Metadata_Section',
			array(
				'name'       => 'Code details',
				'status'     => 'publish',
				'collection' => $collection,
			),
			true
		);
		$section->set_is_conditional_section( 'yes' );
		$section->set_conditional_section_rules( array(
			$code->get_id() => array( '7' ),
			$room->get_id() => array( 'A' ),
		) );
		$this->assertTrue( $section->validate(), json_encode( $section->get_errors() ) );
		\Tainacan\Repositories\Metadata_Sections::get_instance()->update( $section );

		$render = function( $code_value, $room_value ) use ( $collection, $code, $room, $section ) {
			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'Select item',
					'collection' => $collection,
					'status'     => 'publish',
				),
				true
			);
			$this->tainacan_item_metadata_factory->create_item_metadata( $item, $code, $code_value );
			$this->tainacan_item_metadata_factory->create_item_metadata( $item, $room, $room_value );
			return $item->get_metadata_section_as_html( $section, array( 'hide_empty' => false ) );
		};

		$this->assertSame( '', $render( '42', 'A' ) );
		$this->assertSame( '', $render( '7', 'B' ) );
		$this->assertNotFalse( strpos( $render( '7', 'A' ), 'Code details' ) );
	}

}