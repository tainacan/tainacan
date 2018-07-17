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
class CoreMetadatumTypes extends TAINACAN_UnitTestCase {

	
    function test_core_metadata_types() {

        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
                'name'   => 'test',
                'status' => 'publish'
			),
			true
		);
        
        $metadatum = $collection->get_core_title_metadatum();
        
        $metadatumDescription = $collection->get_core_description_metadatum();
        
        
        $i = $this->tainacan_entity_factory->create_entity(
           'item',
           array(
               'title'         => 'item test',
               'description'   => 'adasdasdsa',
               'collection'    => $collection,
               'status'        => 'publish'
           ),
           true
       );
       
       
       $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
       $item_metadata->set_value('changed title');
       $item_metadata->validate();
       
       $Tainacan_Item_Metadata->insert($item_metadata);
       
       $checkItem = $Tainacan_Items->fetch($i->get_id());
       
       $this->assertEquals('changed title', $checkItem->get_title());
       
       $check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($checkItem, $metadatum);
       $this->assertEquals('changed title', $check_item_metadata->get_value());
       
       
       // description
       $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatumDescription);
       $item_metadata->set_value('changed description');
       $item_metadata->validate();
       
       $Tainacan_Item_Metadata->insert($item_metadata);
       
       $checkItem = $Tainacan_Items->fetch($i->get_id());
       
       $this->assertEquals('changed description', $checkItem->get_description());
       
       $check_item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($checkItem, $metadatumDescription);
       $this->assertEquals('changed description', $check_item_metadata->get_value());

       // check that the value was also stored in postmeta table
       $checkMeta = $Tainacan_Items->fetch([
           'meta_query' => [
               [
                   'key' => $metadatumDescription->get_id(),
                   'value' => 'changed description'
               ]
           ]
               ], [], 'OBJECT');

       $this->assertEquals(1, sizeof($checkMeta));
       $this->assertEquals('changed description', $checkMeta[0]->get_description());
       
    }

    function test_validate_required_title() {

        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'   => 'test',
            ),
            true
        );

        $i = $this->tainacan_entity_factory->create_entity(
            'item',
            array(
                'description'   => 'adasdasdsa',
                'collection'    => $collection,
                'status'        => 'draft'
            ),
            true
        );

        $metadata = $Tainacan_Metadata->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        foreach ( $metadata as $index => $metadatum ){
            if ( $metadatum->get_metadata_type_object()->get_core() && $metadatum->get_metadata_type_object()->get_related_mapped_prop() == 'title') {
                $core_title = $metadatum;
            }
        }



        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $core_title);
        $item_metadata->set_value('title');
        $item_metadata->validate();
        $Tainacan_Item_Metadata->insert($item_metadata);

        $i->set_status('publish' );

        $this->assertTrue($i->validate(), 'Item with empy title should validate because core title metadatum has value');

    }

    function test_dont_allow_multiple() {

        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'   => 'test',
            ),
            true
        );

        $metadata = $Tainacan_Metadata->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        foreach ( $metadata as $index => $metadatum ){
            if ( $metadatum->get_metadata_type_object()->get_core() && $metadatum->get_metadata_type_object()->get_related_mapped_prop() == 'title') {
                $core_title = $metadatum;
            }
            if ( $metadatum->get_metadata_type_object()->get_core() && $metadatum->get_metadata_type_object()->get_related_mapped_prop() == 'description') {
                $core_description = $metadatum;
            }
        }

        $core_title->set_multiple('yes');
        $core_description->set_multiple('yes');

        $this->assertFalse($core_title->validate(), 'Core metadata should not validate because it can not allow it to have multiple');
        $this->assertFalse($core_description->validate(), 'Core metadata should not validate because it can not allow it to have multiple');

    }

    function test_collection_getters() {

        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
        );

        $metadatumDescription = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'just to confuse',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Text'
	        ),
	        true
        );
        
        $core_metadata = $collection->get_core_metadata();

        $this->assertEquals(2, sizeof($core_metadata));

        $this->assertNotEquals('Tainacan\Metadata_Types\Text', $core_metadata[0]->get_metadata_type());
        $this->assertNotEquals('Tainacan\Metadata_Types\Text', $core_metadata[1]->get_metadata_type());

        $title = $collection->get_core_title_metadatum();

        $this->assertEquals('Tainacan\Metadata_Types\Core_Title', $title->get_metadata_type());

        $description = $collection->get_core_description_metadatum();

        $this->assertEquals('Tainacan\Metadata_Types\Core_Description', $description->get_metadata_type());


    }

    function test_collection_parent_change_and_update_core_metadata(){
        global $wpdb;
        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

	    $collection_parent2 = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'   => 'Parent2',
			    'status' => 'publish'
		    ),
		    true
        );
        
        $core_metadata_parent2 = $collection_parent2->get_core_metadata();

	    $collection_parent = $this->tainacan_entity_factory->create_entity(
		    'collection',
		    array(
			    'name'   => 'Parent',
			    'status' => 'publish'
		    ),
		    true
	    );

        $core_metadata_parent = $collection_parent->get_core_metadata();

	    $collection_son = $this->tainacan_entity_factory->create_entity(
	    	'collection',
		    array(
		    	'name' => 'Son',
			    'status' => 'publish'
		    ),
		    true
	    );

	    $core_metadata_son = $collection_son->get_core_metadata();

	    # Creates a item
	    $collection_son_item = $this->tainacan_entity_factory->create_entity(
	    	'item',
		    array(
		    	'title'       => 'Son of son',
		    	'description' => 'Desc of son of son',
		    	'collection'  => $collection_son,
			    'status' => 'publish'
		    ),
		    true
	    );

	    $item_metadatum_title = $this->tainacan_item_metadata_factory->create_item_metadata($collection_son_item,
		    $core_metadata_son[1], 'Son of son');

	    $item_metadatum_desc = $this->tainacan_item_metadata_factory->create_item_metadata($collection_son_item, $core_metadata_son[0],
		    'Desc of son of son');

	    $this->assertEquals($core_metadata_son[0]->get_id(), $item_metadatum_desc->get_metadatum()->get_id());
	    $this->assertEquals($core_metadata_son[1]->get_id(), $item_metadatum_title->get_metadatum()->get_id());

	    $this->assertNotEquals($core_metadata_parent[0]->get_id(), $item_metadatum_desc->get_metadatum()->get_id());
	    $this->assertNotEquals($core_metadata_parent[1]->get_id(), $item_metadatum_title->get_metadatum()->get_id());

	    # When updated the parent, the item metadata key, have to be update too
	    $collection_son->set_parent($collection_parent->get_id());

        $collection_son->validate();
        
        $collection_son = $Tainacan_Collections->update($collection_son);
        
        $check_deleted = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE ID = %d", $core_metadata_son[0]->get_id()) );
        $this->assertEquals(0, $check_deleted);
        $check_deleted = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE ID = %d", $core_metadata_son[1]->get_id()) );
        $this->assertEquals(0, $check_deleted);

	    $core_metadata_son = $collection_son->get_core_metadata();

	    $this->assertNotEquals($core_metadata_son[0]->get_id(), $item_metadatum_desc->get_metadatum()->get_id());
	    $this->assertNotEquals($core_metadata_son[1]->get_id(), $item_metadatum_title->get_metadatum()->get_id());

        $it = get_post_meta($collection_son_item->get_id());
        
	    $this->assertArrayHasKey($core_metadata_parent[0]->get_id(), $it);
	    $this->assertArrayHasKey($core_metadata_parent[1]->get_id(), $it);

	    # Changes parent again
	    $collection_son->set_parent(0);

        $collection_son->validate();
        
	    $collection_son = $Tainacan_Collections->update($collection_son);

	    $core_metadata_son2 = $collection_son->get_core_metadata();

	    $it2 = get_post_meta($collection_son_item->get_id());

	    $this->assertArrayHasKey($core_metadata_son2[0]->get_id(), $it2);
	    $this->assertArrayHasKey($core_metadata_son2[1]->get_id(), $it2);

	    $this->assertArrayNotHasKey($core_metadata_parent[0]->get_id(), $it2);
	    $this->assertArrayNotHasKey($core_metadata_parent[1]->get_id(), $it2);

	    # Changes parent again
	    $collection_son->set_parent($collection_parent2->get_id());

        $collection_son->validate();
        
        $collection_son = $Tainacan_Collections->update($collection_son);
        
        $check_deleted = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE ID = %d", $core_metadata_son2[0]->get_id()) );
        $this->assertEquals(0, $check_deleted);
        $check_deleted = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE ID = %d", $core_metadata_son2[1]->get_id()) );
        $this->assertEquals(0, $check_deleted);

        $core_metadata_son3 = $collection_son->get_core_metadata();
        
        $this->assertEquals( $core_metadata_son3[0]->get_id(), $core_metadata_parent2[0]->get_id() );

        $it3 = get_post_meta($collection_son_item->get_id());

	    $this->assertArrayHasKey($core_metadata_son3[0]->get_id(), $it3);
	    $this->assertArrayHasKey($core_metadata_son3[1]->get_id(), $it3);

	    $this->assertArrayNotHasKey($core_metadata_parent[0]->get_id(), $it3);
        $this->assertArrayNotHasKey($core_metadata_parent[1]->get_id(), $it3);
        

        # Changes parent again to another parent directly
	    $collection_son->set_parent($collection_parent->get_id());

        $collection_son->validate();
        
        $collection_son = $Tainacan_Collections->update($collection_son);

        $core_metadata_son4 = $collection_son->get_core_metadata();
        
        $this->assertEquals( $core_metadata_son4[0]->get_id(), $core_metadata_parent[0]->get_id() );

        $it4 = get_post_meta($collection_son_item->get_id());

	    $this->assertArrayHasKey($core_metadata_son4[0]->get_id(), $it4);
	    $this->assertArrayHasKey($core_metadata_son4[1]->get_id(), $it4);

	    $this->assertArrayNotHasKey($core_metadata_parent2[0]->get_id(), $it4);
	    $this->assertArrayNotHasKey($core_metadata_parent2[1]->get_id(), $it4);

    }
    
}