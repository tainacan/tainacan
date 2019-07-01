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


	    $filter = $this->tainacan_entity_factory->create_entity(
	    	'filter',
		    array(
		    	'name'               => 'filtro',
			    'collection'         => $collection,
		    	'description' => 'descricao',
			    'metadatum'           => $metadatum,
			    'filter_type' => 'Tainacan\Filter_Types\Selectbox'
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
        $storedObjClass = get_class( $test->get_filter_type_object() );
        $this->assertEquals('Tainacan\Filter_Types\Selectbox' , $storedObjClass );

    }

    function test_get_filters_type(){
        $Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

        $all_filter_types = $Tainacan_Filters->fetch_filter_types();
        $this->assertEquals( 9, count( $all_filter_types ) );

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

        $filter = $this->tainacan_entity_factory->create_entity(
            'filter',
	        array(
		        'name'        => 'filtro',
		        'collection'  => $collection,
		        'description' => 'descricao',
		        'metadatum'       => $metadatum2,
		        'filter_type' => 'Tainacan\Filter_Types\Autocomplete'
	        ),
            true
        );

        $test = $Tainacan_Filters->fetch( $filter->get_id() );

        $this->assertEquals( 'Tainacan\Filter_Types\Autocomplete', $test->get_filter_type());

        $filter2 = new \Tainacan\Entities\Filter();
        $filter2->set_name('filter 2');
        $filter2->set_collection($collection);
        $filter2->set_description('description');
        $filter2->set_metadatum($metadatum2);
        $filter2->set_filter_type('Tainacan\Filter_Types\Custom_Interval');

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
				'metadatum_id'    => $meta_repo->get_id(),
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
				'metadatum_id'    => $meta_grand->get_id(),
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
				'metadatum_id'    => $meta_father->get_id(),
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
				'metadatum_id'    => $meta_son->get_id(),
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
    
    function test_metadatum_getter_setter(){
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

        $filter2 = new \Tainacan\Entities\Filter();
        $filter2->set_name('filter 2');
        $filter2->set_collection($collection);
        $filter2->set_description('description');
        $filter2->set_metadatum($metadatum2);
        $filter2->set_filter_type('Tainacan\Filter_Types\Selectbox');
        
        $filter2->validate();
        
        $filter = $Tainacan_Filters->insert($filter2);
        
        $this->assertEquals($metadatum2->get_id(), $filter->get_metadatum_id());
        $this->assertEquals($metadatum2->get_name(), $filter->get_metadatum()->get_name());
        
        
        $filter3 = new \Tainacan\Entities\Filter();
        $filter3->set_name('filter 3');
        $filter3->set_collection($collection);
        $filter3->set_description('description');
        $filter3->set_metadatum_id($metadatum2->get_id());
        $filter3->set_filter_type('Tainacan\Filter_Types\Selectbox');
        
        $filter3->validate();
        
        $filter = $Tainacan_Filters->insert($filter3);
        
        $this->assertEquals($metadatum2->get_id(), $filter->get_metadatum_id());
        $this->assertEquals($metadatum2->get_name(), $filter->get_metadatum()->get_name());
        
        $metadatum3 = $this->tainacan_entity_factory->create_entity(
            'metadatum',
            array(
                'name'          => 'Other filtered',
                'description'   => 'Is filtered',
                'metadata_type'    => 'Tainacan\Metadata_Types\Text',
                'collection_id' => $collection->get_id()
            ),
            true
        );
        
        $filter4 = new \Tainacan\Entities\Filter();
        $filter4->set_name('filter 4');
        
        $filter4->set_metadatum_id($metadatum3->get_id());
        $this->assertEquals($metadatum3->get_id(), $filter4->get_metadatum_id());
        $this->assertEquals($metadatum3->get_name(), $filter4->get_metadatum()->get_name());
        
        $filter4->set_metadatum($metadatum2);
        $this->assertEquals($metadatum2->get_id(), $filter4->get_metadatum_id());
        $this->assertEquals($metadatum2->get_name(), $filter4->get_metadatum()->get_name());
        
        
    }
	
	
	function test_delete_filter_when_metadata_deleted() {
		
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
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
		
		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'metadatum'       => $metadatum2,
				'filter_type' => 'Tainacan\Filter_Types\Autocomplete'
			),
			true
		);
		
		$Tainacan_Metadata->delete($metadatum2);
		
		$x = $Tainacan_Filters->fetch( $filter->get_id() );
		
		$this->assertEmpty($x);
		
	}
	
	function test_trash_filter_when_metadata_trashed() {
		
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
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
		
		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'filtro',
				'collection'  => $collection,
				'description' => 'descricao',
				'metadatum'       => $metadatum2,
				'filter_type' => 'Tainacan\Filter_Types\Autocomplete'
			),
			true
		);
		
		$Tainacan_Metadata->trash($metadatum2);
		
		$x = $Tainacan_Filters->fetch( $filter->get_id() );
		
		$this->assertEquals('trash', $x->get_status());
		
	}
	
}