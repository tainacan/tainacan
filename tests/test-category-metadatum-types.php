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
class CategoryMetadatumTypes extends TAINACAN_UnitTestCase {

	
    function test_category_metadatum_types() {

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
			),
			true
		);
        
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadatum_type' => 'Tainacan\Metadatum_Types\Category',
				'status'	 => 'publish',
				'metadatum_type_options' => [
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
		        'metadatum_type' => 'Tainacan\Metadatum_Types\Category',
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
		$options = $metadatum->get_metadatum_type_options();
		$options['allow_new_terms'] = true;
		$metadatum->set_metadatum_type_options($options);
		$metadatum->validate();
		$metadatum = $Tainacan_Metadata->insert($metadatum);

		$item_metadata->set_metadatum($metadatum);

		$this->assertTrue($item_metadata->validate(), 'item metada should validate because it now allows new terms');

		$Tainacan_Item_Metadata->insert($item_metadata);

		$checkItem = $Tainacan_Items->fetch($i->get_id());

		$check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($checkItem, $metadatum);

		$this->assertEquals('Tainacan\Entities\Term', get_class($check_item_metadata->get_value()));
		
		// test 2 metadata with same category
		$metadatum2->set_metadatum_type_options([
			'taxonomy_id' => $tax->get_id(),
		]);
		$metadatum2->set_status('publish');
		
		$this->assertFalse($metadatum2->validate(), 'Category Metadatum should not validate when using a category in use by another metadatum in the same collection');
		$errors = $metadatum2->get_errors();
		$this->assertInternalType('array', $errors);
		$this->assertArrayHasKey('taxonomy_id', $errors[0]['metadatum_type_options']);
    }
	
	function test_relate_taxonomy() {
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        
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
			),
			true
		);
		
		$tax2 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test2',
			),
			true
		);
        
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadatum_type' => 'Tainacan\Metadatum_Types\Category',
				'status'	 => 'publish',
				'metadatum_type_options' => [
					'taxonomy_id' => $tax->get_id(),
					'allow_new_terms' => false
				]
	        ),
	        true
        );
        
		$checkTax = $Tainacan_Taxonomies->fetch($tax->get_id());
		$this->assertContains($collection->get_id(), $checkTax->get_collections_ids(), 'Collection must be added to taxonomy when metadatum is created');
		
		$metadatum->set_metadatum_type_options([
			'taxonomy_id' => $tax2->get_id(),
			'allow_new_terms' => false
		]);
		
		$metadatum->validate();
		$metadatum = $Tainacan_Metadata->insert($metadatum);
		
		$checkTax = $Tainacan_Taxonomies->fetch($tax->get_id());
		$checkTax2 = $Tainacan_Taxonomies->fetch($tax2->get_id());
		$this->assertContains($collection->get_id(), $checkTax2->get_collections_ids(), 'Collection must be added to taxonomy when metadatum is updated');
		$this->assertNotContains($collection->get_id(), $checkTax->get_collections_ids(), 'Collection must be removed from taxonomy when metadatum is updated');
		
		$metadatum = $Tainacan_Metadata->delete($metadatum->get_id());
		
		$checkTax2 = $Tainacan_Taxonomies->fetch($tax2->get_id());
		
		$this->assertNotContains($collection->get_id(), $checkTax2->get_collections_ids(), 'Collection must be removed from taxonomy when metadatum is deleted');
		
		
		
		
		
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
		        'metadatum_type' => 'Tainacan\Metadatum_Types\Category',
				'status'	 => 'publish',
				'metadatum_type_options' => [
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
    
}