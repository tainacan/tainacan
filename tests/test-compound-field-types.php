<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * @group compound
 */
class CompoundFieldTypes extends TAINACAN_UnitTestCase {


    function test_compound_field_types() {

        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Compound',
				'status'	 => 'publish',
	        ),
	        true
        );

        $field_child1 = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta2',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $field->get_id(),
	        ),
	        true
        );

		$field_child2 = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta3',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $field->get_id(),
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

		$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child1);
		$item_metadata1->set_value('Red');

		$item_metadata1->validate();

		$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child2, null, $item_metadata1->get_parent_meta_id());
		$item_metadata->set_value('Blue');

		$item_metadata->validate();

		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		$compoundItem = new \Tainacan\Entities\Item_Metadata_Entity($i, $field);

		global $wpdb;

		$compoundValue = $compoundItem->get_value();

		$this->assertTrue( is_array($compoundValue), 'value of a compound should return array' );
		$this->assertEquals( 2, sizeof($compoundValue), 'value should have 2 item metadata' );

		$this->assertTrue( isset($compoundValue[$field_child1->get_id()]), 'First element of value must be set' );
		$this->assertTrue( isset($compoundValue[$field_child2->get_id()]), 'Second element of value must be set' );

		$this->assertTrue( $compoundValue[$field_child1->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'First element of value should be an item metadata entity' );
		$this->assertTrue( $compoundValue[$field_child2->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'Second element of value should be an item metadata entity' );


		$this->assertEquals( 'Red', $compoundValue[$field_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Blue', $compoundValue[$field_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );

    }

	function test_multiple_compound_field_types() {

        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'yes'
	        ),
	        true
        );

        $field_child1 = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta2',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $field->get_id(),
	        ),
	        true
        );

		$field_child2 = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta3',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Text',
				'status'	 => 'publish',
				'parent' 	 => $field->get_id(),
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

		global $wpdb;




		// First Instance
		$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child1);
		$item_metadata1->set_value('Red');

		$item_metadata1->validate();

		$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);


		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child2, null, $item_metadata1->get_parent_meta_id());
		$item_metadata->set_value('Blue');

		$item_metadata->validate();

		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);


		// Second Instance
		$item_metadata3 = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child1);
		$item_metadata3->set_value('Green');

		$item_metadata3->validate();

		$item_metadata3 = $Tainacan_Item_Metadata->insert($item_metadata3);


		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child2, null, $item_metadata3->get_parent_meta_id());
		$item_metadata->set_value('Yellow');

		$item_metadata->validate();

		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);



		$compoundItem = new \Tainacan\Entities\Item_Metadata_Entity($i, $field);

		//var_dump($wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = {$i->get_id()}"));
		//var_dump($wpdb->get_results("SELECT * FROM $wpdb->posts WHERE parent = {$field->get_id()}"));

		$compoundValue = $compoundItem->get_value();

		$this->assertTrue( is_array($compoundValue), 'value of a compound should return array' );
		$this->assertEquals( 2, sizeof($compoundValue), 'value should have 2 values' );

		$this->assertTrue( is_array($compoundValue[0]), 'value of a compound should return array' );
		$this->assertTrue( is_array($compoundValue[1]), 'value of a compound should return array' );

		$this->assertTrue( isset($compoundValue[0][$field_child1->get_id()]), 'First element of value must be set' );
		$this->assertTrue( isset($compoundValue[1][$field_child2->get_id()]), 'Second element of value must be set' );

		$this->assertTrue( $compoundValue[0][$field_child1->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'First element of value should be an item metadata entity' );
		$this->assertTrue( $compoundValue[1][$field_child2->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity , 'Second element of value should be an item metadata entity' );


		$this->assertEquals( 'Red', $compoundValue[0][$field_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Blue', $compoundValue[0][$field_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );

		$this->assertEquals( 'Green', $compoundValue[1][$field_child1->get_id()]->get_value() , 'First element of value should have "Red" value' );
		$this->assertEquals( 'Yellow', $compoundValue[1][$field_child2->get_id()]->get_value() , 'Second element of value should have "Blue" value' );

    }

	function test_validations_category_in_multiple() {

		$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'yes'
	        ),
	        true
        );

		$newField = new \Tainacan\Entities\Field();
		$newField->set_name('test_multiple');
		$newField->set_field_type('Tainacan\Field_Types\Category');
		$newField->set_parent($field->get_id());

		$this->assertFalse($newField->validate(), 'You cant add a category field inside a multiple compound field');

		$newField->set_field_type('Tainacan\Field_Types\Text');
		$this->assertTrue($newField->validate());


	}

	function test_validations_category_in_multiple_2() {

		$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'no'
	        ),
	        true
        );

		$newField = new \Tainacan\Entities\Field();
		$newField->set_name('test_multiple');
		$newField->set_field_type('Tainacan\Field_Types\Category');
		$newField->set_parent($field->get_id());

		$this->assertTrue($newField->validate(), 'You can add a category field inside a not multiple compound field');
		$newField = $Tainacan_Fields->insert($newField);

		$field->set_multiple('yes');

		$this->assertFalse($field->validate(), 'You cant turn a compound field into multiple when there is a category field inside it');


	}

	function test_validations_multiple_fields() {

		$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);

        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'field_type' => 'Tainacan\Field_Types\Compound',
				'status'	 => 'publish',
				'multiple'   => 'no'
	        ),
	        true
        );

		$newField = new \Tainacan\Entities\Field();
		$newField->set_name('test_multiple');
		$newField->set_multiple('yes');
		$newField->set_field_type('Tainacan\Field_Types\Text');
		$newField->set_parent($field->get_id());

		$this->assertFalse($newField->validate(), 'You cant add a multiple field inside a compound field');

		$newField->set_multiple('no');

		$this->assertTrue($newField->validate());


	}

	function test_compound_field_types_category() {

			$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
			$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
			$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

			$collection = $this->tainacan_entity_factory->create_entity(
				'collection',
				array(
					'name'   => 'test',
				),
				true
			);

			$tax = $this->tainacan_entity_factory->create_entity(
				'taxonomy',
				array(
					'name'   => 'tax_test',
					'collections' => [$collection],
				),
				true
			);

			$term = $this->tainacan_entity_factory->create_entity(
			    'term',
			    array(
				    'taxonomy' => $tax->get_db_identifier(),
				    'name'     => 'Red',
			    ),
			    true
		    );

			$field = $this->tainacan_entity_factory->create_entity(
				'field',
				array(
					'name' => 'meta',
					'description' => 'description',
					'collection' => $collection,
					'field_type' => 'Tainacan\Field_Types\Compound',
			'status'	 => 'publish',
				),
				true
			);

			$field_child1 = $this->tainacan_entity_factory->create_entity(
				'field',
				array(
					'name' => 'meta2',
					'description' => 'description',
					'collection' => $collection,
					'field_type' => 'Tainacan\Field_Types\Text',
			'status'	 => 'publish',
			'parent' 	 => $field->get_id(),
				),
				true
			);

			$field_child2 = $this->tainacan_entity_factory->create_entity(
				'field',
				array(
					'name' => 'meta',
					'description' => 'description',
					'collection' => $collection,
					'field_type' => 'Tainacan\Field_Types\Category',
					'status'	 => 'publish',
					'field_type_options' => [
						'taxonomy_id' => $tax->get_id(),
						'allow_new_terms' => true
					]
					),
				true
			);

		 $field->set_field_type_options([
			 'parent' => $field->get_id(),
			 'before_children' => [],
			 'children' =>  [ $field_child1->get_id(),$field_child2->get_id()]
		 ]);
		 $field->validate();
		 $Tainacan_Fields->update( $field );

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

	$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child1);
	$item_metadata->set_value('Red');

	$item_metadata->validate();

	$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

	$item_metadata1 = new \Tainacan\Entities\Item_Metadata_Entity($i, $field_child2, null, $item_metadata->get_parent_meta_id());
	$item_metadata1->set_value('Blue');

	$item_metadata1->validate();

	$item_metadata1 = $Tainacan_Item_Metadata->insert($item_metadata1);

	$compoundItem = new \Tainacan\Entities\Item_Metadata_Entity($i, $field);

	global $wpdb;

	$compoundValue = $compoundItem->get_value();

	$compoundasArray = $compoundItem->__toArray();
  $compoundItem->get_value_as_string();

	$this->assertTrue( is_array($compoundasArray['value']), 'value of a compound should return array' );

	$this->assertEquals( 2, sizeof($compoundasArray['value']), 'value should have 2 item metadata' );

	//texto
	$this->assertEquals( 'Red', $compoundasArray['value'][$field_child1->get_id()]['value'] , 'First element of value should have "Red" value' );
	//category
	$this->assertEquals( 'Blue', $compoundasArray['value'][$field_child2->get_id()]['value']['name'] , 'Second element of value should have "Blue" value' );

	}

}
