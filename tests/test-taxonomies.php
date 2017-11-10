<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class TestTaxonomies extends WP_UnitTestCase {


    /**
     * Teste da insercao de uma taxonomia simples
     */
    function test_add() {
        global $Tainacan_Taxonomies;

        $taxonomy = new Tainacan_Taxonomy();

        //setando os valores na classe do tainacan
        $taxonomy->set_name('genero');
        $taxonomy->set_description('tipos de musica');
        $taxonomy->set_allow_insert(true);

        //inserindo
        $taxonomy_id = $Tainacan_Taxonomies->insert($taxonomy);

        //retorna a taxonomia
        $test = $Tainacan_Taxonomies->get_taxonomy_by_id($taxonomy_id);

        $this->assertEquals( $test->get_name(), 'genero' );
        $this->assertEquals( $test->get_description(), 'tipos de musica' );
        $this->assertEquals( $test->get_allow_insert(), true );
        $this->assertEquals( taxonomy_exists( $test->get_taxonomy_name() ) , true );
    }

    function test_add_term_taxonomy(){
        global $Tainacan_Taxonomies,$Tainacan_Terms;
        $taxonomy = new Tainacan_Taxonomy();
        $term = new Tainacan_Term();

        //setando os valores na classe de taxonomia
        $taxonomy->set_name('genero');

        //insere a taxonomia
        $taxonomy_id = $Tainacan_Taxonomies->insert($taxonomy);

        //retorna a taxonomia
        $taxonomy_test = $Tainacan_Taxonomies->get_taxonomy_by_id($taxonomy_id);

        //insere um termo na taxonomia
        $term->set_taxonomy( $taxonomy_test->get_taxonomy_name() );
        $term->set_name('Rock');
        $term->set_user(56);
        $term_id = $Tainacan_Terms->insert( $term ) ;

        //retorna um objeto da classe Tainacan_Term
        $test =  $Tainacan_Terms->get_term_by('id', $term_id, $taxonomy_test->get_taxonomy_name());

        $this->assertEquals( $test->get_name(), 'Rock' );
        $this->assertEquals( $test->get_user(), 56 );
    }
}