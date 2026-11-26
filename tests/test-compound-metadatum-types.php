<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities\Item_Metadata_Entity;

/**
 * Compound Metadatum Types test case.
 * @group compound_metadatum
 */
class CompoundMetadatumTypes extends TAINACAN_UnitTestCase {

	function test_compound_metadata_types() {
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
			),
			true
		);

		$metadatum_child1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta2',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
			),
			true
		);

		$metadatum_child2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta3',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
			),
			true
		);

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'         => 'item test',
				'description'   => 'adasdasdsa',
				'collection'    => $collection,
				'status'        => 'publish',
			),
			true
		);

		$item_metadata1 = new Item_Metadata_Entity($i, $metadatum_child1);
		$item_metadata1->set_value('Red');

		$item_metadata1->validate();

		$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);

		$item_metadata = new Item_Metadata_Entity($i, $metadatum_child2, null, $item_metadata1->get_parent_meta_id());
		$item_metadata->set_value('Blue');

		$item_metadata->validate();

		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		$compoundItem = new Item_Metadata_Entity($i, $metadatum);

		$compoundValue = $compoundItem->get_value();

		$this->assertTrue( is_array($compoundValue), 'value of a compound should return array' );
		$this->assertEquals( 2, sizeof($compoundValue), 'value should have 2 item metadata' );

		$this->assertTrue( isset($compoundValue[$metadatum_child1->get_id()]), 'First element of value must be set' );
		$this->assertTrue( isset($compoundValue[$metadatum_child2->get_id()]), 'Second element of value must be set' );

		$this->assertTrue( $compoundValue[$metadatum_child1->get_id()] instanceof Item_Metadata_Entity , 'First element of value should be an item metadata entity' );
		$this->assertTrue( $compoundValue[$metadatum_child2->get_id()] instanceof Item_Metadata_Entity , 'Second element of value should be an item metadata entity' );

		$this->assertEquals( 'Red', $compoundValue[$metadatum_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Blue', $compoundValue[$metadatum_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );
	}

	function test_multiple_compound_metadata_types() {
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'yes'
				),
				true
			);

		$metadatum_child1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta2',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
			),
			true
		);

		$metadatum_child2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta3',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
			),
			true
		);

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'         => 'item test',
				'description'   => 'adasdasdsa',
				'collection'    => $collection,
				'status'		   => 'publish',
			),
			true
		);

		// First Instance
		$item_metadata1 = new Item_Metadata_Entity($i, $metadatum_child1);
		$item_metadata1->set_value('Red');
		$item_metadata1->validate();
		$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);

		$item_metadata = new Item_Metadata_Entity($i, $metadatum_child2, null, $item_metadata1->get_parent_meta_id());
		$item_metadata->set_value('Blue');
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		// Second Instance
		$item_metadata3 = new Item_Metadata_Entity($i, $metadatum_child1);
		$item_metadata3->set_value('Green');
		$item_metadata3->validate();
		$item_metadata3 = $Tainacan_Item_Metadata->insert($item_metadata3);


		$item_metadata = new Item_Metadata_Entity($i, $metadatum_child2, null, $item_metadata3->get_parent_meta_id());
		$item_metadata->set_value('Yellow');
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		$compoundItem = new Item_Metadata_Entity($i, $metadatum);
		$compoundValue = $compoundItem->get_value();

		$this->assertTrue( is_array($compoundValue), 'value of a compound should return array' );
		$this->assertEquals( 2, sizeof($compoundValue), 'value should have 2 values' );

		$this->assertTrue( is_array($compoundValue[0]), 'value of a compound should return array' );
		$this->assertTrue( is_array($compoundValue[1]), 'value of a compound should return array' );

		$this->assertTrue( isset($compoundValue[0][$metadatum_child1->get_id()]), 'First element of value must be set' );
		$this->assertTrue( isset($compoundValue[1][$metadatum_child2->get_id()]), 'Second element of value must be set' );

		$this->assertTrue( $compoundValue[0][$metadatum_child1->get_id()] instanceof Item_Metadata_Entity , 'First element of value should be an item metadata entity' );
		$this->assertTrue( $compoundValue[1][$metadatum_child2->get_id()] instanceof Item_Metadata_Entity , 'Second element of value should be an item metadata entity' );

		$this->assertEquals( 'Red', $compoundValue[0][$metadatum_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Blue', $compoundValue[0][$metadatum_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );

		$this->assertEquals( 'Green', $compoundValue[1][$metadatum_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Yellow', $compoundValue[1][$metadatum_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );

		$item_metadata_removed = $Tainacan_Item_Metadata->remove_compound_value($item_metadata3->get_parent_meta_id());
		$compoundItem = new Item_Metadata_Entity($i, $metadatum);
		$compoundValue = $compoundItem->get_value();

		$this->assertTrue( is_array($compoundValue), 'value of a compound should return array' );
		$this->assertEquals( 1, sizeof($compoundValue), 'value should have 1 values' );

		$this->assertEquals( 'Red', $compoundValue[0][$metadatum_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Blue', $compoundValue[0][$metadatum_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );
	}

	function test_validations_taxonomy_in_multiple() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'yes'
			),
			true
		);

		$newMetadatum = new \Tainacan\Entities\Metadatum();
		$newMetadatum->set_name('test_multiple');
		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Taxonomy');
		$newMetadatum->set_parent($metadatum->get_id());

		$this->assertFalse($newMetadatum->validate(), 'You cant add a taxonomy metadatum inside a multiple compound metadatum');

		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Text');
		$this->assertTrue($newMetadatum->validate());
	}

	function test_validations_taxonomy_in_multiple_2() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'no'
			),
			true
		);

		$newMetadatum = new \Tainacan\Entities\Metadatum();
		$newMetadatum->set_name('test_multiple');
		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Taxonomy');
		$newMetadatum->set_parent($metadatum->get_id());

		$this->assertTrue($newMetadatum->validate(), 'You can add a taxonomy metadatum inside a not multiple compound metadatum');
		$newMetadatum = $Tainacan_Metadata->insert($newMetadatum);

		$metadatum->set_multiple('yes');

		$this->assertFalse($metadatum->validate(), 'You cant turn a compound metadatum into multiple when there is a taxonomy metadatum inside it');
	}

	function test_validations_multiple_metadata() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'no'
			),
			true
		);

		$newMetadatum = new \Tainacan\Entities\Metadatum();
		$newMetadatum->set_name('test_multiple');
		$newMetadatum->set_multiple('yes');
		$newMetadatum->set_metadata_type('Tainacan\Metadata_Types\Text');
		$newMetadatum->set_parent($metadatum->get_id());

		$this->assertFalse($newMetadatum->validate(), 'You cant add a multiple metadatum inside a compound metadatum');

		$newMetadatum->set_multiple('no');

		$this->assertTrue($newMetadatum->validate());
	}

	function test_validations_metadata_order() {
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status' => 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'	 => 'publish',
			),
			true
		);

		$metadatum_child1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta2',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
			),
			true
		);

		$metadatum_child2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta3',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $metadatum->get_id(),
			),
			true
		);

		$metadatum1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta3',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish'
			),
			true
		);

		$metadatum2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta4',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'	 => 'publish'
			),
			true
		);

		$order = [
			['id' => $metadatum->get_id(), 'enabled' => true],
			//['id' => $metadatum_child1->get_id(), 'enabled' => true],
			//['id' => $metadatum_child2->get_id(), 'enabled' => true],
			['id' => $metadatum1->get_id(), 'enabled' => true],
			['id' => $metadatum2->get_id(), 'enabled' => true]
		];

		$collection->set_metadata_order($order);
		$collection->validate();
		\tainacan_collections()->insert($collection);

		$metadata_order = $Tainacan_Collections->fetch( $collection->get_id(), 'OBJECT' )->get_metadata_order();
		$this->assertEquals($metadata_order[0]['id'], $order[0]['id']);
		//$this->assertEquals($metadata_order[1]['id'], $order[1]['id']);
		//$this->assertEquals($metadata_order[2]['id'], $order[2]['id']);
		$this->assertEquals($metadata_order[1]['id'], $order[1]['id']);
		$this->assertEquals($metadata_order[2]['id'], $order[2]['id']);

		$order = [
			['id' => $metadatum1->get_id(), 'enabled' => true],
			['id' => $metadatum2->get_id(), 'enabled' => true],
			['id' => $metadatum->get_id(), 'enabled' => true],
			//['id' => $metadatum_child1->get_id(), 'enabled' => true],
			//['id' => $metadatum_child2->get_id(), 'enabled' => true]
		];

		$collection->set_metadata_order($order);
		$collection->validate();
		\tainacan_collections()->insert($collection);

		$metadata_order = $Tainacan_Collections->fetch( $collection->get_id(), 'OBJECT' )->get_metadata_order();
		$this->assertEquals($metadata_order[0]['id'], $order[0]['id']);
		$this->assertEquals($metadata_order[1]['id'], $order[1]['id']);
		$this->assertEquals($metadata_order[2]['id'], $order[2]['id']);
		//$this->assertEquals($metadata_order[3]['id'], $order[3]['id']);
		//$this->assertEquals($metadata_order[4]['id'], $order[4]['id']);

		// $order = [
		// 	['id' => $metadatum1->get_id(), 'enabled' => true],
		// 	['id' => $metadatum->get_id(), 'enabled' => true],
		// 	['id' => $metadatum2->get_id(), 'enabled' => true],
		// 	//['id' => $metadatum_child1->get_id(), 'enabled' => true],
		// 	//['id' => $metadatum_child2->get_id(), 'enabled' => true]
		// ];

		// $collection->set_metadata_order($order);
		// $this->assertFalse($collection->validate());
	}

	/**
	 * Creates a collection with a unique compound metadatum and two text children.
	 *
	 * @return array The item, the compound metadatum and its two children.
	 */
	private function create_unique_compound_scenario() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'unique compound',
				'status' => 'publish',
			),
			true
		);

		$compound = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Address',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'status'        => 'publish',
				'multiple'      => 'no',
			),
			true
		);

		$street = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Street',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish',
				'parent'        => $compound->get_id(),
			),
			true
		);

		$city = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'City',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'status'        => 'publish',
				'parent'        => $compound->get_id(),
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'item with address',
				'collection' => $collection,
				'status'     => 'publish',
			),
			true
		);

		return array( $item, $compound, $street, $city );
	}

	/**
	 * Saves a child of a compound metadatum.
	 *
	 * @param \Tainacan\Entities\Item      $item           The item.
	 * @param \Tainacan\Entities\Metadatum $metadatum      The child metadatum.
	 * @param string                       $value          The value.
	 * @param int|null                     $parent_meta_id The compound meta id, when the client knows it.
	 *
	 * @return Item_Metadata_Entity
	 */
	private function save_compound_child( $item, $metadatum, $value, $parent_meta_id = null ) {
		$item_metadata = new Item_Metadata_Entity( $item, $metadatum, null, $parent_meta_id );
		$item_metadata->set_value( $value );
		$this->assertTrue( $item_metadata->validate() );

		return \Tainacan\Repositories\Item_Metadata::get_instance()->insert( $item_metadata );
	}

	/**
	 * Counts the rows that store the value of a compound metadatum for an item.
	 *
	 * @param \Tainacan\Entities\Item      $item     The item.
	 * @param \Tainacan\Entities\Metadatum $compound The metadatum whose rows are counted: the compound or one of its children.
	 *
	 * @return int
	 */
	private function count_compound_rows( $item, $compound ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $item->get_id(), $compound->get_id() ) );
	}

	/**
	 * Stores a unique compound value split in several rows, as older versions left it:
	 * an old street, the city and a new street, each in its own compound row.
	 *
	 * @param \Tainacan\Entities\Item      $item     The item.
	 * @param \Tainacan\Entities\Metadatum $compound The unique compound metadatum.
	 * @param \Tainacan\Entities\Metadatum $street   The street child.
	 * @param \Tainacan\Entities\Metadatum $city     The city child.
	 *
	 * @return array The meta ids of the three compound rows, oldest first.
	 */
	private function create_split_unique_compound_value( $item, $compound, $street, $city ) {
		$old_street_id = add_post_meta( $item->get_id(), $street->get_id(), 'Rua antiga' );
		$first_row     = add_post_meta( $item->get_id(), $compound->get_id(), array( $old_street_id ) );
		$city_id       = add_post_meta( $item->get_id(), $city->get_id(), 'Brasília' );
		$second_row    = add_post_meta( $item->get_id(), $compound->get_id(), array( $city_id ) );
		$new_street_id = add_post_meta( $item->get_id(), $street->get_id(), 'Rua nova' );
		$third_row     = add_post_meta( $item->get_id(), $compound->get_id(), array( $new_street_id ) );

		return array( $first_row, $second_row, $third_row );
	}

	/**
	 * Reads the value of a child of a unique compound metadatum.
	 *
	 * @param \Tainacan\Entities\Item      $item     The item.
	 * @param \Tainacan\Entities\Metadatum $compound The unique compound metadatum.
	 * @param \Tainacan\Entities\Metadatum $child    The child metadatum.
	 *
	 * @return string|null The child value, or null when the compound has no value for it.
	 */
	private function get_compound_child_value( $item, $compound, $child ) {
		$value = ( new Item_Metadata_Entity( $item, $compound ) )->get_value();

		return isset( $value[ $child->get_id() ] ) ? $value[ $child->get_id() ]->get_value() : null;
	}

	/**
	 * Children of a unique compound saved without the compound meta id, such as two fields
	 * saved by the item form before the first answer arrives, stay in a single compound value.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/711
	 */
	public function test_unique_compound_children_saved_without_parent_meta_id_share_one_value() {
		list( $item, $compound, $street, $city ) = $this->create_unique_compound_scenario();

		$saved_street = $this->save_compound_child( $item, $street, 'Rua A' );
		$saved_city   = $this->save_compound_child( $item, $city, 'Brasília' );

		$this->assertEquals( 1, $this->count_compound_rows( $item, $compound ), 'A unique compound must keep a single value' );
		$this->assertEquals( $saved_street->get_parent_meta_id(), $saved_city->get_parent_meta_id() );

		$value = ( new Item_Metadata_Entity( $item, $compound ) )->get_value();

		$this->assertEquals( 'Rua A', $value[ $street->get_id() ]->get_value() );
		$this->assertEquals( 'Brasília', $value[ $city->get_id() ]->get_value() );
	}

	/**
	 * Saving the same child of a unique compound again without the compound meta id
	 * updates its value instead of keeping the first one.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/711
	 */
	public function test_unique_compound_child_saved_again_without_parent_meta_id_is_updated() {
		list( $item, $compound, $street ) = $this->create_unique_compound_scenario();

		$this->save_compound_child( $item, $street, 'Rua A' );
		$this->save_compound_child( $item, $street, 'Rua B' );

		$value = ( new Item_Metadata_Entity( $item, $compound ) )->get_value();

		$this->assertEquals( 1, $this->count_compound_rows( $item, $compound ) );
		$this->assertEquals( 'Rua B', $value[ $street->get_id() ]->get_value() );

		global $wpdb;
		$street_rows = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $item->get_id(), $street->get_id() ) );
		$this->assertEquals( 1, $street_rows, 'The old child value must not be left behind' );
	}

	/**
	 * Items saved before this fix may already have a unique compound split in several rows.
	 * Its value joins them, the most recent child value winning, and an edit to any child shows up.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/711
	 */
	public function test_unique_compound_split_in_several_rows_shows_every_child_and_the_latest_edit() {
		list( $item, $compound, $street, $city ) = $this->create_unique_compound_scenario();

		// Rows as they were left by older versions: one compound value per saved child.
		list( , $second_compound_id ) = $this->create_split_unique_compound_value( $item, $compound, $street, $city );

		$value = ( new Item_Metadata_Entity( $item, $compound ) )->get_value();

		$this->assertEquals( 'Rua nova', $value[ $street->get_id() ]->get_value(), 'The most recent value of a child must be shown' );
		$this->assertEquals( 'Brasília', $value[ $city->get_id() ]->get_value(), 'Children saved in other rows must not be hidden' );

		// Editing the city through its own compound row is reflected when the item is read again.
		$this->save_compound_child( $item, $city, 'Goiânia', (int) $second_compound_id );

		$value = ( new Item_Metadata_Entity( $item, $compound ) )->get_value();
		$this->assertEquals( 'Goiânia', $value[ $city->get_id() ]->get_value() );
	}

	/**
	 * Multiple compounds still create one value per new group and keep their order.
	 */
	public function test_multiple_compound_children_saved_without_parent_meta_id_create_new_values() {
		list( $item, $compound, $street ) = $this->create_unique_compound_scenario();
		$compound->set_multiple( 'yes' );
		$compound->validate();
		$compound = \Tainacan\Repositories\Metadata::get_instance()->insert( $compound );

		$this->save_compound_child( $item, $street, 'Rua A' );
		$this->save_compound_child( $item, $street, 'Rua B' );

		$this->assertEquals( 2, $this->count_compound_rows( $item, $compound ) );

		$values = ( new Item_Metadata_Entity( $item, $compound ) )->get_value();

		$this->assertCount( 2, $values );
		$this->assertEquals( 'Rua A', $values[0][ $street->get_id() ]->get_value() );
		$this->assertEquals( 'Rua B', $values[1][ $street->get_id() ]->get_value() );
	}

	/**
	 * In a unique compound split by older versions, a child kept only in an older row
	 * can be cleared without the compound meta id.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/711
	 */
	public function test_unique_compound_split_child_kept_only_in_an_older_row_can_be_cleared() {
		list( $item, $compound, $street, $city ) = $this->create_unique_compound_scenario();
		$this->create_split_unique_compound_value( $item, $compound, $street, $city );

		$this->save_compound_child( $item, $city, '' );

		$this->assertNull( $this->get_compound_child_value( $item, $compound, $city ) );
		$this->assertEquals( 0, $this->count_compound_rows( $item, $city ), 'No copy of the cleared child may be left behind' );
		$this->assertEquals( 'Rua nova', $this->get_compound_child_value( $item, $compound, $street ) );
	}

	/**
	 * In a unique compound split by older versions, clearing a child through the row it is shown from,
	 * as the item form does, must not bring back an older copy of it from another row.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/711
	 */
	public function test_unique_compound_split_child_cleared_through_its_row_does_not_show_an_older_copy() {
		list( $item, $compound, $street, $city ) = $this->create_unique_compound_scenario();

		list( , , $third_row ) = $this->create_split_unique_compound_value( $item, $compound, $street, $city );

		$this->save_compound_child( $item, $street, '', (int) $third_row );

		$this->assertNull( $this->get_compound_child_value( $item, $compound, $street ), 'The old street must not show up again' );
		$this->assertEquals( 0, $this->count_compound_rows( $item, $street ) );
		$this->assertEquals( 'Brasília', $this->get_compound_child_value( $item, $compound, $city ) );
	}

	/**
	 * In a unique compound split by older versions, clearing a child without the compound meta id
	 * must not bring back an older copy of it from another row.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/711
	 */
	public function test_unique_compound_split_child_cleared_without_parent_meta_id_does_not_show_an_older_copy() {
		list( $item, $compound, $street, $city ) = $this->create_unique_compound_scenario();
		$this->create_split_unique_compound_value( $item, $compound, $street, $city );

		$this->save_compound_child( $item, $street, '' );

		$this->assertNull( $this->get_compound_child_value( $item, $compound, $street ), 'The old street must not show up again' );
		$this->assertEquals( 0, $this->count_compound_rows( $item, $street ) );
		$this->assertEquals( 'Brasília', $this->get_compound_child_value( $item, $compound, $city ) );
	}

	/**
	 * In a unique compound split by older versions, saving a child keeps a single copy of it,
	 * so each edit leaves the item with fewer compound rows.
	 *
	 * @see https://github.com/tainacan/tainacan/issues/711
	 */
	public function test_unique_compound_split_child_saved_keeps_a_single_copy() {
		list( $item, $compound, $street, $city ) = $this->create_unique_compound_scenario();
		$this->create_split_unique_compound_value( $item, $compound, $street, $city );

		$this->save_compound_child( $item, $city, 'Goiânia' );
		$this->save_compound_child( $item, $street, 'Rua editada' );

		$this->assertEquals( 'Goiânia', $this->get_compound_child_value( $item, $compound, $city ) );
		$this->assertEquals( 'Rua editada', $this->get_compound_child_value( $item, $compound, $street ) );
		$this->assertEquals( 1, $this->count_compound_rows( $item, $city ) );
		$this->assertEquals( 1, $this->count_compound_rows( $item, $street ) );
		$this->assertEquals( 1, $this->count_compound_rows( $item, $compound ), 'The split rows must be joined back into one' );
	}
}

