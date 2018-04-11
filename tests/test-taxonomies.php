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
}