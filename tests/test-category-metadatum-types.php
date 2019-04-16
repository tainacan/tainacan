<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Sample test case.
 */
class TaxonomyMetadatumTypes extends TAINACAN_UnitTestCase {

	
    function test_taxonomy_metadata_types() {

        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
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
					'allow_new_terms' => false
				]
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
		
        $metadatum2 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta2',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'draft',
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

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
		$item_metadata->set_value('Red');

		$this->assertTrue($item_metadata->validate(), 'item metadata should validate because it is an existing term');

		$Tainacan_Item_Metadata->insert($item_metadata);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
		$item_metadata->set_value('love');

		$this->assertFalse($item_metadata->validate(), 'item metadata should not validate because it does not allow new terms');

		// Lets change it
		$options = $metadatum->get_metadata_type_options();
		$options['allow_new_terms'] = true;
		$metadatum->set_metadata_type_options($options);
		$metadatum->validate();
		$metadatum = $Tainacan_Metadata->insert($metadatum);

		$item_metadata->set_metadatum($metadatum);

		$this->assertTrue($item_metadata->validate(), 'item metada should validate because it now allows new terms');

		$Tainacan_Item_Metadata->insert($item_metadata);

		$checkItem = $Tainacan_Items->fetch($i->get_id());

		$check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($checkItem, $metadatum);

		$this->assertEquals('Tainacan\Entities\Term', get_class($check_item_metadata->get_value()));
		
		// test 2 metadata with same taxonomy
		$metadatum2->set_metadata_type_options([
			'taxonomy_id' => $tax->get_id(),
		]);
		$metadatum2->set_status('publish');
		
		$this->assertFalse($metadatum2->validate(), 'Taxonomy Metadatum should not validate when using a taxonomy in use by another metadatum in the same collection');
		$errors = $metadatum2->get_errors();
		$this->assertInternalType('array', $errors);
		$this->assertArrayHasKey('taxonomy_id', $errors[0]['metadata_type_options']);
    }
	
	function test_relate_taxonomy() {
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax2 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test2',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax3 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test3',
				'status'	 => 'publish',
			),
			true
		);
        
		$this->assertNotContains($tax->get_db_identifier(), get_object_taxonomies($collection->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		
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
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		$this->assertContains($tax->get_db_identifier(), get_object_taxonomies($collection->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		
        
		$checkTax = $Tainacan_Taxonomies->fetch($tax->get_id());
		$this->assertContains($collection->get_id(), $checkTax->get_collections_ids(), 'Collection must be added to taxonomy when metadatum is created');
		
		
		$metadatum->set_metadata_type_options([
			'taxonomy_id' => $tax2->get_id(),
			'allow_new_terms' => false
		]);
		
		$metadatum->validate();
		$metadatum = $Tainacan_Metadata->insert($metadatum);
		
		$checkTax = $Tainacan_Taxonomies->fetch($tax->get_id());
		$checkTax2 = $Tainacan_Taxonomies->fetch($tax2->get_id());
		$this->assertContains($collection->get_id(), $checkTax2->get_collections_ids(), 'Collection must be added to taxonomy when metadatum is updated');
		$this->assertNotContains($collection->get_id(), $checkTax->get_collections_ids(), 'Collection must be removed from taxonomy when metadatum is updated');
		
		$metadatum = $Tainacan_Metadata->trash($metadatum->get_id());
		
		$checkTax2 = $Tainacan_Taxonomies->fetch($tax2->get_id());
		
		$this->assertNotContains($collection->get_id(), $checkTax2->get_collections_ids(), 'Collection must be removed from taxonomy when metadatum is deleted');
		
		
		$metadatum_repo = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta_repo',
		        'description' => 'description',
		        'collection_id' => 'default',
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax3->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		$this->assertContains($tax3->get_db_identifier(), get_object_taxonomies($collection->get_db_identifier()), 'Taxonommy used by repository level metadatum must be assigned to all collections post types');
		
		
		
		
    }
	
	function test_relate_taxonomy_match() {
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test2',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax2 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test2',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax3 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test3',
				'status'	 => 'publish',
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
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		$metadatum2 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta2',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax2->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		$metadatum3 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta3',
		        'description' => 'description',
		        'collection' => $collection2,
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax2->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		$metadatum4 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta4',
		        'description' => 'description',
		        'collection' => $collection2,
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax3->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		$checkTax = $Tainacan_Taxonomies->fetch($tax->get_id());
		$checkTax2 = $Tainacan_Taxonomies->fetch($tax2->get_id());
		$checkTax3 = $Tainacan_Taxonomies->fetch($tax3->get_id());
		
		$this->assertEquals( sizeof($checkTax->get_collections_ids()), sizeof($checkTax->get_collections()) );
		$this->assertEquals( sizeof($checkTax2->get_collections_ids()), sizeof($checkTax2->get_collections()) );
		$this->assertEquals( sizeof($checkTax3->get_collections_ids()), sizeof($checkTax3->get_collections()) );
		
		$this->assertNotContains($checkTax3->get_db_identifier(), get_object_taxonomies($collection->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		$this->assertContains($checkTax->get_db_identifier(), get_object_taxonomies($collection->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		$this->assertContains($checkTax2->get_db_identifier(), get_object_taxonomies($collection->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		
		$this->assertNotContains($checkTax->get_db_identifier(), get_object_taxonomies($collection2->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		$this->assertContains($checkTax3->get_db_identifier(), get_object_taxonomies($collection2->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		$this->assertContains($checkTax2->get_db_identifier(), get_object_taxonomies($collection2->get_db_identifier()), 'Collection must be added to taxonomy when metadatum is created');
		
		$this->assertEquals(2, sizeof( get_object_taxonomies($collection2->get_db_identifier()) ));
		$this->assertEquals(2, sizeof( get_object_taxonomies($collection->get_db_identifier()) ));
    }
	
	/**
	 * @group fetch_by_collection
	 */
	function test_fetch_by_collection() {
		
		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_c = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_gc = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2_c->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax2 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test2',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax3 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test3',
				'status'	 => 'publish',
			),
			true
		);
		
		$tax4 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test4',
				'status'	 => 'publish',
			),
			true
		);
		
		
		// metadata 1 in repo level for every one
		$metadatum_repo = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta_repo',
		        'description' => 'description',
		        'collection_id' => 'default',
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		// meta 2 in collection 1 just for it
		$metadatum2 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta_repo',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax2->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		// meta 3 in collection 2 for it and chidlren and grand children 
		$metadatum3 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta_repo',
		        'description' => 'description',
		        'collection' => $collection2,
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax3->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		// meta 4 in collection 2c only for children and grand children
		$metadatum4 = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta_repo',
		        'description' => 'description',
		        'collection' => $collection2_c,
		        'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax4->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
		
		$taxonomies_1 = $Tainacan_Taxonomies->fetch_by_collection($collection, [], 'OBJECT');
		$this->assertEquals(2, sizeof($taxonomies_1));
		
		$taxonomies_2 = $Tainacan_Taxonomies->fetch_by_collection($collection2, [], 'OBJECT');
		$this->assertEquals(2, sizeof($taxonomies_2));
		
		$taxonomies_3 = $Tainacan_Taxonomies->fetch_by_collection($collection2_c, [], 'OBJECT');
		$this->assertEquals(3, sizeof($taxonomies_3));
		
		$taxonomies_4 = $Tainacan_Taxonomies->fetch_by_collection($collection2_gc, [], 'OBJECT');
		$this->assertEquals(3, sizeof($taxonomies_4));
		
		
	}
	
	function test_values_and_html() {
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        $Tainacan_ItemMetadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        
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
				'status' => 'publish'
			),
			true
		);
		
		$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'orange',
					'collection' => $collection,
					'status'	 => 'publish'
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
					'allow_new_terms' => true
				]
	        ),
	        true
        );
		
	
        
		$meta = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
		
		$meta->set_value('new_term');
		
		$meta->validate();
		
		$meta = $Tainacan_ItemMetadata->insert($meta);
		
		$this->assertInternalType( 'string', $meta->get_value_as_html() );
		$this->assertInternalType( 'string', $meta->get_value_as_string() );
		
		$this->assertInternalType( 'integer', strpos($meta->get_value_as_html(), '<a ') );
		$this->assertFalse( strpos($meta->get_value_as_string(), '<a ') );
		
		
    }

	function test_validate_private_taxonomy() {
		
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        $Tainacan_ItemMetadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        
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
				'status' => 'private'
			),
			true
		);
		
		$meta = new \Tainacan\Entities\Metadatum();
		
		$meta->set_name('test meta');
		$meta->set_status('publish');
		$meta->set_metadata_type('Tainacan\Metadata_Types\Taxonomy');
		$meta->set_collection($collection);
		$meta->set_metadata_type_options([
			'taxonomy_id' => $tax->get_id(),
			'allow_new_terms' => true
		]);
		
		$this->assertFalse($meta->validate(), 'Metadatum should not validate because taxonomy is private');
		
		$meta->set_status('private');
		
		$this->assertTrue($meta->validate(), 'Metadatum should validate because it is private now');
		
		
		
		
		
	}
    
}