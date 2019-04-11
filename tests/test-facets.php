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
				],
				'multiple' => 'yes'
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
		
		// test default taxonomy with filter 
		
		// test default relationship with filter
		
		// test defaults 1 collection without filter
		
		
		//
		
		
		// test default text metadata without filter 
		
		// test default taxonomy without filter 
		
		// test default relationship without filter
		
		// test search
		
		
		
		// test search relationship 
		
		// test search with filter
		
		
		// test search relationship  with filter
		
		// test search without filter
		
		
		// test search relationship  without filter
		
		// test offset normal 
		
		
		// test include normal 
		
		
		// test count items normal 
		
		// test default taxonomy 
		// test search taxonomy 
		// test search taxonomy  with filter
		// test search taxonomy  without filter
		// test offset taxonomy 
		// test include taxonomy 
		// test count items taxonomy 
		
		//
		
		
		
		
	}
	

}