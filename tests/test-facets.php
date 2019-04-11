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
		
		// test defaults 1 collection 
		
		// test default text metadata 
		
		
		
		// test default relationship 
		
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
			'collection_id' => $this->collection1->get_id(),
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
			'collection_id' => $this->collection1->get_id(),
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
			'collection_id' => $this->collection1->get_id(),
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
		
		// test default relationship with filter
		
		// test defaults 1 collection without filter
		
		
		//
		
		
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
		
		
		// test search relationship  without filter
		
		// test offset normal 
		
		
		// test include normal 
		
		
		// test count items normal 
		
		// test default taxonomy 
		// test default taxonomy without filter 
		// test search taxonomy 
		// test search taxonomy  with filter
		// test search taxonomy  without filter
		// test offset taxonomy 
		// test include taxonomy 
		// test count items taxonomy 
		
		//
		
		
		
		
	}
	

}