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
        $Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

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
        $Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
	        	'description' => 'Filter teste colletion'
	        ),
	        true
        );

	    $metadatum = $this->tainacan_entity_factory->create_entity(
	    	'metadatum',
		    array(
		    	'name'              => 'metadado',
			    'collection_id'     => $collection->get_id(),
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
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
			    'metadatum'           => $metadatum,
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
        $this->assertEquals( $metadatum->get_id(), $test->get_metadatum()->get_id() );
        $objClass = get_class( $filter_list_type );
        $storedObjClass = get_class( $test->get_filter_type_object() );
        $this->assertEquals($objClass , $storedObjClass );

    }

    function test_get_filters_type(){
        $Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

        $all_filter_types = $Tainacan_Filters->fetch_filter_types();
        $this->assertEquals( 8, count( $all_filter_types ) );

        $float_filters = $Tainacan_Filters->fetch_supported_filter_types('float');
        $this->assertTrue( count( $float_filters ) > 0 );
    }

    /**
     * @group filter
     */
    function test_validate_supported_filters(){
        $Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'        => 'Collection filtered',
                'description' => 'Is filtered',
            ),
            true
        );

        $metadatum2 = $this->tainacan_entity_factory->create_entity(
            'metadatum',
            array(
                'name'          => 'Other filtered',
                'description'   => 'Is filtered',
                'metadata_type'    => 'Tainacan\Metadata_Types\Text',
                'collection_id' => $collection->get_id()
            ),
            true
        );

        $autocomplete = $this->tainacan_filter_factory->create_filter('autocomplete');

        $filter = $this->tainacan_entity_factory->create_entity(
            'filter',
	        array(
		        'name'        => 'filtro',
		        'collection'  => $collection,
		        'description' => 'descricao',
		        'metadatum'       => $metadatum2,
		        'filter_type' => $autocomplete
	        ),
            true
        );

        $test = $Tainacan_Filters->fetch( $filter->get_id() );

        $this->assertEquals( 'Tainacan\Filter_Types\Autocomplete', $test->get_filter_type());

        $custom_interval = $this->tainacan_filter_factory->create_filter('custom_interval');

        $filter2 = new \Tainacan\Entities\Filter();
        $filter2->set_name('filter 2');
        $filter2->set_collection($collection);
        $filter2->set_description('description');
        $filter2->set_metadatum($metadatum2);
        $filter2->set_filter_type($custom_interval);

        $this->assertFalse($filter2->validate(), 'filter with a metadatum with unsupported primitive type should not validate');

    }
	
	/**
     * test if parent metadatum are visible for children collection
     */
    function test_hierarchy_filters(){
        $Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

	    $meta_repo = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name'              => 'metadatum default',
		        'collection_id'     => $Tainacan_Metadata->get_default_metadata_attribute(),
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		        'status'            => 'publish'
	        ),
	        true
        );
		
		$this->tainacan_entity_factory->create_entity(
        	'filter',
	        array(
	        	'name'              => 'filter default',
		        'collection_id'     => 'filter_in_repository',
		        'filter_type'  => 'Tainacan\Filter_Types\Selectbox',
				'metadatum'    => $meta_repo->get_id(),
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

        $meta_grand = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name'              => 'metadatum grandfather',
		        'collection_id'     => $collection_grandfather->get_id(),
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		        'status'            => 'publish'
	        ),
	        true
        );
		$this->tainacan_entity_factory->create_entity(
        	'filter',
	        array(
	        	'name'              => 'filter grandfather',
		        'collection_id'     => $collection_grandfather->get_id(),
		        'filter_type'  => 'Tainacan\Filter_Types\Selectbox',
				'metadatum'    => $meta_grand->get_id(),
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

	    $meta_father = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'              => 'metadatum father',
			    'collection_id'     => $collection_father->get_id(),
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			    'status'            => 'publish'
		    ),
		    true
	    );
		$this->tainacan_entity_factory->create_entity(
        	'filter',
	        array(
	        	'name'              => 'filter father',
		        'collection_id'     => $collection_father->get_id(),
		        'filter_type'  => 'Tainacan\Filter_Types\Selectbox',
				'metadatum'    => $meta_father->get_id(),
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

	    $meta_son = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'              => 'metadatum son',
			    'collection_id'     => $collection_son->get_id(),
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			    'status'            => 'publish'
		    ),
		    true
	    );
		$this->tainacan_entity_factory->create_entity(
        	'filter',
	        array(
	        	'name'              => 'filter son',
		        'collection_id'     => $collection_son->get_id(),
		        'filter_type'  => 'Tainacan\Filter_Types\Selectbox',
				'metadatum'    => $meta_son->get_id(),
		        'status'            => 'publish'
	        ),
	        true
        );

        $retrieve_filters =  $Tainacan_Filters->fetch_by_collection( $collection_son, [], 'OBJECT' );
		
		$retrieve_filters_ids =  $Tainacan_Filters->fetch_ids_by_collection( $collection_son, [] );

        // should return 4
        $this->assertEquals( 4, sizeof( $retrieve_filters ) );
		$this->assertEquals( 4, sizeof( $retrieve_filters_ids ) );
    }
	
}