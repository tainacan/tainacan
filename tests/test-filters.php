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
class Filters extends TAINACAN_UnitTestCase {


    function teste_add(){
        global $Tainacan_Filters;

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste'
	        ),
	        true
        );

        $filter = $this->tainacan_entity_factory->create_entity(
        	'filter',
	        array(
	        	'name'       => 'filtro',
		        'collection' => $collection
	        ),
	        true
        );

        $test = $Tainacan_Filters->fetch($filter->get_id());

        $this->assertEquals('filtro', $test->get_name());
        $this->assertEquals($collection->get_id(), $test->get_collection_id());
    }

    function test_add_with_metadata_and_type(){
        global $Tainacan_Filters;

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
			    'collection_id'     => $collection->get_id(),
			    'field_type_object' => $type
		    ),
		    true
	    );

	    $filter_list_type = $this->tainacan_filter_factory->create_filter('list_filter');

	    $filter = $this->tainacan_entity_factory->create_entity(
	    	'filter',
		    array(
		    	'name'               => 'filtro',
			    'collection'         => $collection,
			    'metadata'           => $metadata,
			    'filter_type_object' => $filter_list_type
		    ),
		    true
	    );

        $filter_range_type = $this->tainacan_filter_factory->create_filter('range');

        //nao devera permitir um filtro Range para o tipo string
        $this->assertTrue( $filter->set_filter_type_object( $filter_range_type ) === null );

        $test = $Tainacan_Filters->fetch( $filter->get_id() );

        $this->assertEquals( 'filtro', $test->get_name() );
        $this->assertEquals( $collection->get_id(), $test->get_collection_id() );
        $this->assertEquals( $metadata->get_id(), $test->get_metadata()->get_id() );
        $objClass = get_class( $filter_list_type );
        $storedObjClass = get_class( $test->get_filter_type_object() );
        $this->assertEquals($objClass , $storedObjClass );

    }

    function test_get_filters_type(){
        global $Tainacan_Filters;

        $all_filter_types = $Tainacan_Filters->fetch_filter_types();
        $this->assertEquals( 2, count( $all_filter_types ) );

        $float_filters = $Tainacan_Filters->fetch_supported_filter_types('float');
        $this->assertTrue( count( $float_filters ) > 0 );
    }
}