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
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::getInstance();

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
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::getInstance();
        $Tainacan_Terms = \Tainacan\Repositories\Terms::getInstance();

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
}