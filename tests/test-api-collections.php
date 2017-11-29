<?php

namespace Tainacan\Tests;

class TAINACAN_REST_Collections_Controller extends \WP_UnitTestCase {
    const URL = 'http://localhost/wordpress-test/';

    public function test_create_and_fetch_collection_by_id(){

        $collection_JSON = json_encode([
            'name'         => 'Teste',
            'description'  => 'Teste JSON',
            'itemsPerPage' => 10,
        ]);

        $collection = wp_remote_post(self::URL . 'wp-json/tainacan/v2/collections/', array(
            'body' => $collection_JSON
        ));

        $collection = json_decode(json_decode($collection['body'], true), true);

        $id = $collection['id'];

        $response = wp_remote_get(self::URL . 'wp-json/tainacan/v2/collections/'. $id);

        $this->assertEquals(200, $response['response']['code']);

        $data = json_decode(json_decode($response['body'], true), true);

        $this->assertEquals('Teste', $data['name']);
    }

    public function test_fetch_collections(){
        $response = wp_remote_get(self::URL . 'wp-json/tainacan/v2/collections/');

	    $this->assertEquals(200, $response['response']['code']);

        $data = json_decode(json_decode($response['body'], true), true);

        $this->assertContainsOnly('string', $data);

        $one_collection = json_decode($data[0], true);

        $this->assertEquals('Teste', $one_collection['name']);
    }

}

?>
