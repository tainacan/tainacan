<?php

namespace Tainacan\Tests;
use Tainacan\Field_Types;
/**
 * Class Field
 *
 * @package Test_Tainacan
 */

/**
 * Field test case.
 * @group fields
 */
class Fields extends TAINACAN_UnitTestCase {

    /**
     * Test insert a regular field with type
     */
    function test_add() {
        global $Tainacan_Fields;

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste'
	        ),
	        true
        );

        $field = $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name' => 'metadado',
		        'description' => 'descricao',
		        'collection' => $collection,
	        	'accept_suggestion' => true,
		        'field_type'  => 'Tainacan\Field_Types\Text',
	        ),
	        true
        );

        $test = $Tainacan_Fields->fetch($field->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_description(), 'descricao');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertTrue((bool) $test->get_accept_suggestion());
    }

    /**
     * Test insert a regular field with type
     */
    function test_add_type(){
        global $Tainacan_Fields;

        $collection = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'teste'
	        ),
	        true
        );

	    $field = $this->tainacan_entity_factory->create_entity(
	        'field',
	        array(
		        'name'              => 'metadado',
		        'description'       => 'descricao',
		        'collection_id'     => $collection->get_id(),
		        'field_type'  => 'Tainacan\Field_Types\Text',
	        ),
	        true
        );

        $test = $Tainacan_Fields->fetch($field->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertEquals('Tainacan\Field_Types\Text', $test->get_field_type());
        $this->assertEquals($test->get_field_type(), 'Tainacan\Field_Types\Text');
    }

    /**
     * test if parent field are visible for children collection
     */
    function test_hierarchy_metadata(){
        global $Tainacan_Fields;

	    $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name'              => 'field default',
		        'collection_id'     => $Tainacan_Fields->get_default_metadata_attribute(),
		        'field_type'  => 'Tainacan\Field_Types\Text',
		        'status'            => 'publish'
	        ),
	        true
        );

        $collection_grandfather = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'collection grandfather'
	        ),
	        true
        );

        $this->tainacan_entity_factory->create_entity(
        	'field',
	        array(
	        	'name'              => 'field grandfather',
		        'collection_id'     => $collection_grandfather->get_id(),
		        'field_type'  => 'Tainacan\Field_Types\Text',
		        'status'            => 'publish'
	        ),
	        true
        );

	    $collection_father = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'   => 'collection father',
			    'parent' => $collection_grandfather->get_id()
		    ),
		    true
	    );

	    $this->tainacan_entity_factory->create_entity(
		    'field',
		    array(
			    'name'              => 'field father',
			    'collection_id'     => $collection_father->get_id(),
			    'field_type'  => 'Tainacan\Field_Types\Text',
			    'status'            => 'publish'
		    ),
		    true
	    );

	    $collection_son = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'   => 'collection son',
			    'parent' => $collection_father->get_id()
		    ),
		    true
	    );

        $this->assertEquals( $collection_grandfather->get_id(), $collection_father->get_parent() );
        $this->assertEquals( $collection_father->get_id(), $collection_son->get_parent() );

	    $this->tainacan_entity_factory->create_entity(
		    'field',
		    array(
			    'name'              => 'field son',
			    'collection_id'     => $collection_son->get_id(),
			    'field_type'  => 'Tainacan\Field_Types\Text',
			    'status'            => 'publish'
		    ),
		    true
	    );

        $retrieve_metadata =  $Tainacan_Fields->fetch_by_collection( $collection_son, [], 'OBJECT' );

        // should return 6
        $this->assertEquals( 6, sizeof( $retrieve_metadata ) );
    }

    /**
     * test remove core fields
     */
    function test_core_fields(){
        global $Tainacan_Fields;

        $collection_grandfather = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name' => 'collection field'
            ),
            true
        );

        $core_fields = $Tainacan_Fields->get_core_fields( $collection_grandfather );

        if( $core_fields ){
            foreach( $core_fields as $core_field ){
                $this->assertFalse(wp_trash_post( $core_field->get_id() ) );
            }
        }
    }

    /**
     * test if the defaults types are registered
     */
    function test_metadata_field_type(){
        global $Tainacan_Fields;
        $this->assertEquals( 7, sizeof( $Tainacan_Fields->fetch_field_types() ) );
    }


    /**
     *
     */
    function test_ordenation_fields(){
        global $Tainacan_Collections, $Tainacan_Fields;

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name' => 'teste'
            ),
            true
        );

        $field1 = $this->tainacan_entity_factory->create_entity(
            'field',
            array(
                'name' => 'field1',
                'description' => 'descricao',
                'collection' => $collection,
                'field_type'  => 'Tainacan\Field_Types\Text',
                'status' => 'publish'
            ),
            true
        );

        $field2 = $this->tainacan_entity_factory->create_entity(
            'field',
            array(
                'name' => 'field2',
                'description' => 'field2',
                'collection' => $collection,
                'field_type'  => 'Tainacan\Field_Types\Text',
                'status' => 'publish'
            ),
            true
        );


        $field3 = $this->tainacan_entity_factory->create_entity(
            'field',
            array(
                'name' => 'field3',
                'description' => 'field3',
                'collection' => $collection,
                'field_type'  => 'Tainacan\Field_Types\Text',
                'status' => 'publish'
            ),
            true
        );

        $collection->set_fields_order(
            [
                array( 'id' => $field3->get_id(), 'enable' => false ),
                array( 'id' => $field2->get_id(), 'enable' => true ),
                array( 'id' => $field1->get_id(), 'enable' => true )
            ]);

        $update_collection = $Tainacan_Collections->update( $collection );
        
        $fields_ordinate = $Tainacan_Fields->fetch_by_collection( $update_collection, [], 'OBJECT' );
        $this->assertEquals( 'field2', $fields_ordinate[0]->get_name() );

        $fields_ordinate_enabled = $Tainacan_Fields->fetch_by_collection( $update_collection, [ 'include_disabled' => true ], 'OBJECT' );
        $this->assertEquals( 'field3', $fields_ordinate_enabled[0]->get_name() );
		
		$this->assertFalse($fields_ordinate_enabled[0]->get_enabled_for_collection());
		$this->assertTrue($fields_ordinate_enabled[1]->get_enabled_for_collection());
		$this->assertTrue($fields_ordinate_enabled[2]->get_enabled_for_collection());
    }
    
    function test_unique_slugs() {
		$x = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'status'        => 'publish',
                'field_type'  => 'Tainacan\Field_Types\Text',
			),
			true
		);
        
        $y = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'status'        => 'publish',
                'field_type'  => 'Tainacan\Field_Types\Text',
			),
			true
		);
        
        $this->assertNotEquals($x->get_slug(), $y->get_slug());
        
        // Create as draft and publish later
        $x = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'field_type'  => 'Tainacan\Field_Types\Text',
			),
			true
		);
        
        $y = $this->tainacan_entity_factory->create_entity(
			'field',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'field_type'  => 'Tainacan\Field_Types\Text',
			),
			true
		);
        
        $this->assertEquals($x->get_slug(), $y->get_slug());
        
        global $Tainacan_Fields;
        $x->set_status('publish');
        $x->validate();
        $x = $Tainacan_Fields->insert($x);
        $y->set_status('private'); // or publish shoud behave the same
        $y->validate();
        $y = $Tainacan_Fields->insert($y);
        
        $this->assertNotEquals($x->get_slug(), $y->get_slug());
        
    }

    function test_validation_of_field_types() {
        global $Tainacan_Fields;

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste'
	        ),
	        true
        );

        
        $validField = new \Tainacan\Entities\Field();
        $validField->set_name('test');
        $validField->set_description('test');
        $validField->set_collection($collection);
        $validField->set_field_type('Tainacan\Field_Types\Relationship');
        $validField->set_field_type_options(['collection_id' => 12]);
        
        $this->assertTrue($validField->validate());
        
        $invalidField = new \Tainacan\Entities\Field();
        $invalidField->set_name('test');
        $invalidField->set_description('test');
        $invalidField->set_collection($collection);
        $invalidField->set_status('publish');
        $invalidField->set_field_type('Tainacan\Field_Types\Relationship');
        $invalidField->set_field_type_options(['collection_id' => 'string']);
        
        $this->assertFalse($invalidField->validate());

    }
}

/**
 * Class TainacanFieldType
 */
class RandomType extends Field_Types\Field_Type {

    function __construct(){
        parent::__construct();
    }

    /**
     * @param $field
     * @return string
     */

    public function render( $field ){}
}