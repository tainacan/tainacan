<?php

namespace Tainacan\Tests;

/**
 * Class Metadata
 *
 * @package Test_Tainacan
 */

/**
 * Metadata test case.
 */
class Metadata extends \WP_UnitTestCase {

    /**
     * Test insert a regular metadata with type
     */
    function test_add() {
        global $Tainacan_Collections, $Tainacan_Metadatas;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        $collection->set_name('teste');
        $collection->validate();
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );
        $metadata->set_field_type_object( $type );

        //inserindo o metadado
        $metadata->validate();
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_description(), 'descricao');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());

    }

    /**
     * Test insert a regular metadata with type
     */
    function test_add_type(){
        global $Tainacan_Collections, $Tainacan_Metadatas;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        $collection->set_name('teste');
        $collection->validate();
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_collection_id( $collection->get_id() );
        $metadata->set_field_type_object( $type );


        //inserindo o metadado
        $metadata->validate();
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->fetch($metadata->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertEquals('Tainacan\Field_Types\Text', $test->get_field_type());
        $this->assertEquals($test->get_field_type_object(), $type);
    }


    function test_hierarchy_metadata(){
        global $Tainacan_Collections, $Tainacan_Metadatas;

        $metadata_default = new \Tainacan\Entities\Metadata();
        $collection_grandfather = new \Tainacan\Entities\Collection();
        $metadata_grandfather = new \Tainacan\Entities\Metadata();
        $collection_father = new \Tainacan\Entities\Collection();
        $metadata_father = new \Tainacan\Entities\Metadata();
        $collection_son = new \Tainacan\Entities\Collection();
        $metadata_son = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        //creating metadata default
        $metadata_default->set_name('metadata default');
        $metadata_default->set_collection_id( $Tainacan_Metadatas->get_default_metadata_attribute() );
        $metadata_default->set_field_type_object( $type );
        $metadata_default->set_status('publish');
        $metadata_default->validate();
        $Tainacan_Metadatas->insert($metadata_default);

        //creating collection grandfather
        $collection_grandfather->set_name('collection grandfather');
        $collection_grandfather->validate();
        $collection_grandfather = $Tainacan_Collections->insert($collection_grandfather);

        //creating metadata grandfather
        $metadata_grandfather->set_name('metadata grandfather');
        $metadata_grandfather->set_collection_id( $collection_grandfather->get_id() );
        $metadata_grandfather->set_field_type_object( $type );
        $metadata_grandfather->set_status('publish');
        $metadata_grandfather->validate();
        $Tainacan_Metadatas->insert($metadata_grandfather);

        //creating collection father
        $collection_father->set_name('collection father');
        $collection_father->set_parent( $collection_grandfather->get_id() );
        $collection_father->validate();
        $collection_father = $Tainacan_Collections->insert( $collection_father );

        $this->assertEquals( $collection_grandfather->get_id(), $collection_father->get_parent() );

        //creating metadata father
        $metadata_father->set_name('metadata father');
        $metadata_father->set_collection_id( $collection_father->get_id() );
        $metadata_father->set_field_type_object( $type );
        $metadata_father->set_status('publish');
        $metadata_father->validate();
        $Tainacan_Metadatas->insert($metadata_father);

        //creating collection son
        $collection_son->set_name('collection son');
        $collection_son->set_parent( $collection_father->get_id() );
        $collection_son->validate();
        $collection_son = $Tainacan_Collections->insert($collection_son);

        $this->assertEquals( $collection_father->get_id(), $collection_son->get_parent() );

        //creating metadata son
        $metadata_son->set_name('metadata son');
        $metadata_son->set_collection_id( $collection_son->get_id() );
        $metadata_son->set_field_type_object( $type );
        $metadata_son->set_status('publish');
        $metadata_son->validate();
        $Tainacan_Metadatas->insert($metadata_son);

        $retrieve_metadata =  $Tainacan_Metadatas->fetch_by_collection( $collection_son, [], 'OBJECT' );

        $this->assertEquals( 4, sizeof( $retrieve_metadata ) );

    }
}