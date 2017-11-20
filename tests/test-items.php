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
class Items extends \WP_UnitTestCase {

    
    function teste_query(){
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new \Tainacan\Entities\Collection();
        $collection2 = new \Tainacan\Entities\Collection();
        
        $collection->set_name('teste');
        $collection = $Tainacan_Collections->insert($collection);
        $collection2->set_name('teste2');
        $collection2 = $Tainacan_Collections->insert($collection2);
        
        $metadata = new \Tainacan\Entities\Metadata();
        $metadata2 = new \Tainacan\Entities\Metadata();
        $metadata3 = new \Tainacan\Entities\Metadata();
        
        $metadata->set_name('metadado');
        $metadata->set_collection( $collection );
        $metadata = $Tainacan_Metadatas->insert($metadata);
        $metadata2->set_name('metadado2');
        $metadata2->set_collection( $collection2 );
        $metadata2 = $Tainacan_Metadatas->insert($metadata2);
        $metadata3->set_name('metadado3');
        $metadata3->set_collection( $collection2 );
        $metadata3 = $Tainacan_Metadatas->insert($metadata3);

        global $Tainacan_Items;
        $i = new \Tainacan\Entities\Item();
        $i->set_title('orange');
        $i->set_collection($collection);
        $i->add_metadata($metadata, 'value_1');
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->fetch($item->get_id());
        $meta_test = $item->get_metadata();
        
        $this->assertTrue( isset($meta_test[$metadata->get_id()]) );
        $this->assertTrue( $meta_test[$metadata->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity );
        $this->assertEquals( $meta_test[$metadata->get_id()]->get_value(), 'value_1');
        
        $i = new \Tainacan\Entities\Item();
        $i->set_title('apple');
        $i->set_collection($collection2);
        $i->add_metadata($metadata2, 'value_2');
        $i->add_metadata($metadata3, 'value_2');
        $item = $Tainacan_Items->insert($i);
        
        $i = new \Tainacan\Entities\Item();
        $i->set_title('lemon');
        $i->set_collection($collection2);
        $i->add_metadata($metadata2, 'value_2');
        $i->add_metadata($metadata2, 'value_3'); // if we set twice, value is overridden
        $i->add_metadata($metadata3, 'value_3');
        $item = $Tainacan_Items->insert($i);
        
        $i = new \Tainacan\Entities\Item();
        $i->set_title('pinapple');
        $i->set_collection($collection2);
        $i->add_metadata($metadata2, 'value_3');
        $i->add_metadata($metadata3, 'value_6');
        $item = $Tainacan_Items->insert($i);
        
        // should return all 4 items
        $test_query = $Tainacan_Items->query([]);
        $this->assertEquals(4, sizeof($test_query));
        
        // should also return all 4 items
        $test_query = $Tainacan_Items->query(['collections' => [$collection, $collection2]]);
        $this->assertEquals(4, sizeof($test_query));
        
        // should return only the first item
        $test_query = $Tainacan_Items->query(['collections' => $collection]);
        $this->assertEquals(1, sizeof($test_query));
        $this->assertEquals('orange', $test_query[0]->get_title());
        $test_query = $Tainacan_Items->query(['title' => 'orange']);
        $this->assertEquals(1, sizeof($test_query));
        $this->assertEquals('orange', $test_query[0]->get_title());
        
        // should return the other 3 items
        $test_query = $Tainacan_Items->query(['collections' => $collection2]);
        $this->assertEquals(3, sizeof($test_query));
        
        $test_query = $Tainacan_Items->query(['title' => 'apple']);
        $this->assertEquals(1, sizeof($test_query));
        $this->assertEquals('apple', $test_query[0]->get_title());
        $apple_meta = $test_query[0]->get_metadata();
        $this->assertEquals(2, sizeof($apple_meta));
        $apple_meta_values = [];
        foreach ($apple_meta as $am) {
            $this->assertEquals('value_2', $am->get_value());
        }
        
        // should return 1 item
        $test_query = $Tainacan_Items->query([
            'collections' => $collection2,
            'metadata' => [
                [
                    'key' => $metadata2->get_id(),
                    'value' => 'value_2'
                ]
            ]
        ]);
        $this->assertEquals(1, sizeof($test_query));
        
        // should return 2 items
        $test_query = $Tainacan_Items->query([
            'collections' => $collection2,
            'metadata' => [
                [
                    'key' => $metadata2->get_id(),
                    'value' => 'value_3'
                ]
            ]
        ]);
        $this->assertEquals(2, sizeof($test_query));
        
        // should return 2 item
        $test_query = $Tainacan_Items->query([
            'collections' => $collection2,
            'metadata' => [
                [
                    'key' => $metadata3->get_id(),
                    'value' => 'value_2',
                    'compare' => '>'
                ]
            ]
        ]);
        $this->assertEquals(2, sizeof($test_query));

    }
}