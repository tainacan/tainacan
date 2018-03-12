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
	        	'name' => 'teste',
	        	'description' => 'Filter teste colletion'
	        ),
	        true
        );

        $filter = $this->tainacan_entity_factory->create_entity(
        	'filter',
	        array(
	        	'name'       => 'filtro',
		        'collection' => $collection,
	        	'description' => 'Teste Filtro'
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
	        	'name' => 'teste',
	        	'description' => 'Filter teste colletion'
	        ),
	        true
        );

	    $field = $this->tainacan_entity_factory->create_entity(
	    	'field',
		    array(
		    	'name'              => 'metadado',
			    'collection_id'     => $collection->get_id(),
			    'field_type'  => 'Tainacan\Field_Types\Text',
		    	'description' => 'descricao',
		    ),
		    true
	    );

	    $filter_list_type = $this->tainacan_filter_factory->create_filter('selectbox');

	    $filter = $this->tainacan_entity_factory->create_entity(
	    	'filter',
		    array(
		    	'name'               => 'filtro',
			    'collection'         => $collection,
		    	'description' => 'descricao',
			    'field'           => $field,
			    'filter_type' => $filter_list_type
		    ),
		    true
	    );

        $filter_range_type = $this->tainacan_filter_factory->create_filter('custom_interval');

        //nao devera permitir um filtro Range para o tipo string
         $this->assertTrue( $filter->set_filter_type( $filter_range_type ) === null );

        $test = $Tainacan_Filters->fetch( $filter->get_id() );

        $this->assertEquals( 'filtro', $test->get_name() );
        $this->assertEquals( $collection->get_id(), $test->get_collection_id() );
        $this->assertEquals( $field->get_id(), $test->get_field()->get_id() );
        $objClass = get_class( $filter_list_type );
        $storedObjClass = get_class( $test->get_filter_type_object() );
        $this->assertEquals($objClass , $storedObjClass );

    }

    function test_get_filters_type(){
        global $Tainacan_Filters;

        $all_filter_types = $Tainacan_Filters->fetch_filter_types();
        $this->assertEquals( 8, count( $all_filter_types ) );

        $float_filters = $Tainacan_Filters->fetch_supported_filter_types('float');
        $this->assertTrue( count( $float_filters ) > 0 );
    }

    /**
     * @group filter
     */
    function test_validate_supported_filters(){
        global $Tainacan_Filters;

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'        => 'Collection filtered',
                'description' => 'Is filtered',
            ),
            true
        );

        $field2 = $this->tainacan_entity_factory->create_entity(
            'field',
            array(
                'name'          => 'Other filtered',
                'description'   => 'Is filtered',
                'field_type'    => 'Tainacan\Field_Types\Text',
                'collection_id' => $collection->get_id()
            ),
            true
        );

        $autocomplete = $this->tainacan_filter_factory->create_filter('autocomplete');

        $filter = $this->tainacan_entity_factory->create_entity(
            'filter',
            array(
                'name'               => 'filtro',
                'collection'         => $collection,
                'description' => 'descricao',
                'field'           => $field2,
                'filter_type' => $autocomplete
            ),
            true
        );

        $test = $Tainacan_Filters->fetch( $filter->get_id() );

        $this->assertEquals( 'Tainacan\Filter_Types\Autocomplete', $test->get_filter_type());

        $custom_interval = $this->tainacan_filter_factory->create_filter('custom_interval');

        $this->expectException('ErrorException');
        $filter2 = $this->tainacan_entity_factory->create_entity(
            'filter',
            array(
                'name'               => 'filtro 2',
                'collection'         => $collection,
                'description' => 'descricao',
                'field'           => $field2,
                'filter_type' => $custom_interval
            ),
            true
        );

    }
}