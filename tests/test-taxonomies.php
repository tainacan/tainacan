<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
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
	
}