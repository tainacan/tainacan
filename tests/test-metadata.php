<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Test_Metadata extends WP_UnitTestCase {

    /**
     * Teste da insercao de um metadado simples sem o tipo
     */
    function test_add() {
        global $Tainacan_Collections, $Tainacan_Metadatas;

        $collection = new Tainacan_Collection();
        $metadata = new Tainacan_Metadata();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_description('descricao');
        $metadata->set_collection( $collection );

        //inserindo o metadado
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->get_metadata_by_id($metadata->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_description(), 'descricao');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());

    }

    /**
     * Teste da insercao de um metadado simples com o tipo
     */
    function teste_add_type(){
        global $Tainacan_Collections, $Tainacan_Metadatas;

        $collection = new Tainacan_Collection();
        $metadata = new Tainacan_Metadata();
        $type = new Tainacan_Text_Field_Type();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_collection_id( $collection->get_id() );
        $metadata->set_type( $type );


        //inserindo o metadado
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->get_metadata_by_id($metadata->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertEquals($test->get_type('name'), 'Tainacan_Text_Field_Type');
        $this->assertEquals($test->get_type(), $type);
    }
}