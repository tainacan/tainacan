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
		 * - public_collection (10 items, 5 public, 5 private)
		 * --- (Core Title adn Description)
		 * --- public_metadatum
		 * --- private_metadatum
		 * --- public_filter
		 * --- private_filter
		 * - private_collection (10 items, 5 public, 5 private)
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

		$this->term_in_public = $term_1;

		$term_2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'Term for collection 2'
			),
			true
		);

		$this->term_in_private = $term_2;

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
		$this->items = [];


		for ($i = 1; $i<=10; $i++) {

			$title = 'testeItem ' . str_pad($i, 2, "0", STR_PAD_LEFT);

			$col = $i <= 5 ? $collection1 : $collection2;
			$col_slug = $i <= 5 ? 'public_col' : 'private_col';

			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => $title,
					'collection' => $col,
					'status' => 'publish'
				),
				true
			);

			$this->items[$col_slug]['public'][] = $item;

			$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_repo, 'Value ' . $i);
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_repo2, 'Value ' . $i);

			if ($i <= 5) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_text, $i % 2 == 0 ? 'even' : 'odd');
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_text2, $i % 2 == 0 ? 'even' : 'odd');
			}

			$private_item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => 'private' . $title,
					'collection' => $col,
					'status' => 'private'
				),
				true
			);
			$this->items[$col_slug]['private'][] = $private_item;

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


		$this->subscriber2->add_cap( 'tnc_rep_edit_metadata' );
		clean_user_cache(get_current_user_id());

		$this->assertFalse( $this->public_metadatum->can_edit() );
		$this->assertTrue( $this->public_repo_metadatum->can_edit() );
		$this->assertFalse( $this->private_metadatum->can_edit() );
		$this->assertTrue( $this->private_repo_metadatum->can_edit() );
		$this->assertFalse( $this->public_metadatum->can_delete() );
		$this->assertFalse( $this->public_repo_metadatum->can_delete() );
		$this->assertFalse( $this->private_metadatum->can_delete() );
		$this->assertFalse( $this->private_repo_metadatum->can_delete() );

		$this->subscriber2->add_cap( 'tnc_rep_delete_metadata' );
		clean_user_cache(get_current_user_id());

		$this->assertFalse( $this->public_metadatum->can_edit() );
		$this->assertTrue( $this->public_repo_metadatum->can_edit() );
		$this->assertFalse( $this->private_metadatum->can_edit() );
		$this->assertTrue( $this->private_repo_metadatum->can_edit() );
		$this->assertFalse( $this->public_metadatum->can_delete() );
		$this->assertTrue( $this->public_repo_metadatum->can_delete() );
		$this->assertFalse( $this->private_metadatum->can_delete() );
		$this->assertTrue( $this->private_repo_metadatum->can_delete() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_edit_metadata' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $this->public_metadatum->can_edit() );
		$this->assertTrue( $this->public_repo_metadatum->can_edit() );
		$this->assertTrue( $this->private_metadatum->can_edit() );
		$this->assertTrue( $this->private_repo_metadatum->can_edit() );
		$this->assertFalse( $this->public_metadatum->can_delete() );
		$this->assertTrue( $this->public_repo_metadatum->can_delete() );
		$this->assertFalse( $this->private_metadatum->can_delete() );
		$this->assertTrue( $this->private_repo_metadatum->can_delete() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_delete_metadata' );
		clean_user_cache(get_current_user_id());

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
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $this->public_metadatum->can_read() );
		$this->assertTrue( $this->public_repo_metadatum->can_read() );
		$this->assertFalse( $this->private_metadatum->can_read() );
		$this->assertTrue( $this->private_repo_metadatum->can_read() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_read_private_metadata' );
		clean_user_cache(get_current_user_id());

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

		$this->subscriber2->add_cap( 'tnc_rep_edit_filters' );
		clean_user_cache(get_current_user_id());

		$this->assertFalse( $this->public_filter->can_edit() );
		$this->assertTrue( $this->public_repo_filter->can_edit() );
		$this->assertFalse( $this->private_filter->can_edit() );
		$this->assertTrue( $this->private_repo_filter->can_edit() );
		$this->assertFalse( $this->public_filter->can_delete() );
		$this->assertFalse( $this->public_repo_filter->can_delete() );
		$this->assertFalse( $this->private_filter->can_delete() );
		$this->assertFalse( $this->private_repo_filter->can_delete() );

		$this->subscriber2->add_cap( 'tnc_rep_delete_filters' );
		clean_user_cache(get_current_user_id());

		$this->assertFalse( $this->public_filter->can_edit() );
		$this->assertTrue( $this->public_repo_filter->can_edit() );
		$this->assertFalse( $this->private_filter->can_edit() );
		$this->assertTrue( $this->private_repo_filter->can_edit() );
		$this->assertFalse( $this->public_filter->can_delete() );
		$this->assertTrue( $this->public_repo_filter->can_delete() );
		$this->assertFalse( $this->private_filter->can_delete() );
		$this->assertTrue( $this->private_repo_filter->can_delete() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_edit_filters' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $this->public_filter->can_edit() );
		$this->assertTrue( $this->public_repo_filter->can_edit() );
		$this->assertTrue( $this->private_filter->can_edit() );
		$this->assertTrue( $this->private_repo_filter->can_edit() );
		$this->assertFalse( $this->public_filter->can_delete() );
		$this->assertTrue( $this->public_repo_filter->can_delete() );
		$this->assertFalse( $this->private_filter->can_delete() );
		$this->assertTrue( $this->private_repo_filter->can_delete() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_delete_filters' );
		clean_user_cache(get_current_user_id());

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
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $this->public_filter->can_read() );
		$this->assertTrue( $this->public_repo_filter->can_read() );
		$this->assertFalse( $this->private_filter->can_read() );
		$this->assertTrue( $this->private_repo_filter->can_read() );

		$this->subscriber2->add_cap( 'tnc_col_' . $this->public_collection->get_id() . '_read_private_filters' );
		clean_user_cache(get_current_user_id());

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
		clean_user_cache(get_current_user_id());

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

		$this->assertFalse( $tax_public->can_delete() );
		$this->assertFalse( $tax_private->can_delete() );
		$this->assertFalse( $this->public_taxonomy->can_delete() );
		$this->assertFalse( $this->private_taxonomy->can_delete() );

		$this->assertTrue( $tax_public->can_read() );
		$this->assertTrue( $tax_private->can_read(), 'user should be able to read own private taxonomies' );
		$this->assertTrue( $this->public_taxonomy->can_read() );
		$this->assertFalse( $this->private_taxonomy->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_edit_others_taxonomies' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $tax_public->can_edit() );
		$this->assertTrue( $tax_private->can_edit() );
		$this->assertTrue( $this->public_taxonomy->can_edit() );
		$this->assertTrue( $this->private_taxonomy->can_edit() );

		$this->assertFalse( $tax_public->can_delete() );
		$this->assertFalse( $tax_private->can_delete() );
		$this->assertFalse( $this->public_taxonomy->can_delete() );
		$this->assertFalse( $this->private_taxonomy->can_delete() );

		$this->assertTrue( $this->public_taxonomy->can_read() );
		$this->assertFalse( $this->private_taxonomy->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_read_private_taxonomies' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $this->private_taxonomy->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_delete_taxonomies' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $tax_public->can_delete() );
		$this->assertTrue( $tax_private->can_delete() );
		$this->assertFalse( $this->public_taxonomy->can_delete() );
		$this->assertFalse( $this->private_taxonomy->can_delete() );

		$this->subscriber2->add_cap( 'tnc_rep_delete_others_taxonomies' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $tax_public->can_delete() );
		$this->assertTrue( $tax_private->can_delete() );
		$this->assertTrue( $this->public_taxonomy->can_delete() );
		$this->assertTrue( $this->private_taxonomy->can_delete() );

	}

	/**
	 * @group taxonomies
	 */
	function test_terms_caps() {

		wp_set_current_user($this->subscriber2->ID);

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

		$term_1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax_public->get_db_identifier(),
				'name'     => 'Term for collection 2'
			),
			true
		);

		$term_2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax_private->get_db_identifier(),
				'name'     => 'Term for collection 2'
			),
			true
		);


		$this->assertFalse( $term_1->can_delete() );
		$this->assertFalse( $term_2->can_delete() );
		$this->assertFalse( $this->term_in_public->can_delete() );
		$this->assertFalse( $this->term_in_private->can_delete() );

		$this->assertFalse( $term_1->can_edit() );
		$this->assertFalse( $term_2->can_edit() );
		$this->assertFalse( $this->term_in_public->can_edit() );
		$this->assertFalse( $this->term_in_private->can_edit() );

		$this->assertTrue( $term_1->can_read() );
		$this->assertTrue( $term_2->can_read() );
		$this->assertTrue( $this->term_in_public->can_read() );
		$this->assertFalse( $this->term_in_private->can_read() );

		$this->subscriber2->add_cap( 'tnc_rep_edit_taxonomies' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $term_1->can_delete() );
		$this->assertTrue( $term_2->can_delete() );
		$this->assertFalse( $this->term_in_public->can_delete() );
		$this->assertFalse( $this->term_in_private->can_delete() );

		$this->assertTrue( $term_1->can_edit() );
		$this->assertTrue( $term_2->can_edit() );
		$this->assertFalse( $this->term_in_public->can_edit() );
		$this->assertFalse( $this->term_in_private->can_edit() );


		$this->subscriber2->add_cap( 'tnc_rep_edit_others_taxonomies' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $term_1->can_delete() );
		$this->assertTrue( $term_2->can_delete() );
		$this->assertTrue( $this->term_in_public->can_delete() );
		$this->assertTrue( $this->term_in_private->can_delete() );

		$this->assertTrue( $term_1->can_edit() );
		$this->assertTrue( $term_2->can_edit() );
		$this->assertTrue( $this->term_in_public->can_edit() );
		$this->assertTrue( $this->term_in_private->can_edit() );


		$this->subscriber2->add_cap( 'tnc_rep_read_private_taxonomies' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $term_1->can_read() );
		$this->assertTrue( $term_2->can_read() );
		$this->assertTrue( $this->term_in_public->can_read() );
		$this->assertTrue( $this->term_in_private->can_read() );

	}

	/**
	 * @group collections
	 */
	function test_collections_metacaps() {

		wp_set_current_user($this->subscriber2->ID);

		$this->subscriber2->add_cap( 'tnc_rep_edit_collections' );
		clean_user_cache(get_current_user_id());

		$my_collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'My Col',
				'status' => 'publish'
			),
			true
		);

		$this->assertFalse( $this->public_collection->can_edit() );
		$this->assertTrue( $this->public_collection->can_read() );
		$this->assertFalse( $this->public_collection->can_delete() );
		$this->assertFalse( $this->private_collection->can_edit() );
		$this->assertFalse( $this->private_collection->can_read() );
		$this->assertFalse( $this->private_collection->can_delete() );
		$this->assertTrue( $my_collection->can_edit() );
		$this->assertTrue( $my_collection->can_read() );
		$this->assertFalse( $my_collection->can_delete() );

		$this->subscriber2->add_cap( 'tnc_rep_delete_collections' );
		clean_user_cache(get_current_user_id());

		$this->assertFalse( $this->public_collection->can_edit() );
		$this->assertTrue( $this->public_collection->can_read() );
		$this->assertFalse( $this->public_collection->can_delete() );
		$this->assertFalse( $this->private_collection->can_edit() );
		$this->assertFalse( $this->private_collection->can_read() );
		$this->assertFalse( $this->private_collection->can_delete() );
		$this->assertTrue( $my_collection->can_edit() );
		$this->assertTrue( $my_collection->can_read() );
		$this->assertTrue( $my_collection->can_delete() );

		$this->subscriber2->add_cap( 'tnc_rep_read_private_collections' );
		clean_user_cache(get_current_user_id());

		$this->assertFalse( $this->public_collection->can_edit() );
		$this->assertTrue( $this->public_collection->can_read() );
		$this->assertFalse( $this->public_collection->can_delete() );
		$this->assertFalse( $this->private_collection->can_edit() );
		$this->assertTrue( $this->private_collection->can_read() );
		$this->assertFalse( $this->private_collection->can_delete() );

		$this->subscriber2->add_cap( 'manage_tainacan' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $this->public_collection->can_edit() );
		$this->assertTrue( $this->public_collection->can_read() );
		$this->assertTrue( $this->public_collection->can_delete() );
		$this->assertTrue( $this->private_collection->can_edit() );
		$this->assertTrue( $this->private_collection->can_read() );
		$this->assertTrue( $this->private_collection->can_delete() );


	}

	/**
	 * @group collections
	 */
	function test_fetch_collections() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$this->subscriber2->add_cap( 'tnc_rep_edit_collections' );
		clean_user_cache(get_current_user_id());

		$my_collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'My Col',
				'status' => 'publish'
			),
			true
		);


		$cols = tainacan_collections()->fetch([], 'OBJECT');
		$this->assertEquals(2, sizeof($cols));

		$this->subscriber2->add_cap( 'tnc_rep_read_private_collections' );
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$cols = tainacan_collections()->fetch([], 'OBJECT');
		$this->assertEquals(3, sizeof($cols));


	}

	/**
	 * @group collectionss
	 */
	function test_manage_collection_can_edit_collection() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$this->assertFalse( $this->public_collection->can_edit() );

		$this->subscriber2->add_cap( 'manage_tainacan_collection_' . $this->public_collection->get_id() );
		clean_user_cache(get_current_user_id());
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$this->assertTrue( $this->public_collection->can_edit() );

	}

	/**
	 * @group collections
	 */
	function test_manage_all_collections_can_edit_collection() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$this->assertFalse( $this->public_collection->can_edit() );

		$this->subscriber2->add_cap( 'manage_tainacan_collection_all' );
		clean_user_cache(get_current_user_id());
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$this->assertTrue( $this->public_collection->can_edit() );
		$this->assertTrue( $this->private_collection->can_edit() );


	}

	/**
	 * @group items
	 */
	function test_items_metacaps() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$col1prefix = 'tnc_col_' . $this->public_collection->get_id() . '_';
		$col2prefix = 'tnc_col_' . $this->private_collection->get_id() . '_';

		$public = $this->items['public_col']['public'][0];
		$public_in_private = $this->items['private_col']['public'][0];

		$private = $this->items['public_col']['private'][0];
		$private_in_private = $this->items['private_col']['private'][0];

		$this->assertTrue( $public->can_read() );
		$this->assertFalse( $public_in_private->can_read() );
		$this->assertFalse( $private->can_read() );
		$this->assertFalse( $private_in_private->can_read() );

		$this->subscriber2->add_cap( $col1prefix . 'read_private_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $public->can_read() );
		$this->assertFalse( $public_in_private->can_read() );
		$this->assertTrue( $private->can_read() );
		$this->assertFalse( $private_in_private->can_read() );

		$this->subscriber2->add_cap( $col2prefix . 'read_private_items' );

		$this->assertTrue( $public->can_read() );
		$this->assertFalse( $public_in_private->can_read() );
		$this->assertTrue( $private->can_read() );
		$this->assertFalse( $private_in_private->can_read(), 'user must also have read_private_collections to read items in private collections' );

		$this->subscriber2->add_cap( 'tnc_rep_read_private_collections' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $public->can_read() );
		$this->assertTrue( $public_in_private->can_read() );
		$this->assertTrue( $private->can_read() );
		$this->assertTrue( $private_in_private->can_read() );

		$own_item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'my item',
				'collection' => $this->public_collection,
				'status' => 'draft'
			),
			true
		);

		$this->assertFalse( $own_item->can_edit() );

		$this->subscriber2->add_cap( $col1prefix . 'edit_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $own_item->can_edit() );
		$this->assertFalse( $own_item->can_publish() );

		$this->subscriber2->add_cap( $col1prefix . 'publish_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $own_item->can_publish() );

		$this->assertFalse( $own_item->can_delete() );

		$this->subscriber2->add_cap( $col1prefix . 'delete_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $own_item->can_delete() );

		$own_item->set_status('publish');
		$own_item->validate();

		$own_item = \tainacan_items()->insert($own_item);

		$this->assertFalse( $own_item->can_edit() );

		$this->subscriber2->add_cap( $col1prefix . 'edit_published_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $own_item->can_edit() );

		$this->assertFalse( $own_item->can_delete() );

		$this->subscriber2->add_cap( $col1prefix . 'delete_published_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $own_item->can_delete() );



	}

	/**
	 * @group items
	 */
	function test_items_edit_others() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$col1prefix = 'tnc_col_' . $this->public_collection->get_id() . '_';

		$other_draft = $this->items['public_col']['public'][0];
		$other_draft->set_status('draft');
		$other_draft->validate();
		$other_draft = \tainacan_items()->insert($other_draft);

		$other_published = $this->items['public_col']['public'][1];

		$this->assertFalse( $other_draft->can_edit() );
		$this->assertFalse( $other_published->can_edit() );

		$this->subscriber2->add_cap( $col1prefix . 'edit_others_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $other_draft->can_edit() );
		$this->assertFalse( $other_published->can_edit() );

		$this->subscriber2->add_cap( $col1prefix . 'edit_published_items' );
		clean_user_cache(get_current_user_id());

		$this->assertTrue( $other_draft->can_edit() );
		$this->assertTrue( $other_published->can_edit() );
	}

	/**
	 * @group items
	 */
	function test_fetch_items() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$col1prefix = 'tnc_col_' . $this->public_collection->get_id() . '_';
		$col2prefix = 'tnc_col_' . $this->private_collection->get_id() . '_';

		$repo = tainacan_items()->fetch(['nopaging' => true], [], 'OBJECT');

		$this->assertEquals(5, sizeof($repo));

		$this->subscriber2->add_cap( $col1prefix . 'read_private_items' );
		clean_user_cache(get_current_user_id());
		$current_user = $this->subscriber2; // force update current user object with new capabilities


		$repo = tainacan_items()->fetch(['nopaging' => true], [], 'OBJECT');

		$this->assertEquals(10, sizeof($repo));

		$this->subscriber2->add_cap( 'tnc_rep_read_private_collections' );
		clean_user_cache(get_current_user_id());
		//$this->subscriber2->add_cap( 'read_private_multiple_post_types' );
		$current_user = $this->subscriber2; // force update current user object with new capabilities


		$repo = tainacan_items()->fetch(['nopaging' => true], [], 'OBJECT');


		$this->assertEquals(15, sizeof($repo));

		$this->subscriber2->add_cap( $col2prefix . 'read_private_items' );
		clean_user_cache(get_current_user_id());
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$repo = tainacan_items()->fetch(['nopaging' => true], [], 'OBJECT');

		$this->assertEquals(20, sizeof($repo));


		$col1 = tainacan_items()->fetch(['nopaging' => true], $this->public_collection, 'OBJECT');





	}

	/**
	 * @group items
	 */
	function test_fetch_collection_items() {
		global $current_user;
		wp_set_current_user($this->subscriber2->ID);

		$col1prefix = 'tnc_col_' . $this->public_collection->get_id() . '_';
		$col2prefix = 'tnc_col_' . $this->private_collection->get_id() . '_';

		$col1 = tainacan_items()->fetch(['nopaging' => true], $this->public_collection, 'OBJECT');

		$this->assertEquals(5, sizeof($col1));

		$this->subscriber2->add_cap( $col1prefix . 'read_private_items' );
		$current_user = $this->subscriber2; // force update current user object with new capabilities

		$col1 = tainacan_items()->fetch(['nopaging' => true], $this->public_collection, 'OBJECT');

		$this->assertEquals(10, sizeof($col1));


	}

	/**
	 * @group users
	 */
	public function test_admin_can_edit_user() {
		global $current_user;
		wp_set_current_user(1);

		$this->assertTrue(current_user_can('edit_users'));
		$this->assertTrue(current_user_can('tnc_rep_edit_users'));

		$admin = get_userdata(1);
		$admin->add_cap('manage_tainacan', false);
		$current_user = $admin;
		wp_set_current_user(1);

		$this->assertFalse(current_user_can('manage_tainacan'));
		$this->assertTrue(current_user_can('edit_users'));
		$this->assertTrue(current_user_can('tnc_rep_edit_users'));
	}

}
