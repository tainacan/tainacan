<?php

namespace Tainacan\Tests;
use Tainacan\Field_Types;
/**
 * Class Metadata
 *
 * @package Test_Tainacan
 */

/**
 * Metadata test case.
 */
class Metadata extends TAINACAN_UnitTestCase {

    /**
     * Test insert a regular metadata with type
     */
    function test_add() {
        global $Tainacan_Metadatas;

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste'
	        ),
	        true
        );

        $type = $this->tainacan_field_factory->create_field('text');

        $metadata = $this->tainacan_entity_factory->create_entity(
        	'metadata',
	        array(
	        	'name' => 'metadado',
		        'description' => 'descricao',
		        'collection' => $collection,
		        'field_type_object' => $type
	        ),
	        true
        );

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_description(), 'descricao');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
    }

    /**
     * Test insert a regular metadata with type
     */
    function test_add_type(){
        global $Tainacan_Metadatas;

        $collection = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'teste'
	        ),
	        true
        );

	    $type = $this->tainacan_field_factory->create_field('text');

	    $metadata = $this->tainacan_entity_factory->create_entity(
	        'metadata',
	        array(
		        'name'              => 'metadado',
		        'description'       => 'descricao',
		        'collection_id'     => $collection->get_id(),
		        'field_type_object' => $type
	        ),
	        true
        );

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertEquals('Tainacan\Field_Types\Text', $test->get_field_type());
        $this->assertEquals($test->get_field_type_object(), $type);
    }

    /**
     * test if parent metadata are visible for children collection
     */
    function test_hierarchy_metadata(){
        global $Tainacan_Metadatas;

	    $type = $this->tainacan_field_factory->create_field('text');

	    $this->tainacan_entity_factory->create_entity(
        	'metadata',
	        array(
	        	'name'              => 'metadata default',
		        'collection_id'     => $Tainacan_Metadatas->get_default_metadata_attribute(),
		        'field_type_object' => $type,
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
        	'metadata',
	        array(
	        	'name'              => 'metadata grandfather',
		        'collection_id'     => $collection_grandfather->get_id(),
		        'field_type_object' => $type,
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
		    'metadata',
		    array(
			    'name'              => 'metadata father',
			    'collection_id'     => $collection_father->get_id(),
			    'field_type_object' => $type,
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
		    'metadata',
		    array(
			    'name'              => 'metadata son',
			    'collection_id'     => $collection_son->get_id(),
			    'field_type_object' => $type,
			    'status'            => 'publish'
		    ),
		    true
	    );

        $retrieve_metadata =  $Tainacan_Metadatas->fetch_by_collection( $collection_son, [], 'OBJECT' );
        $this->assertEquals( 4, sizeof( $retrieve_metadata ) );
    }

    /**
     * test if the defaults types are registered
     */
    function test_metadata_field_type(){
        global $Tainacan_Metadatas;
        $this->assertEquals( 8, sizeof( $Tainacan_Metadatas->fetch_field_types() ) );
    }

    /**
     * test if the defaults types are registered
     */
    function test_metadata_field_type_insert(){
        global $Tainacan_Metadatas;
        $class = new RandomType;
        $this->assertEquals( 9, sizeof( $Tainacan_Metadatas->fetch_field_types() ) );
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
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){}
}