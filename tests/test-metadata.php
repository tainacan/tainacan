<?php

namespace Tainacan\Tests;
use Tainacan\Metadata_Types;
/**
 * Class Metadatum
 *
 * @package Test_Tainacan
 */

/**
 * Metadatum test case.
 * @group metadata
 */
class Metadata extends TAINACAN_UnitTestCase {

    /**
     * Test insert a regular metadatum with type
     */
    function test_add() {
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste'
	        ),
	        true
        );

        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'metadado',
		        'description' => 'descricao',
		        'collection' => $collection,
	        	'accept_suggestion' => true,
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
	        ),
	        true
        );

        $test = $Tainacan_Metadata->fetch($metadatum->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_description(), 'descricao');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertTrue((bool) $test->get_accept_suggestion());
    }

    /**
     * Test insert a regular metadatum with type
     */
    function test_add_type(){
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
	        'collection',
	        array(
		        'name' => 'teste'
	        ),
	        true
        );

	    $metadatum = $this->tainacan_entity_factory->create_entity(
	        'metadatum',
	        array(
		        'name'              => 'metadado',
		        'description'       => 'descricao',
		        'collection_id'     => $collection->get_id(),
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
	        ),
	        true
        );

        $test = $Tainacan_Metadata->fetch($metadatum->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertEquals('Tainacan\Metadata_Types\Text', $test->get_metadata_type());
        $this->assertEquals($test->get_metadata_type(), 'Tainacan\Metadata_Types\Text');
    }

    /**
     * test if parent metadatum are visible for children collection
     */
    function test_hierarchy_metadata(){
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

	    $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name'              => 'metadatum default',
		        'collection_id'     => $Tainacan_Metadata->get_default_metadata_attribute(),
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
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
        	'metadatum',
	        array(
	        	'name'              => 'metadatum grandfather',
		        'collection_id'     => $collection_grandfather->get_id(),
		        'metadata_type'  => 'Tainacan\Metadata_Types\Text',
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
		    'metadatum',
		    array(
			    'name'              => 'metadatum father',
			    'collection_id'     => $collection_father->get_id(),
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
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
		    'metadatum',
		    array(
			    'name'              => 'metadatum son',
			    'collection_id'     => $collection_son->get_id(),
			    'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			    'status'            => 'publish'
		    ),
		    true
	    );

        $retrieve_metadata =  $Tainacan_Metadata->fetch_by_collection( $collection_son, [], 'OBJECT' );
		
		$retrieve_metadata_ids =  $Tainacan_Metadata->fetch_ids_by_collection( $collection_son, [] );

        // should return 6
        $this->assertEquals( 6, sizeof( $retrieve_metadata ) );
		$this->assertEquals( 6, sizeof( $retrieve_metadata_ids ) );
    }

    /**
     * test remove core metadata
     */
    function test_core_metadata(){
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection_grandfather = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name' => 'collection metadatum'
            ),
            true
        );

        $core_metadata = $Tainacan_Metadata->get_core_metadata( $collection_grandfather );

        if( $core_metadata ){
            foreach( $core_metadata as $core_metadatum ){
                $this->assertFalse(wp_trash_post( $core_metadatum->get_id() ) );
            }
        }
    }

    /**
     * test if the defaults types are registered
     */
    function test_metadata_metadata_type(){
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $this->assertEquals( 7, sizeof( $Tainacan_Metadata->fetch_metadata_types() ) );
    }


    /**
     *
     */
    function test_ordenation_metadata(){
        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name' => 'teste'
            ),
            true
        );

        $metadatum1 = $this->tainacan_entity_factory->create_entity(
            'metadatum',
            array(
                'name' => 'metadatum1',
                'description' => 'descricao',
                'collection' => $collection,
                'metadata_type'  => 'Tainacan\Metadata_Types\Text',
                'status' => 'publish'
            ),
            true
        );

        $metadatum2 = $this->tainacan_entity_factory->create_entity(
            'metadatum',
            array(
                'name' => 'metadatum2',
                'description' => 'metadatum2',
                'collection' => $collection,
                'metadata_type'  => 'Tainacan\Metadata_Types\Text',
                'status' => 'publish'
            ),
            true
        );


        $metadatum3 = $this->tainacan_entity_factory->create_entity(
            'metadatum',
            array(
                'name' => 'metadatum3',
                'description' => 'metadatum3',
                'collection' => $collection,
                'metadata_type'  => 'Tainacan\Metadata_Types\Text',
                'status' => 'publish'
            ),
            true
        );

        $collection->set_metadata_order(
            [
                array( 'id' => $metadatum3->get_id(), 'enabled' => false ),
                array( 'id' => $metadatum2->get_id(), 'enabled' => true ),
                array( 'id' => $metadatum1->get_id(), 'enabled' => true )
            ]);

        $update_collection = $Tainacan_Collections->update( $collection );
        
        $metadata_ordinate = $Tainacan_Metadata->fetch_by_collection( $update_collection, [], 'OBJECT' );
        $this->assertEquals( 'metadatum2', $metadata_ordinate[0]->get_name() );

        $metadata_ordinate_enabled = $Tainacan_Metadata->fetch_by_collection( $update_collection, [ 'include_disabled' => true ], 'OBJECT' );
        $this->assertEquals( 'metadatum3', $metadata_ordinate_enabled[0]->get_name() );
		
		$this->assertFalse($metadata_ordinate_enabled[0]->get_enabled_for_collection());
		$this->assertTrue($metadata_ordinate_enabled[1]->get_enabled_for_collection());
		$this->assertTrue($metadata_ordinate_enabled[2]->get_enabled_for_collection());
    }
    
    function test_unique_slugs() {
		$x = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'status'        => 'publish',
                'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
        
        $y = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'status'        => 'publish',
                'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
        
        $this->assertNotEquals($x->get_slug(), $y->get_slug());
        
        // Create as draft and publish later
        $x = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
        
        $y = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
                'slug'          => 'duplicated_slug',
                'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
        
        $this->assertEquals($x->get_slug(), $y->get_slug());
        
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $x->set_status('publish');
        $x->validate();
        $x = $Tainacan_Metadata->insert($x);
        $y->set_status('private'); // or publish shoud behave the same
        $y->validate();
        $y = $Tainacan_Metadata->insert($y);
        
        $this->assertNotEquals($x->get_slug(), $y->get_slug());
        
    }

    function test_validation_of_metadata_types() {
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste'
	        ),
	        true
        );

        
        $validMetadatum = new \Tainacan\Entities\Metadatum();
        $validMetadatum->set_name('test');
        $validMetadatum->set_description('test');
        $validMetadatum->set_collection($collection);
        $validMetadatum->set_metadata_type('Tainacan\Metadata_Types\Relationship');
        $validMetadatum->set_metadata_type_options(['collection_id' => 12]);
        
        $this->assertTrue($validMetadatum->validate());
        
        $invalidMetadatum = new \Tainacan\Entities\Metadatum();
        $invalidMetadatum->set_name('test');
        $invalidMetadatum->set_description('test');
        $invalidMetadatum->set_collection($collection);
        $invalidMetadatum->set_status('publish');
        $invalidMetadatum->set_metadata_type('Tainacan\Metadata_Types\Relationship');
        $invalidMetadatum->set_metadata_type_options(['collection_id' => 'string']);
        
        $this->assertFalse($invalidMetadatum->validate());

    }
	
	function test_metadata_type_option_index() {

        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        
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
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
					'allow_new_terms' => 'no'
				]
	        ),
	        true
        );
        
		$test_meta = get_post_meta($metadatum->get_id(), '_option_taxonomy_id', true);
		$this->assertEquals( $tax->get_id(), $test_meta );
		
		$fetch = $Tainacan_Metadata->fetch([
			'meta_query' => [
				[
					'key' => '_option_taxonomy_id',
					'value' => $tax->get_id()
				]
			]
		], 'OBJECT');
		
		$this->assertEquals(1, count($fetch));
		
		$this->assertEquals($metadatum->get_id(), $fetch[0]->get_id());
		
    }
	
	function test_relatioship_get_collection_method() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste'
			),
			true
		);
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste2'
			),
			true
		);
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Relationship',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'collection_id' => $collection2->get_id(),
					'search' => [$collection2->get_core_title_metadatum()->get_id()]
					]
				),
				true
		);
			
		$object = $metadatum->get_metadata_type_object();
		
		$colCheck = $object->get_collection();
		
		$this->assertTrue( $colCheck instanceof \Tainacan\Entities\Collection );
		$this->assertEquals($collection2->get_id(), $colCheck->get_id());
		
	}
}

/**
 * Class TainacanMetadatumType
 */
class RandomType extends Metadata_Types\Metadata_Type {

    function __construct(){
        parent::__construct();
    }

    /**
     * @param $metadatum
     * @return string
     */

    public function render( $metadatum ){}
}