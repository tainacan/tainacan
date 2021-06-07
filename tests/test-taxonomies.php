<?php

namespace Tainacan\Tests;

/**
 * Class Taxonomies
 *
 * @package Test_Tainacan
 */

/**
 * Taxonomies test case.
 */
class Taxonomies extends TAINACAN_UnitTestCase {

    /**
     * Teste da insercao de uma taxonomia simples
     */
    function test_add() {
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

        $taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes'
	        ),
	        true
        );

        //retorna a taxonomia
        $test = $Tainacan_Taxonomies->fetch($taxonomy->get_id());

        $this->assertEquals( $test->get_name(), 'genero' );
        $this->assertEquals( $test->get_description(), 'tipos de musica' );
        $this->assertEquals( $test->get_allow_insert(), 'yes' );
        $this->assertEquals( taxonomy_exists( $test->get_db_identifier() ) , true );
    }

    function test_add_term_taxonomy(){
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        $Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

	    $taxonomy = $this->tainacan_entity_factory->create_entity(
		    'taxonomy',
		    array(
			    'name' => 'genero',
		    ),
		    true
	    );

        //retorna a taxonomia
        $taxonomy_test = $Tainacan_Taxonomies->fetch($taxonomy->get_id());

	    $term = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy_test->get_db_identifier(),
			    'name'     => 'Rock',
			    'user'     => 56
		    ),
		    true
	    );

        //retorna um objeto da classe Tainacan_Term
        $test =  $Tainacan_Terms->fetch($term->get_term_id(), $taxonomy_test);
        $this->assertEquals( $test->get_name(), 'Rock' );
        $this->assertEquals( $test->get_user(), 56 );
    }

	function test_terms_of_draft_taxonomy() {

		$taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes',
				'status'       => 'publish'
	        ),
	        true
        );

		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$terms = $Tainacan_Terms->fetch(['hide_empty' => false], $taxonomy->get_id());

		$this->assertEquals(0, sizeof($terms), 'new auto draft taxonomy should return 0 terms');

		$term = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Rock',
		    ),
		    true
	    );



		$terms = $Tainacan_Terms->fetch(['hide_empty' => false], $taxonomy->get_id());

		$this->assertEquals(1, sizeof($terms), 'you should be able to create a term even if the taxonomy is still auto-draft');

	}

	function test_term_exists() {

		$taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes',
				'status'       => 'publish'
	        ),
	        true
        );

		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$term = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Rock',
		    ),
		    true
	    );

		$parent = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Parent',
		    ),
		    true
	    );

		$child = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Child',
				'parent'   => $parent->get_id()
		    ),
		    true
	    );

		$this->assertFalse( $Tainacan_Terms->term_exists('Reggae', $taxonomy->get_db_identifier()) );
		$this->assertTrue( $Tainacan_Terms->term_exists('Rock', $taxonomy->get_db_identifier()) );

		//var_dump( $Tainacan_Terms->term_exists('Rock', $taxonomy->get_db_identifier(), 0, true) );

		// test extreme case

		$term_2 = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'test 123',
				'parent'   => $term->get_id()
		    ),
		    true
	    );

		$this->assertFalse( $Tainacan_Terms->term_exists('test 123', $taxonomy->get_db_identifier(), 0) ); // parent 0
		$this->assertTrue( $Tainacan_Terms->term_exists('test    123', $taxonomy->get_db_identifier(), $term->get_id()) ); // spaces in between

		// testing passing taxonomy object
		$this->assertTrue( $Tainacan_Terms->term_exists('Rock', $taxonomy) );

		// testing passing taxonomy ID
		$this->assertTrue( $Tainacan_Terms->term_exists('Rock', $taxonomy->get_id()) );

		// testing via Taxonomy object
		$this->assertTrue( $taxonomy->term_exists('Rock') );

		// testing retrieving the term
		$this->assertTrue( $taxonomy->term_exists('Rock', 0, true) instanceof \WP_Term );
		$this->assertEquals( $term->get_id(), $taxonomy->term_exists('Rock', 0, true)->term_taxonomy_id );

		// test parent
		$this->assertTrue( $Tainacan_Terms->term_exists('Child', $taxonomy->get_db_identifier()) ); // parent null
		$this->assertFalse( $Tainacan_Terms->term_exists('Child', $taxonomy->get_db_identifier(), 0) ); // parent 0
		$this->assertTrue( $Tainacan_Terms->term_exists('Child', $taxonomy->get_db_identifier(), $parent->get_id()) ); // parent

		// test with ID
		$this->assertTrue( $Tainacan_Terms->term_exists($term->get_id(), $taxonomy->get_id()) );

		// test get term
		$test_term = $Tainacan_Terms->term_exists($term->get_id(), $taxonomy->get_id(), null, true);
		$this->assertEquals($term->get_id(), $test_term->term_id);

		$test_term = $Tainacan_Terms->term_exists('Rock', $taxonomy->get_id(), null, true);
		$this->assertEquals($term->get_id(), $test_term->term_id);

		$test_term = $Tainacan_Terms->term_exists('Parent', $taxonomy->get_id(), null, true);
		$this->assertEquals($parent->get_id(), $test_term->term_id);

		// test brackets
		$test_term = $Tainacan_Terms->term_exists('[Rock]', $taxonomy->get_id(), null, true);
		$this->assertFalse($test_term);

	}

	function test_term_validation() {

		$taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes',
				'status'       => 'publish'
	        ),
	        true
        );

		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$term = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Rock',
		    ),
		    true
	    );

		$parent = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Parent',
		    ),
		    true
	    );

		$child = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Child',
				'parent'   => $parent->get_id()
		    ),
		    true
	    );

		$newTerm = new \Tainacan\Entities\Term();
		$newTerm->set_name('Child');
		$newTerm->set_taxonomy($taxonomy->get_db_identifier());

		$this->assertTrue( $newTerm->validate() );

		$newTerm->set_parent($parent->get_id());

		$this->assertFalse( $newTerm->validate(), 'term should not validate because it has a duplicate in the same level' );

		$child->set_description('changed');

		$this->assertTrue( $child->validate(), 'child should validate');



	}

    /**
    * @group enabled
    */
    function test_enabled_post_types(){
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        $Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

	    $taxonomy = $this->tainacan_entity_factory->create_entity(
		    'taxonomy',
		    array(
			    'name' => 'genero',
                'enabled_post_types' => ['post']
		    ),
		    true
	    );

        $taxonomy = $Tainacan_Taxonomies->insert($taxonomy);

	    $pto = get_object_taxonomies('post');
	    $pages = get_object_taxonomies('page');
        $this->assertContains($taxonomy->get_db_identifier(), $pto);
        $this->assertNotContains($taxonomy->get_db_identifier(), $pages);
    }

	function test_brackets() {

		$taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes',
				'status'       => 'publish'
	        ),
	        true
        );

		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

		$term = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => 'Rock',
		    ),
		    true
	    );

		$term2 = $this->tainacan_entity_factory->create_entity(
		    'term',
		    array(
			    'taxonomy' => $taxonomy->get_db_identifier(),
			    'name'     => '[Rock]',
		    ),
		    true
	    );

		$terms = $Tainacan_Terms->fetch(['hide_empty' => false], $taxonomy);
		$this->assertEquals(2, sizeof($terms));

	}

	function test_brackets_2() {
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name' => 'teste',
		        'description' => 'No description',
	        ),
	        true
        );

		$taxonomy = $this->tainacan_entity_factory->create_entity(
        	'taxonomy',
	        array(
	        	'name'         => 'genero',
		        'description'  => 'tipos de musica',
		        'allow_insert' => 'yes',
				'status'       => 'publish'
	        ),
	        true
        );

	    $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name'              => 'metadado',
		        'description'       => 'descricao',
		        'collection'        => $collection,
		        'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'allow_new_terms' => 'yes',
					'taxonomy_id' => $taxonomy->get_id()
				],
	        ),
	        true
        );

        $i1 = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'       => 'item teste',
		        'description' => 'adasdasdsa',
		        'collection'  => $collection
	        ),
	        true
        );

		$i2 = $this->tainacan_entity_factory->create_entity(
        	'item',
	        array(
	        	'title'       => 'item2 teste',
		        'description' => 'adasdasdsa',
		        'collection'  => $collection
	        ),
	        true
        );

		$itemMeta1 = new \Tainacan\Entities\Item_Metadata_Entity($i1, $metadatum);
		$itemMeta1->set_value('Rock');
		$itemMeta1->validate();
		$Tainacan_Item_Metadata->insert($itemMeta1);

		//$this->assertNotFalse(term_exists( 'Rock', $taxonomy->get_db_identifier() ));
		// term_exists() is not to be trusted
		//$this->assertFalse(term_exists( '[Rock]', $taxonomy->get_db_identifier() ));

		$itemMeta2 = new \Tainacan\Entities\Item_Metadata_Entity($i2, $metadatum);
		$itemMeta2->set_value('[Rock]');
		$itemMeta2->validate();
		$Tainacan_Item_Metadata->insert($itemMeta2);

		$itemMeta1_check = new \Tainacan\Entities\Item_Metadata_Entity($i1, $metadatum);
		$this->assertEquals('Rock', $itemMeta1_check->get_value()->get_name());

		$itemMeta2_check = new \Tainacan\Entities\Item_Metadata_Entity($i2, $metadatum);
		$this->assertEquals('[Rock]', $itemMeta2_check->get_value()->get_name());
	}

	function test_metadata_taxonomy_term_count() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection_1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array( 'name'   => 'test-1', 'status' => 'publish', ),
			true
		);

		$collection_2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array( 'name'   => 'test-2', 'status' => 'publish', ),
			true
		);

		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test',
				'status' => 'publish',
				//'enabled_post_types' => [$collection_1->get_db_identifier(), $collection_2->get_db_identifier()]
			),
			true
		);

		$tax_repository = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test_repository',
				'status' => 'publish'
			),
			true
		);

		$t1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax->get_db_identifier(),
				'name'     => 'term',
				'user'     => get_current_user_id(),
			),
			true
		);

		$t2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax_repository->get_db_identifier(),
				'name'     => 'term_repository'
			),
			true
		);

		$metadatum_1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta-1',
				'description' => 'description-1',
				'collection' => $collection_1,
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
					'allow_new_terms' => 'no'
				]
			),
			true
		);

		$metadatum_2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta-2',
				'description' => 'description-2',
				'collection' => $collection_2,
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
					'allow_new_terms' => 'no'
				]
			),
			true
		);

		$metadatum_repository = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta-1',
				'description' => 'description-1',
				'collection_id' => $Tainacan_Metadata->get_default_metadata_attribute(),
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax_repository->get_id(),
					'allow_new_terms' => 'no'
				]
			),
			true
		);

		$i1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'adasdasdsa',
				'collection'  => $collection_1,
				'status' => 'publish'
			),
			true
		);
		$itemMeta1 = new \Tainacan\Entities\Item_Metadata_Entity($i1, $metadatum_1);
		$itemMeta1->set_value('term');
		$itemMeta1->validate();
		$Tainacan_Item_Metadata->insert($itemMeta1);

		$itemMeta1_repo = new \Tainacan\Entities\Item_Metadata_Entity($i1, $metadatum_repository);
		$itemMeta1_repo->set_value('term_repository');
		$itemMeta1_repo->validate();
		$Tainacan_Item_Metadata->insert($itemMeta1_repo);

		$i2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'adasdasdsa',
				'collection'  => $collection_2,
				'status' => 'private'
			),
			true
		);
		$itemMeta2 = new \Tainacan\Entities\Item_Metadata_Entity($i2, $metadatum_2);
		$itemMeta2->set_value('term');
		$itemMeta2->validate();
		$Tainacan_Item_Metadata->insert($itemMeta2);

		$itemMeta2_repo = new \Tainacan\Entities\Item_Metadata_Entity($i2, $metadatum_repository);
		$itemMeta2_repo->set_value('term_repository');
		$itemMeta2_repo->validate();
		$Tainacan_Item_Metadata->insert($itemMeta2_repo);

		$terms = get_terms([
			'taxonomy' => $tax->get_db_identifier(),
			//'hide_empty' => false,
		]);
		$this->assertEquals(1, count($terms));

		wp_update_term_count($t1->get_term_id(), $tax->get_db_identifier());
		wp_update_term_count($t2->get_term_id(), $tax_repository->get_db_identifier());

		$term = get_term($t1->get_term_id());
		$term_repo = get_term($t2->get_term_id());
		$tax_used = get_object_taxonomies( [$collection_1->get_db_identifier(), $collection_2->get_db_identifier()]);

		$tax = get_taxonomy($tax->get_db_identifier());
		$tax_repository = get_taxonomy($tax_repository->get_db_identifier());

		$this->assertContains($collection_1->get_db_identifier(), $tax->object_type);
		$this->assertContains($collection_2->get_db_identifier(), $tax->object_type);

		$this->assertContains($collection_1->get_db_identifier(), $tax_repository->object_type);
		$this->assertContains($collection_2->get_db_identifier(), $tax_repository->object_type);

		$this->assertEquals(2, $term->count);
		$this->assertEquals(2, $term_repo->count);
	}

	function test_term_taxonomy_filtered_by_collections() {
		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'         => 'Tax',
				'description'  => 'Tax',
				'allow_insert' => 'no',
				'status'       => 'publish'
			),
			true
		);

		//retorna a taxonomia
		$tax = $Tainacan_Taxonomies->fetch($tax->get_id());

		$term_1 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax->get_db_identifier(),
				'name'     => 'preto',
				'user'     => get_current_user_id(),
			),
			true
		);

		$term_2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax->get_db_identifier(),
				'name'     => 'branco',
				'user'     => get_current_user_id(),
			),
			true
		);

		$collectionOnly = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testA',
			),
			true
		);

		$collectionAll = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testB',
			),
			true
		);
		
		$metadatumOnly = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collectionOnly,
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
					'allow_new_terms' => 'no',
					'link_filtered_by_collections' => [$collectionOnly->get_id()]
				]
			),
			true
		);

		$metadatumAll = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'meta',
				'description' => 'description',
				'collection' => $collectionAll,
				'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
				'status'	 => 'publish',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
					'allow_new_terms' => 'no'
				]
			),
			true
		);

		$itemOnly = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'description',
				'collection'  => $collectionOnly,
				'status'      => 'publish'
			),
			true
		);

		$itemAll = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'description',
				'collection'  => $collectionAll,
				'status'      => 'publish'
			),
			true
		);

		$item_metadata_only = $this->tainacan_item_metadata_factory->create_item_metadata($itemOnly, $metadatumOnly, $term_1); 
		$item_metadata_all = $this->tainacan_item_metadata_factory->create_item_metadata($itemAll, $metadatumAll, $term_2); 
				
		$expected_response = get_term_link($term_2->get_id());
		$text = $item_metadata_all->get_value_as_html();
		preg_match_all('~<a(.*?)href=(\'|")([^"]+)(\'|")(.*?)>~', $text, $matches);
		$response = $matches[3][0];
		$this->assertEquals($response, $expected_response);

		$meta_query = [
			'metaquery' => [
				[
					'key' => 'collection_id',
					'compare' => 'IN',
					'value' => [$collectionOnly->get_id()]
				]
			]
		];
		$expected_response = get_term_link($term_1->get_id()) . '?' . http_build_query( $meta_query );
		$text = $item_metadata_only->get_value_as_html();
		preg_match_all('~<a(.*?)href=(\'|")([^"]+)(\'|")(.*?)>~', $text, $matches);
		$response = $matches[3][0];
		$this->assertEquals($response, $expected_response);
	}

}
