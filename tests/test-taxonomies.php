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
}