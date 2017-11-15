<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Test_Taxonomies extends WP_UnitTestCase {


    /**
     * Teste da insercao de uma taxonomia simples
     */
    function test_add() {
        global $Tainacan_Taxonomies;

        $taxonomy = new \Tainacan\Entities\Taxonomy();

        //setando os valores na classe do tainacan
        $taxonomy->set_name('genero');
        $taxonomy->set_description('tipos de musica');
        $taxonomy->set_allow_insert(true);

        //inserindo
        $taxonomy = $Tainacan_Taxonomies->insert($taxonomy);

        //retorna a taxonomia
        $test = $Tainacan_Taxonomies->get_taxonomy_by_id($taxonomy->get_id());

        $this->assertEquals( $test->get_name(), 'genero' );
        $this->assertEquals( $test->get_description(), 'tipos de musica' );
        $this->assertEquals( $test->get_allow_insert(), true );
        $this->assertEquals( taxonomy_exists( $test->get_db_identifier() ) , true );
    }

    function test_add_term_taxonomy(){
        global $Tainacan_Taxonomies, $Tainacan_Terms;
        $taxonomy = new \Tainacan\Entities\Taxonomy();
        $term = new \Tainacan\Entities\Term();

        //setando os valores na classe de taxonomia
        $taxonomy->set_name('genero');

        //insere a taxonomia
        $taxonomy = $Tainacan_Taxonomies->insert($taxonomy);

        //retorna a taxonomia
        $taxonomy_test = $Tainacan_Taxonomies->get_taxonomy_by_id($taxonomy->get_id());

        //insere um termo na taxonomia
        $term->set_taxonomy( $taxonomy_test->get_db_identifier() );
        $term->set_name('Rock');
        $term->set_user(56);
        $term_id = $Tainacan_Terms->insert( $term ) ;

        //retorna um objeto da classe Tainacan_Term
        $test =  $Tainacan_Terms->get_term_by('id', $term_id, $taxonomy_test->get_db_identifier());

        $this->assertEquals( $test->get_name(), 'Rock' );
        $this->assertEquals( $test->get_user(), 56 );
    }
}