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
class Facets extends TAINACAN_UnitApiTestCase {

	public $items_ids = [];

	function setUp() {
		parent::setUp();
		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);
		$this->collection1 = $collection1;
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);
		$this->collection2 = $collection2;
		
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
		
		$this->taxonomy = $taxonomy;
		
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
		
		$this->taxonomy = $taxonomy;
		
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
		$term_1_c = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Term for collection 1 child',
				'parent' => $term_1->get_id()
			),
			true
		);
		$term_2_c = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Term for collection 2 child',
				'parent' => $term_2->get_id()
			),
			true
		);
		$term_all_c = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Term for all child',
				'parent' => $term_all->get_id()
			),
			true
		);
		
		$term_nobody = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Term for nobody',
			),
			true
		);
		
		$term2_root = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'Root'
			),
			true
		);
		$term2_root2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'Root2'
			),
			true
		);
		$term2_root_c1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'Children',
				'parent' => $term2_root->get_id()
			),
			true
		);
		$term2_root_c2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'Children2',
				'parent' => $term2_root->get_id()
			),
			true
		);
		$term2_root_gc1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'GChildren',
				'parent' => $term2_root_c2->get_id()
			),
			true
		);
		$term2_root_gc2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'GChildren2',
				'parent' => $term2_root_c2->get_id()
			),
			true
		);
		$term2_root2_c1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'Children',
				'parent' => $term2_root2->get_id()
			),
			true
		);
		$term2_root2_c2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'Children2',
				'parent' => $term2_root2->get_id()
			),
			true
		);
		$term2_root2_gc1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'GChildren',
				'parent' => $term2_root2_c2->get_id()
			),
			true
		);
		$term2_root2_gc2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'GChildren2',
				'parent' => $term2_root2_c2->get_id()
			),
			true
		);
		$term2_root2_ggc1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy2->get_db_identifier(),
				'name'     => 'GGChildren1',
				'parent' => $term2_root2_gc2->get_id()
			),
			true
		);
		
		$meta_1_tax = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'category1',
			    'status' => 'publish',
			    'collection' => $collection1,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => true,
					'taxonomy_id' => $taxonomy->get_id()
				],
				'multiple' => 'yes'
		    ),
		    true
	    );
		
		$this->meta_1_tax = $meta_1_tax;
		
		
		$meta_2_tax = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'category2',
			    'status' => 'publish',
			    'collection' => $collection2,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => true,
					'taxonomy_id' => $taxonomy->get_id()
				],
				'multiple' => 'yes'
		    ),
		    true
	    );
		
		$this->meta_2_tax = $meta_2_tax;
		
		$meta_3_tax = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'test taxonomy',
			    'status' => 'publish',
			    'collection' => $collection2,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => true,
					'taxonomy_id' => $taxonomy2->get_id()
				],
				'multiple' => 'yes'
		    ),
		    true
	    );
		
		$this->meta_3_tax = $meta_3_tax;
		
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
		
		$this->metadatum_text = $metadatum_text;
		
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
		
		$this->metadatum_repo = $metadatum_repo;
		
		$meta_relationship = $this->tainacan_entity_factory->create_entity(
		    'metadatum',
		    array(
			    'name'   => 'relationship',
			    'status' => 'publish',
			    'collection' => $collection2,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'metadata_type_options' => [
					'allow_new_terms' => true,
					'collection_id' => $collection1->get_id(),
					'search' => []
				]
		    ),
		    true
	    );
		
		$this->meta_relationship = $meta_relationship;


		for ($i = 1; $i<=80; $i++) {
			
			$title = 'testeItem ' . str_pad($i, 2, "0", STR_PAD_LEFT); 
			
			$col = $i <= 40 ? $collection1 : $collection2;
			
			$item = $this->tainacan_entity_factory->create_entity(
				'item',
				array(
					'title'      => $title,
					'collection' => $col,
					'status' => 'publish'
				),
				true
			);
			
			$this->items_ids[] = $item->get_id();
			
			$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_repo, 'Value ' . $i);
			
			if ($i <= 40) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $metadatum_text, $i % 2 == 0 ? 'even' : 'odd');
			} else {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_relationship, $this->items_ids[$i - 41]);
			}
			
			if ($i <= 10) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_1_tax, [$term_1->get_id()]);
			} elseif($i <= 20) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_1_tax, [$term_1_c->get_id()]);
			} elseif($i <= 30) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_1_tax, [$term_all->get_id()]);
			} elseif($i <= 40) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_1_tax, [$term_all_c->get_id()]);
			} elseif($i <= 50) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_2_tax, [$term_all->get_id()]);
			} elseif($i <= 60) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_2_tax, [$term_all_c->get_id()]);
			} elseif($i <= 70) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_2_tax, [$term_2->get_id()]);
			} elseif($i <= 80) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_2_tax, [$term_2_c->get_id()]);
			}
			
			// hierarchical taxonomy 
			if ($i <= 10) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_3_tax, [$term2_root_c1->get_id()]);
			} elseif($i <= 20) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_3_tax, [$term2_root2_c1->get_id()]);
			} elseif($i <= 30) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_3_tax, [$term2_root2_gc2->get_id()]);
			} elseif($i <= 40) {
				$this->tainacan_item_metadata_factory->create_item_metadata($item, $meta_3_tax, [$term2_root2_ggc1->get_id()]);
			} 
		}
		
		$this->repository = \Tainacan\Repositories\Metadata::get_instance();

		
	}
	
	// function test_setup() {
	// 	$this->assertEquals(80, sizeof($this->items_ids));
	// }
	
	/**
	* get the values from the method response (useful for refactoring)
	*/
	private function get_values($response) {
		return $response['values'];
	}
	
	function test_defaults_values() {
		
		
		
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id() );
		
		//$items = \Tainacan\Repositories\Items::get_instance()->fetch( [], null );
		
		// var_dump($items->request);
		// var_dump($items->found_posts);
		
		//var_dump($values);
		// 
		// $metas = ($items[0]->get_metadata());
		// 
		// foreach($metas as $m) {
		// 	var_dump( $m->get_metadatum()->get_id() );
		// 	var_dump( $m->get_metadatum()->get_name() );
		// 	var_dump( $m->get_value() );
		// }
		// 
		// $metas = ($items[39]->get_metadata());
		// 
		// foreach($metas as $m) {
		// 	var_dump( $m->get_metadatum()->get_id() );
		// 	var_dump( $m->get_metadatum()->get_name() );
		// 	var_dump( $m->get_value() );
		// }
		// 
		// var_dump($this->metadatum_repo->get_id());
		// 
		$values = $this->get_values($values);
		
		$this->assertEquals( 80, sizeof($values) );
		
		// test defaults with filter 
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_1_tax->get_id(), [
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->metadatum_repo->get_id(),
						'value' => ['Value 1', 'Value 10', 'Value 20', 'Value 30', 'Value 40', 'Value 50', 'Value 60', 'Value 70', 'Value 80']
					]
				]
			]
		]);
		$values = $this->get_values($values);
		$this->assertEquals( 3, sizeof($values) );

		// test defaults 1 collection with filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_1_tax->get_id(), [
			'collection_id' => $this->collection1->get_id(),
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->metadatum_repo->get_id(),
						'value' => ['Value 1', 'Value 10', 'Value 20', 'Value 30', 'Value 40', 'Value 50', 'Value 60', 'Value 70', 'Value 80']
					]
				]
			]
		]);
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );
		
		// test defaults 1 collection 
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_1_tax->get_id(), [
			'collection_id' => $this->collection1->get_id()
		]);
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );
		
		// test default text metadata 
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_text->get_id());
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );

		// test default relationship 
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id());
		$values = $this->get_values($values);
		$this->assertEquals( 40, sizeof($values) );

		// test default metadatum repo 
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id());
		$values = $this->get_values($values);
		$this->assertEquals( 80, sizeof($values) );

		// test defaults 1 collection with filter
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'collection_id' => $this->collection1->get_id(),
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->metadatum_text->get_id(),
						'value' => 'odd'
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 20, sizeof($values) );

		// test default text metadata with filter 
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_text->get_id(), [
			'count_items' => true,
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->metadatum_repo->get_id(),
						'value' => ['Value 2', 'Value 4', 'Value 6', 'Value 8', 'Value 1', 'Value 3', 'Value 80', 'Value 78', 'Value 79']
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );
		$this->assertEquals( 4, $values[0]['total_items']);
		$this->assertEquals( 2, $values[1]['total_items']);

		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_text->get_id(), [
			'count_items' => true,
			'items_filter' => [
				'tax_query' => [
					[
						'taxonomy'  => $this->taxonomy->get_db_identifier(),
						'field' 		=> 'name',
						'terms'     => 'Term for collection 1'
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );
		$this->assertEquals(10, $values[0]['total_items']);
		$this->assertEquals(10, $values[1]['total_items']);

		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_text->get_id(), [
			'count_items' => true,
			'items_filter' => [
				'tax_query' => [
					[
						'taxonomy'  => $this->taxonomy->get_db_identifier(),
						'field' 		=> 'name',
						'terms'     => 'Term for collection 1 child'
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );
		$this->assertEquals(5, $values[0]['total_items']);
		$this->assertEquals(5, $values[1]['total_items']);
		
		// test default taxonomy with filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_2_tax->get_id(),[
			//'collection_id' => $this->collection2->get_id(),
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->meta_relationship->get_id(),
						'value' => [$this->items_ids[1], $this->items_ids[10], $this->items_ids[20], $this->items_ids[30], $this->items_ids[40]]
					]
				]
			]
		]);
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );
		
		// test default relationship with filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			//'collection_id' => $this->collection2->get_id(),
			'items_filter' => [
				'tax_query' => [
					[
						'taxonomy'  => $this->taxonomy->get_db_identifier(),
						'field' 		=> 'name',
						'terms'     => ['Term for collection 2','Term for collection 2 child', 'Term for all child']
					]
				]
			]
		]);
		$values = $this->get_values($values);
		$this->assertEquals( 30, sizeof($values) );

		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			//'collection_id' => $this->collection2->get_id(),
			'items_filter' => [
				'tax_query' => [
					[
						'taxonomy'  => $this->taxonomy->get_db_identifier(),
						'field' 		=> 'name',
						'terms'     => ['Term for collection 2','Term for collection 2 child', 'Term for all child']
					]
				],
				'meta_query' => [
					[
						'key' => $this->metadatum_repo->get_id(),
						'value' => ['Value 80', 'Value 75', 'Value 70', 'Value 65', 'Value 60','Value 55','Value 50','Value 45','Value 40']
					]
				]
			]
		]);
		$values = $this->get_values($values);
		$this->assertEquals( 6, sizeof($values) );
		
		// test defaults 1 collection without filter
		// test default text metadata without filter 
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_text->get_id(), [
			'collection_id' => $this->collection1->get_id(),
			'items_filter' => false
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 2, sizeof($values) );
		
		
		// test default relationship without filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			'collection_id' => $this->collection2->get_id(),
			'items_filter' => false
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 40, sizeof($values) );
		
		// test search
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'search' => '23'
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 1, sizeof($values) );
		$this->assertEquals( 'Value 23', $values[0]['value']);
		
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'search' => '2'
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 17, sizeof($values) ); // 2, 12, 20, 21, 22, 23 ... 32, 42...
		
		// test search relationship 
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			'collection_id' => $this->collection2->get_id(),
			'search' => '23'
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 1, sizeof($values) );
		$this->assertEquals( 'testeItem 23', $values[0]['label']);
		
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			'collection_id' => $this->collection2->get_id(),
			'search' => '66'
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 0, sizeof($values) );
		
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			'collection_id' => $this->collection2->get_id(),
			'search' => '2'
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 13, sizeof($values) ); // 2, 12, 20, 21, 22, 23 ... 32
		
		// test search with filter
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'search' => '2',
			'items_filter' => [
				'tax_query' => [
					[
						'taxonomy' => $this->taxonomy->get_db_identifier(),
						'field' => 'name',
						'terms' => ['Term for all child', 'Term for collection 1 child', 'Term for collection 2 child']
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 5, sizeof($values) ); // 12, 20, 32, 52, 72
		$valuesParsed = array_map(function($el) {
			return $el['label'];
		}, $values);
		$this->assertContains( 'Value 12', $valuesParsed); 
		$this->assertContains( 'Value 20', $valuesParsed); 
		$this->assertContains( 'Value 32', $valuesParsed); 
		$this->assertContains( 'Value 52', $valuesParsed); 
		$this->assertContains( 'Value 72', $valuesParsed); 
		
		// test search relationship  with filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			'search' => '2',
			'items_filter' => [
				'tax_query' => [
					[
						'taxonomy' => $this->taxonomy->get_db_identifier(),
						'field' => 'name',
						'terms' => ['Term for all child', 'Term for collection 2 child']
					]
				]
			]
		] );
		$values = $this->get_values($values);
		// items in the range of 51-60 and 71-80 have the queried terms and are related to items in the firts collection with the number -40
		$this->assertEquals( 3, sizeof($values) ); // 12, 20, 32
		$valuesParsed = array_map(function($el) {
			return $el['label'];
		}, $values);
		$this->assertContains( 'testeItem 12', $valuesParsed); 
		$this->assertContains( 'testeItem 20', $valuesParsed); 
		$this->assertContains( 'testeItem 32', $valuesParsed);
		
		// test search without filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			'search' => '2',
			'items_filter' => false
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 13, sizeof($values) ); // 2,12,20-29,32

		$values = $this->repository->fetch_all_metadatum_values( $this->meta_2_tax->get_id(), [
			'count_items' => true,
			'search' => 'child',
			'items_filter' => false,
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 3, sizeof($values) );

		$valuesParsed = array_map(function($el) {
			//$this->assertEquals( 10, $el['total_items'] );
			$this->assertContains($el['total_items'], [10,20]);
			return $el['label'];
		}, $values);

		$this->assertContains( 'Term for collection 2 child', $valuesParsed);
		$this->assertContains( 'Term for collection 1 child', $valuesParsed);
		$this->assertContains( 'Term for all child', $valuesParsed);
		
		// test search relationship  without filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_relationship->get_id(), [
			'search' => 'testeItem 1',
			'items_filter' => false
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 10, sizeof($values) );
		
		// test offset normal
		$values_p1 = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'number' => 10,
			'offset' => 0,
			'items_filter' => false
		] );
		$values_p1 = $this->get_values($values_p1);
		$values_p2 = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'number' => 10,
			'offset' => 10,
			'items_filter' => false
		] );
		$values_p2 = $this->get_values($values_p2);
		$values_p3 = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'number' => 10,
			'offset' => 20,
			'items_filter' => false
		] );
		$values_p3 = $this->get_values($values_p3);
		
		$this->assertEquals($values_p1[9]['label'], 'Value 18');
		$this->assertEquals($values_p2[9]['label'], 'Value 27');
		$this->assertEquals($values_p3[9]['label'], 'Value 36');
		
		// test include normal
		$values = $this->repository->fetch_all_metadatum_values( $this->metadatum_repo->get_id(), [
			'include' => ['Value 1','Value 2','Value 55','Value 77'],
			'number' => 10,
			'offset' => 0,
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->metadatum_text->get_id(),
						'value' => ['even']
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$valuesParsed = array_map(function($el) {
			return $el['label'];
		}, $values);
		$this->assertContains( 'Value 1', $valuesParsed);
		$this->assertContains( 'Value 2', $valuesParsed);
		$this->assertContains( 'Value 55', $valuesParsed);
		$this->assertContains( 'Value 77', $valuesParsed);
		
		// test count items normal
		
		// test default taxonomy
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id());
		$values = $this->get_values($values);
		$this->assertEquals(2, sizeof($values));
		
		// test default taxonomy without filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false
		]);
		$values = $this->get_values($values);
		$this->assertEquals(2, sizeof($values));

		//test search taxonomy
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false,
			'search' => 't2',
		]);
		$values = $this->get_values($values);
		$this->assertEquals(1, sizeof($values));

		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false,
			'search' => 'Children',
		]);
		$values = $this->get_values($values);
		$this->assertEquals(9, sizeof($values));

		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false,
			'search' => 'GGC',
		]);
		$values = $this->get_values($values);
		$this->assertEquals(1, sizeof($values));

		// test search taxonomy  with filter
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'count_items' => true,
			'search' => 'GGC',
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->metadatum_text->get_id(),
						'value' => ['even']
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$this->assertEquals( 1, sizeof($values) );
		$this->assertEquals( 5, $values[0]['total_items']);

		// test offset taxonomy 
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false,
			'search' => 'Children',
			'number' => 9,
			'offset' => 0
		]);
		$values = $this->get_values($values);
		$this->assertEquals(9, sizeof($values));

		$values_p1 = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false,
			'search' => 'Children',
			'number' => 3,
			'offset' => 0
		]);
		$values_p1 = $this->get_values($values_p1);
		$this->assertEquals(3, sizeof($values_p1));

		$values_p2 = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false,
			'search' => 'Children',
			'number' => 3,
			'offset' => 3
		]);
		$values_p2 = $this->get_values($values_p2);
		$this->assertEquals(3, sizeof($values_p2));

		$values_p3 = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'items_filter' => false,
			'search' => 'Children',
			'number' => 3,
			'offset' => 6
		]);
		$values_p3 = $this->get_values($values_p3);
		$this->assertEquals(3, sizeof($values_p3));

		$this->assertEquals($values[0]['label'], $values_p1[0]['label']);
		$this->assertEquals($values[3]['label'], $values_p2[0]['label']);
		$this->assertEquals($values[6]['label'], $values_p3[0]['label']);
		
		// test include taxonomy 
		$values = $this->repository->fetch_all_metadatum_values( $this->meta_3_tax->get_id(), [
			'count_items' => true,
			'include' => ['18','16'],
			'search' => 'GGC',
			'items_filter' => [
				'meta_query' => [
					[
						'key' => $this->metadatum_text->get_id(),
						'value' => ['even']
					]
				]
			]
		] );
		$values = $this->get_values($values);
		$this->assertEquals(3, sizeof($values));


		// test search taxonomy  without filter
		// test count items taxonomy 
		
		
	}
	
	/**
	* @group term_tree
	*/
	public function test_process_term_tree() {
		
		$data = [
			(object) [
				'term_id' => 1,
				'name' => 'Root',
				'parent' => 0,
				'have_items' => 0
			],
			(object) [
				'term_id' => 2,
				'name' => 'Child 1',
				'parent' => 1,
				'have_items' => 0
			],
			(object) [
				'term_id' => 3,
				'name' => 'G Child 1',
				'parent' => 2,
				'have_items' => 0
			],
			(object) [
				'term_id' => 4,
				'name' => 'G G Child',
				'parent' => 3,
				'have_items' => 0
			],
			(object) [
				'term_id' => 5,
				'name' => 'G G Child 2',
				'parent' => 3,
				'have_items' => 0
			],
			(object) [
				'term_id' => 6,
				'name' => 'G G G Child',
				'parent' => 4,
				'have_items' => 0
			],
			(object) [
				'term_id' => 7,
				'name' => 'G G G Child 2',
				'parent' => 5,
				'have_items' => 0
			],
			(object) [
				'term_id' => 8,
				'name' => '2 Root',
				'parent' => 0,
				'have_items' => 0
			],
			(object) [
				'term_id' => 9,
				'name' => '2 Child',
				'parent' => 8,
				'have_items' => 0
			],
			(object) [
				'term_id' => 10,
				'name' => '2 Child 2',
				'parent' => 8,
				'have_items' => 0
			],
			(object) [
				'term_id' => 11,
				'name' => '2 G Child',
				'parent' => 10,
				'have_items' => 0
			],
			(object) [
				'term_id' => 12,
				'name' => '2 G Child 2',
				'parent' => 10,
				'have_items' => 0
			]
		];
		
		$data_b = $data;
		
		
		$MetaRepo = \Tainacan\Repositories\Metadata::get_instance();
		
		
		// items on 5 and 12 
		$data[4]->have_items = 1;
		$data[11]->have_items = 1;
		$i = 0;
		while ($i<100) {
			$i++;
			
			shuffle($data);
			
			$results = $MetaRepo->_process_terms_tree($data, 0);
			
			$this->assertEquals(2, count($results));
			$ids = array_map(function($el) {return $el->term_id; }, $results);
			$this->assertContains(1, $ids);
			$this->assertContains(8, $ids);
			
			
			$results = $MetaRepo->_process_terms_tree($data, 3);
			
			$this->assertEquals(1, count($results));
			$ids = array_map(function($el) {return $el->term_id; }, $results);
			$this->assertContains(5, $ids);
			
			$results = $MetaRepo->_process_terms_tree($data, 5);
			$this->assertEquals(0, count($results));
			
			$results = $MetaRepo->_process_terms_tree($data, 10);
			$this->assertEquals(1, count($results));			
			$this->assertEquals(12, $results[0]->term_id);
			
		}
		
		// items on 6, 7 and 8 
		$data = $data_b;
		$data[4]->have_items = 0;
		$data[11]->have_items = 0;
		$data[7]->have_items = 1;
		$data[6]->have_items = 1;
		$data[5]->have_items = 1;
		
		$i = 0;
		while ($i<100) {
			$i++;
			
			shuffle($data);
			
			$results = $MetaRepo->_process_terms_tree($data, 0);
			
			$this->assertEquals(2, count($results));
			$ids = array_map(function($el) {return $el->term_id; }, $results);
			$this->assertContains(1, $ids);
			$this->assertContains(8, $ids);
			
			
			$results = $MetaRepo->_process_terms_tree($data, 3);
			$this->assertEquals(2, count($results));
			$ids = array_map(function($el) {return $el->term_id; }, $results);
			$this->assertContains(5, $ids);
			$this->assertContains(4, $ids);
			
			$results = $MetaRepo->_process_terms_tree($data, 5);
			$this->assertEquals(1, count($results));
			$ids = array_map(function($el) {return $el->term_id; }, $results);
			$this->assertContains(7, $ids);
			
			$results = $MetaRepo->_process_terms_tree($data, 10);
			$this->assertEquals(0, count($results));
			
			$results = $MetaRepo->_process_terms_tree($data, 6);
			$this->assertEquals(0, count($results));
			
		}
		
		
	}

	/**
	* @group term_tree
	*/
	public function test_process_term_tree_naria() {
		$MetaRepo = \Tainacan\Repositories\Metadata::get_instance();
		
		$nchildrens = 3;
		$h = 6;
		$data = $this->generate_narias_tree_test($nchildrens, $h);
		$this->set_items_in_tree($data, $nchildrens, $h, [1,3], 1);
		//var_dump($data);
		$start = microtime(true);
		$results = $MetaRepo->_process_terms_tree($data, 0);
		$time = microtime(true) - $start;

		$this->assertEquals(2, count($results));
		$ids = array_map(function($el) {return $el->term_id; }, $results);
		$this->assertContains(1, $ids);
		$this->assertContains(3, $ids);
	}

	private function set_items_in_tree($data, $nchildrens, $h, $parents=[], $items_repeat=1) {
		if (empty($parents) || $nchildrens < 2 || $h < 1)
			return $data;

		foreach ($parents as $parent) {
			for($i=0; $i < $items_repeat; $i++) {
				$rando_h = rand (1, $h);
				$id = $parent;
				for ($count=0; $count < $rando_h; $count++ ) {
					$rando_c = rand (1, $nchildrens) - 1;
					$idx = ($id * $nchildrens) + $rando_c;
					if($idx > count($data)-1) {
						$idx = ($parent * $nchildrens) + $rando_c;
					}
					$id = $data[$idx]->term_id;
				}
				$data[$idx]->have_items = 1;
			}
		}
	}

	/*
	* generate n-árias trees
	*
	* $nchildrens ||  $h=2 | $h=4 | $h=8  | $h=9
	*           2 ||    3  |  15  |  255  |  511
	*           3 ||    4  |  40  |  3280 |  9841
	*           4 ||    5  |  85  |  21845|  87381
	*           5 ||    6  |  156 |  97656|  488281
	*/
	private function generate_narias_tree_test($nchildrens, $h) {
		if ($nchildrens < 2 || $h < 2)
			return [];

		$n = (pow($nchildrens, $h) - 1) / ($nchildrens - 1);
		$data = [];
		for ($i = 0; $i < $n-1; $i++) {
			$id = $i+1;
			$parent = floor($i/$nchildrens);
			$data[] = (object) [
				'term_id' => $id,
				'name'		=> "i-$id",
				'parent'	=> $parent,
				'have_items' => 0
			];
		}
		return $data;
	}
}