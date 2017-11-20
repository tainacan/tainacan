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
class Metadata extends \WP_UnitTestCase {

    /**
     * Teste da insercao de um metadado simples sem o tipo
     */
    function test_add() {
        global $Tainacan_Collections, $Tainacan_Metadatas;

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();

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

        $collection = new \Tainacan\Entities\Collection();
        $metadata = new \Tainacan\Entities\Metadata();
        $type = new \Tainacan\Field_Types\Text();

        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);

        //setando os valores na classe do metadado
        $metadata->set_name('metadado');
        $metadata->set_collection_id( $collection->get_id() );
        $metadata->set_field_type_object( $type );


        //inserindo o metadado
        $metadata = $Tainacan_Metadatas->insert($metadata);

        $test = $Tainacan_Metadatas->get_metadata_by_id($metadata->get_id());

        $this->assertEquals($test->get_name(), 'metadado');
        $this->assertEquals($test->get_collection_id(), $collection->get_id());
        $this->assertEquals('Tainacan\Field_Types\Text', $test->get_field_type());
        $this->assertEquals($test->get_field_type_object(), $type);
    }
}