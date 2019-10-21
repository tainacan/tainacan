<?php

namespace Tainacan\Tests;

use Tainacan\Entities\Collection;

/**
 * Class Capabilities
 *
 * @package Test_Tainacan
 */

/**
 * @group permissions
 */
class Capabilities extends TAINACAN_UnitTestCase {
	
	function setUp() {
		parent::setUp();
		
		$subscriber = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		$this->subscriber = get_userdata( $subscriber );
		
		wp_set_current_user($subscriber);
		
		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);
		$this->public_collection = $collection1;
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'private'
			),
			true
		);
		$this->private_collection = $collection2;
		
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'genero',
				'description'  => 'tipos de musica',
				'allow_insert' => 'yes',
				'status' => 'publish'
			),
			true
		);
		
		$this->public_taxonomy = $taxonomy;
		
		$taxonomy2 = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'genero2',
				'description'  => 'tipos de musica2',
				'allow_insert' => 'yes',
				'status' => 'publish'
			),
			true
		);
		
		$this->private_taxonomy = $taxonomy;
		
		$term_1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Term for collection 1'
			),
			true
		);
		$term_2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Term for collection 2'
			),
			true
		);
		$term_all = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Term for all'
			),
			true
		);
		
		$metadatum_text = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'text',
			    'status' => 'publish',
			    'collection' => $collection1,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
		);
		
		$this->public_metadatum = $metadatum_text;
		
		$metadatum_repo = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'repo',
			    'status' => 'publish',
			    'collection_id' => 'default',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
		);
		
		$this->public_repo_metadatum = $metadatum_repo;
		
		$metadatum_text2 = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'text',
			    'status' => 'private',
			    'collection' => $collection1,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
		);
		
		$this->private_metadatum = $metadatum_text2;
		
		$metadatum_repo2 = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'repo',
			    'status' => 'private',
			    'collection_id' => 'default',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
		    ),
		    true
		);
		
		$this->private_repo_metadatum = $metadatum_repo2;
		
		$meta_relationship = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'relationship',
			    'status' => 'publish',
			    'collection' => $collection2,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'collection_id' => $collection1->get_id(),
					'search' => ''
				]
		    ),
		    true
	    );
		
		$this->meta_relationship = $meta_relationship;
		
		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'       => 'filtro',
				'collection' => $collection1,
				'description' => 'Teste Filtro',
				'status' => 'publish',
				'metadatum'  => $metadatum_text,
			),
			true
		);
		
		$this->public_filter = $filter;
		
		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'       => 'filtro',
				'collection_id' => 'default',
				'description' => 'Teste Filtro',
				'status' => 'publish',
				'metadatum'  => $metadatum_repo,
			),
			true
		);
		
		$this->public_repo_filter = $filter;
		
		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'       => 'filtro',
				'collection' => $collection1,
				'description' => 'Teste Filtro',
				'status' => 'private',
				'metadatum'  => $metadatum_text2,
			),
			true
		);
		
		$this->private_filter = $filter;
		
		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'       => 'filtro',
				'collection_id' => 'default',
				'description' => 'Teste Filtro',
				'status' => 'private',
				'metadatum'  => $metadatum_repo2,
			),
			true
		);
		
		$this->private_repo_filter = $filter;
		
		
		for ($i = 1; $i<=10; $i++) {
			
			$title = 'testeItem ' . str_pad($i, 2, "0", STR_PAD_LEFT); 
			
			$col = $i <= 5 ? $collection1 : $collection2;
			
			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => $title,
					'collection' => $col,
					'status' => 'publish'
				),
				true
			);
			
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_repo, 'Value ' . $i);
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_repo2, 'Value ' . $i);
			
			if ($i <= 5) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_text, $i % 2 == 0 ? 'even' : 'odd');
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_text2, $i % 2 == 0 ? 'even' : 'odd');
			} 
			
		}
		
		$subscriber = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		$this->subscriber2 = get_userdata( $subscriber );
		
	}
	
	/**
	 * 
	 */
	function test_super_manage_tainacan () {
		
		$this->assertFalse( user_can($this->subscriber, 'tnc_rep_manage_taxonomies') );
		
		$this->subscriber->add_cap('manage_tainacan');
		$this->subscriber->get_role_caps();
		
		$this->assertTrue( user_can($this->subscriber, 'tnc_rep_manage_taxonomies') );
		
	}
	
	function test_super_manage_tainacan_collection () {
		
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		
		$this->subscriber->add_cap('manage_tainacan_collection_25');
		$this->subscriber->get_role_caps();
		
		$this->assertTrue( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_36_read_private_filters') );
		
	}
	
	function test_super_all_collection () {
		
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		
		$this->subscriber->add_cap('tnc_col_all_read_private_filters');
		$this->subscriber->get_role_caps();
		
		$this->assertTrue( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		$this->assertTrue( user_can($this->subscriber, 'tnc_col_36_read_private_filters') );
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_25_edit_posts') );
		
	}
	
	// function test_items_capabilities() {
	// 	
	// 	$collection = $this->tainacan_entity_factory->create_entity(
	// 		'collection',
	// 		array(
	// 			'name'        => 'Javascript Frameworks',
	// 			'description' => 'The best framework to javascript',
	// 			'status' => 'publish'
	// 		),
	// 		true
	// 	);
	// 	
	// 	$caps = $collection->get_items_capabilities();
	// 	
	// }
	
	/**
	 * @group metadata
	 */
	function test_metadata_metacap() {
		
		wp_set_current_user($this->subscriber2->ID);
		
		$this->assertFalse( $this->public_metadatum->can_edit() );
		$this->assertFalse( $this->public_repo_metadatum->can_edit() );
		$this->assertFalse( $this->private_metadatum->can_edit() );
		$this->assertFalse( $this->private_repo_metadatum->can_edit() );
		
		$this->subscriber2->add_cap( 'tnc_rep_manage_metadata' );
		
		$this->assertFalse( $this->public_metadatum->can_edit() );
		$this->assertTrue( $this->public_repo_metadatum->can_edit() );
		$this->assertFalse( $this->private_metadatum->can_edit() );
		$this->assertTrue( $this->private_repo_metadatum->can_edit() );
		
		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_manage_metadata' );
		
		$this->assertTrue( $this->public_metadatum->can_edit() );
		$this->assertTrue( $this->public_repo_metadatum->can_edit() );
		$this->assertTrue( $this->private_metadatum->can_edit() );
		$this->assertTrue( $this->private_repo_metadatum->can_edit() );
		
		
	}
	
}