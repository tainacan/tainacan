<?php

namespace Tainacan\Tests;

use \GuzzleHttp\Client;

class TAINACAN_REST_Collections_Controller extends \PHPUnit_Framework_TestCase {
    protected $client;

    const URL = 'http://localhost/wordpress/';

    protected function setUp(){
        $this->client = new Client([
            'base_uri' => self::URL,
        ]);
    }

    public function test_fetch_collection_by_id(){
        $id = 1;
        $response = $this->client->request('GET', 'wp-json/tainacan/v2/collections/'. $id);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode(json_decode($response->getBody(), true));
        var_dump($data);
        $this->assertEquals('teste', $data->name);
    }

    public function test_fetch_collections(){
        $response = $this->client->request('GET', 'wp-json/tainacan/v2/collections/');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        var_dump($data);
    }

}

?>
