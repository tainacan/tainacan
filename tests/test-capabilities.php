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

		/**
		 * Test fixtures:
		 *
		 * Repo
		 * - public_taxonomy
		 * - private_taxonomy
		 * - public_repo_metadatum
		 * - private_repo_metadatum
		 * - public_repo_filter
		 * - private_repo_filter
		 * - public_collection (5 items)
		 * --- (Core Title adn Description)
		 * --- public_metadatum
		 * --- private_metadatum
		 * --- public_filter
		 * --- private_filter
		 * - private_collection (5 items)
		 * --- (Core Title adn Description)
		 * --- meta_relationshipt (with public collection)
		 */
		$subscriber = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		$this->subscriber = get_userdata( $subscriber );

		wp_set_current_user($subscriber);

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Public Col',
				'status' => 'publish'
			),
			true
		);
		$this->public_collection = $collection1;

		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'Private Col',
				'status' => 'private'
			),
			true
		);
		$this->private_collection = $collection2;

		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'Public Tax',
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
				'name'         => 'Private Tax',
				'description'  => 'tipos de musica2',
				'allow_insert' => 'yes',
				'status' => 'private'
			),
			true
		);

		$this->private_taxonomy = $taxonomy2;

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
			    'name'   => 'Public meta',
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
			    'name'   => 'Public Repo Meta',
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
			    'name'   => 'Private Meta',
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
			    'name'   => 'Private Repo Meta',
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
				'name'       => 'Public filter',
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
				'name'       => 'Public repo filter',
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
				'name'       => 'Private Filter',
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
				'name'       => 'Private repo filter',
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

		$this->assertTrue( user_can($this->subscriber, 'tnc_rep_manage_taxonomies') );

	}

	function test_super_manage_tainacan_collection () {

		$this->assertFalse( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );

		$this->subscriber->add_cap('manage_tainacan_collection_25');

		$this->assertTrue( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_36_read_private_filters') );

	}

	function test_super_all_collection () {

		$this->assertFalse( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );

		$this->subscriber->add_cap('tnc_col_all_read_private_filters');

		$this->assertTrue( user_can($this->subscriber, 'tnc_col_25_read_private_filters') );
		$this->assertTrue( user_can($this->subscriber, 'tnc_col_36_read_private_filters') );
		$this->assertFalse( user_can($this->subscriber, 'tnc_col_25_edit_posts') );

		$this->assertFalse( user_can($this->subscriber2, 'tnc_col_25_read_private_filters') );

		$this->subscriber2->add_cap('manage_tainacan_collection_all');

		$this->assertTrue( user_can($this->subscriber2, 'tnc_col_25_read_private_filters') );
		$this->assertTrue( user_can($this->subscriber2, 'tnc_col_36_read_private_filters') );

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

		// edit and delete
		$this->assertFalse( $this->public_metadatum->can_edit() );
		$this->assertFalse( $this->public_repo_metadatum->can_edit() );
		$this->assertFalse( $this->private_metadatum->can_edit() );
		$this->assertFalse( $this->private_repo_metadatum->can_edit() );
		$this->assertFalse( $this->public_metadatum->can_delete() );
		$this->assertFalse( $this->public_repo_metadatum->can_delete() );
		$this->assertFalse( $this->private_metadatum->can_delete() );
		$this->assertFalse( $this->private_repo_metadatum->can_delete() );


		$this->subscriber2->add_cap( 'tnc_rep_manage_metadata' );

		$this->assertFalse( $this->public_metadatum->can_edit() );
		$this->assertTrue( $this->public_repo_metadatum->can_edit() );
		$this->assertFalse( $this->private_metadatum->can_edit() );
		$this->assertTrue( $this->private_repo_metadatum->can_edit() );
		$this->assertFalse( $this->public_metadatum->can_delete() );
		$this->assertTrue( $this->public_repo_metadatum->can_delete() );
		$this->assertFalse( $this->private_metadatum->can_delete() );
		$this->assertTrue( $this->private_repo_metadatum->can_delete() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_manage_metadata' );

		$this->assertTrue( $this->public_metadatum->can_edit() );
		$this->assertTrue( $this->public_repo_metadatum->can_edit() );
		$this->assertTrue( $this->private_metadatum->can_edit() );
		$this->assertTrue( $this->private_repo_metadatum->can_edit() );
		$this->assertTrue( $this->public_metadatum->can_delete() );
		$this->assertTrue( $this->public_repo_metadatum->can_delete() );
		$this->assertTrue( $this->private_metadatum->can_delete() );
		$this->assertTrue( $this->private_repo_metadatum->can_delete() );

		// read
		$this->assertTrue( $this->public_metadatum->can_read() );
		$this->assertTrue( $this->public_repo_metadatum->can_read() );
		$this->assertFalse( $this->private_metadatum->can_read() );
		$this->assertFalse( $this->private_repo_metadatum->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_read_private_metadata' );

		$this->assertTrue( $this->public_metadatum->can_read() );
		$this->assertTrue( $this->public_repo_metadatum->can_read() );
		$this->assertFalse( $this->private_metadatum->can_read() );
		$this->assertTrue( $this->private_repo_metadatum->can_read() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_read_private_metadata' );

		$this->assertTrue( $this->public_metadatum->can_read() );
		$this->assertTrue( $this->public_repo_metadatum->can_read() );
		$this->assertTrue( $this->private_metadatum->can_read() );
		$this->assertTrue( $this->private_repo_metadatum->can_read() );
	}

	/**
	 * @group filters
	 */
	function test_filters_metacap() {

		wp_set_current_user($this->subscriber2->ID);

		// edit and delete
		$this->assertFalse( $this->public_filter->can_edit() );
		$this->assertFalse( $this->public_repo_filter->can_edit() );
		$this->assertFalse( $this->private_filter->can_edit() );
		$this->assertFalse( $this->private_repo_filter->can_edit() );
		$this->assertFalse( $this->public_filter->can_delete() );
		$this->assertFalse( $this->public_repo_filter->can_delete() );
		$this->assertFalse( $this->private_filter->can_delete() );
		$this->assertFalse( $this->private_repo_filter->can_delete() );

		$this->subscriber2->add_cap( 'tnc_rep_manage_filters' );

		$this->assertFalse( $this->public_filter->can_edit() );
		$this->assertTrue( $this->public_repo_filter->can_edit() );
		$this->assertFalse( $this->private_filter->can_edit() );
		$this->assertTrue( $this->private_repo_filter->can_edit() );
		$this->assertFalse( $this->public_filter->can_delete() );
		$this->assertTrue( $this->public_repo_filter->can_delete() );
		$this->assertFalse( $this->private_filter->can_delete() );
		$this->assertTrue( $this->private_repo_filter->can_delete() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_manage_filters' );

		$this->assertTrue( $this->public_filter->can_edit() );
		$this->assertTrue( $this->public_repo_filter->can_edit() );
		$this->assertTrue( $this->private_filter->can_edit() );
		$this->assertTrue( $this->private_repo_filter->can_edit() );
		$this->assertTrue( $this->public_filter->can_delete() );
		$this->assertTrue( $this->public_repo_filter->can_delete() );
		$this->assertTrue( $this->private_filter->can_delete() );
		$this->assertTrue( $this->private_repo_filter->can_delete() );

		// read
		$this->assertTrue( $this->public_filter->can_read() );
		$this->assertTrue( $this->public_repo_filter->can_read() );
		$this->assertFalse( $this->private_filter->can_read() );
		$this->assertFalse( $this->private_repo_filter->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_read_private_filters' );

		$this->assertTrue( $this->public_filter->can_read() );
		$this->assertTrue( $this->public_repo_filter->can_read() );
		$this->assertFalse( $this->private_filter->can_read() );
		$this->assertTrue( $this->private_repo_filter->can_read() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_read_private_filters' );

		$this->assertTrue( $this->public_filter->can_read() );
		$this->assertTrue( $this->public_repo_filter->can_read() );
		$this->assertTrue( $this->private_filter->can_read() );
		$this->assertTrue( $this->private_repo_filter->can_read() );


	}

	/**
	 * @group fetch_by_collection
	 */
	function test_fetch_meta_by_collection() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$meta = tainacan_metadata()->fetch_by_collection($this->public_collection);
		$this->AssertEquals(4, sizeof($meta));
		$meta = tainacan_metadata()->fetch_ids_by_collection($this->public_collection);
		$this->AssertEquals(4, sizeof($meta));

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_read_private_metadata' );
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$meta = tainacan_metadata()->fetch_by_collection($this->public_collection);
		$this->AssertEquals(5, sizeof($meta));
		$meta = tainacan_metadata()->fetch_ids_by_collection($this->public_collection);
		$this->AssertEquals(5, sizeof($meta));

		$this->subscriber2->add_cap( 'tnc_rep_read_private_metadata' );
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$meta = tainacan_metadata()->fetch_by_collection($this->public_collection);
		$this->AssertEquals(6, sizeof($meta));
		$meta = tainacan_metadata()->fetch_ids_by_collection($this->public_collection);
		$this->AssertEquals(6, sizeof($meta));


	}

	/**
	 * @group fetch_by_collection
	 */
	function test_fetch_filter_by_collection() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$meta = tainacan_filters()->fetch_by_collection($this->public_collection);
		$this->AssertEquals(2, sizeof($meta));
		$meta = tainacan_filters()->fetch_ids_by_collection($this->public_collection);
		$this->AssertEquals(2, sizeof($meta));

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_read_private_filters' );
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$meta = tainacan_filters()->fetch_by_collection($this->public_collection);
		$this->AssertEquals(3, sizeof($meta));
		$meta = tainacan_filters()->fetch_ids_by_collection($this->public_collection);
		$this->AssertEquals(3, sizeof($meta));

		$this->subscriber2->add_cap( 'tnc_rep_read_private_filters' );
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$meta = tainacan_filters()->fetch_by_collection($this->public_collection);
		$this->AssertEquals(4, sizeof($meta));
		$meta = tainacan_filters()->fetch_ids_by_collection($this->public_collection);
		$this->AssertEquals(4, sizeof($meta));


	}

	/**
	 * @group taxonomies
	 */
	function test_taxonomies_caps() {

		wp_set_current_user($this->subscriber2->ID);

		$this->assertFalse( user_can($this->subscriber2->ID, 'tnc_rep_edit_taxonomies') );

		$this->subscriber2->add_cap( 'tnc_rep_edit_taxonomies' );

		$this->assertTrue( user_can($this->subscriber2->ID, 'tnc_rep_edit_taxonomies') );

		$tax_public = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'Subscriber public Tax',
				'description'  => 'tipos de musica2',
				'allow_insert' => 'yes',
				'status' => 'publish'
			),
			true
		);

		$tax_private = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'Subscriber private Tax',
				'description'  => 'tipos de musica2',
				'allow_insert' => 'yes',
				'status' => 'private'
			),
			true
		);

		$this->assertTrue( $tax_public->can_edit() );
		$this->assertTrue( $tax_private->can_edit() );
		$this->assertFalse( $this->public_taxonomy->can_edit() );
		$this->assertFalse( $this->private_taxonomy->can_edit() );

		$this->assertTrue( $tax_public->can_delete() );
		$this->assertTrue( $tax_private->can_delete() );
		$this->assertFalse( $this->public_taxonomy->can_delete() );
		$this->assertFalse( $this->private_taxonomy->can_delete() );

		$this->assertTrue( $tax_public->can_read() );
		$this->assertTrue( $tax_private->can_read(), 'user should be able to read own private taxonomies' );
		$this->assertTrue( $this->public_taxonomy->can_read() );
		$this->assertFalse( $this->private_taxonomy->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_manage_taxonomies' );

		$this->assertTrue( $tax_public->can_edit() );
		$this->assertTrue( $tax_private->can_edit() );
		$this->assertTrue( $this->public_taxonomy->can_edit() );
		$this->assertTrue( $this->private_taxonomy->can_edit() );

		$this->assertTrue( $tax_public->can_delete() );
		$this->assertTrue( $tax_private->can_delete() );
		$this->assertTrue( $this->public_taxonomy->can_delete() );
		$this->assertTrue( $this->private_taxonomy->can_delete() );

		$this->assertTrue( $this->public_taxonomy->can_read() );
		$this->assertFalse( $this->private_taxonomy->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_read_private_taxonomies' );

		$this->assertTrue( $this->private_taxonomy->can_read() );

	}

}
