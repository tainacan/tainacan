<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Filters extends \WP_UnitTestCase {


    function teste_add(){
        global $Tainacan_Collections, $Tainacan_Filters;

        $collection = new \Tainacan\Entities\Collection();
        $filter = new \Tainacan\Entities\Filter();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $filter->set_name('filtro');
        $filter->set_collection( $collection );

        //inserindo o metadado
        $filter = $Tainacan_Filters->insert( $filter );

        $test = $Tainacan_Filters->get_filter_by_id( $filter->get_id() );

        $this->assertEquals($test->get_name(), 'filtro');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
    }

    function test_add_with_metadata_and_type(){
        global $Tainacan_Collections, $Tainacan_Filters,$Tainacan_Metadatas;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $filter = new \Tainacan\Entities\Filter();
        $type = new \Tainacan\Field_Types\Text_Field_Type();
        $filter_list_type = new \Tainacan\Filter_Types\List_Filter_Type();
        $filter_range_type = new \Tainacan\Filter_Types\Range_Filter_Type();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_collection_id( $collection->get_id() );
        $metadata->set_field_type_object( $type );


        //inserindo o metadado
        $metadata = $Tainacan_Metadatas->insert($metadata);

        //inserindo o filtro
        $filter->set_name('filtro');
        $filter->set_collection( $collection );
        $filter->set_metadata( $metadata );

        //nao devera permitir um filtro Range para o tipo string
        $this->assertTrue( $filter->set_filter_type_object( $filter_range_type ) === null );

        $filter->set_filter_type_object( $filter_list_type );

        $filter = $Tainacan_Filters->insert( $filter );

        $test = $Tainacan_Filters->get_filter_by_id( $filter->get_id() );

        $this->assertEquals( $test->get_name(), 'filtro');
        $this->assertEquals( $test->get_collection_id(), $collection->get_id() );
        $this->assertEquals( $test->get_metadata()->get_id(),  $metadata->get_id() );
        $this->assertEquals( get_class( $test->get_filter_type_object() ),  get_class( $filter_list_type ) );

    }

    function test_get_filters_type(){
        global $Tainacan_Filters;

        $all_filter_types = $Tainacan_Filters->get_all_filters_type();
        $this->assertEquals( count( $all_filter_types ), 2 );

        $float_filters = $Tainacan_Filters->get_filters_by_metadata_type('float');
        $this->assertTrue( count( $float_filters ) > 0 );
    }
}