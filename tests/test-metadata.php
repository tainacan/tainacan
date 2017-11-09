<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class TestMetadata extends WP_UnitTestCase {

    /**
     * Teste da insercao de um metadado simples sem o tipo
     */
    function test_add() {
        global $TainacanCollections, $Tainacan_Metadatas;

        $collection = new TainacanCollection();
        $metadata = new Tainacan_Metadata();

        $collection->set_name('teste');
        $id = $TainacanCollections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->setter('name','metadado');
        $metadata->setter('description','descricao');
        $metadata->setter('collection',$id);

        //inserindo o metadado
        $metadata_id = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->get_metadata_by_id($metadata_id);

        $this->assertEquals($test->getter('name'), 'metadado');
        $this->assertEquals($test->getter('description'), 'descricao');
        $this->assertEquals($test->getter('collection'), $id);

    }
}