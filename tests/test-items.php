<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities\Entity;

/**
 * Sample test case.
 */
class Items extends \WP_UnitTestCase {

    
    function teste_query(){
        global $Tainacan_Collections, $Tainacan_Metadatas, $Tainacan_Item_Metadata;

        $collection = new \Tainacan\Entities\Collection();
        $collection2 = new \Tainacan\Entities\Collection();
        
        $collection->set_name('teste');
        $collection->set_status('publish');
        $collection->validate();
        $collection = $Tainacan_Collections->insert($collection);
        $collection2->set_name('teste2');
        $collection2->set_status('publish');
        $collection2->validate();
        $collection2 = $Tainacan_Collections->insert($collection2);
        
        $metadata = new \Tainacan\Entities\Metadata();
        $metadata2 = new \Tainacan\Entities\Metadata();
        $metadata3 = new \Tainacan\Entities\Metadata();
        
        $metadata->set_name('metadado');
        $metadata->set_collection( $collection );
        $metadata->set_status('publish');
        $metadata->validate();
        $metadata = $Tainacan_Metadatas->insert($metadata);
        $metadata2->set_name('metadado2');
        $metadata2->set_collection( $collection2 );
        $metadata2->set_status('publish');
        $metadata2->validate();
        $metadata2 = $Tainacan_Metadatas->insert($metadata2);
        $metadata3->set_name('metadado3');
        $metadata3->set_collection( $collection2 );
        $metadata3->set_status('publish');
        $metadata3->validate();
        $metadata3 = $Tainacan_Metadatas->insert($metadata3);

        global $Tainacan_Items;
        $i = new \Tainacan\Entities\Item();
        $i->set_title('orange');
        $i->set_collection($collection);
        $i->add_metadata($metadata, 'value_1');
        $i->validate();
        /**
         * @var \Tainacan\Entities\Item $item
         */
        $item = $Tainacan_Items->insert($i);
        
        $item = $Tainacan_Items->fetch($item->get_id());
        $meta_test = $item->get_metadata();
        
        $this->assertTrue( isset($meta_test[$metadata->get_id()]) );
        $this->assertTrue( $meta_test[$metadata->get_id()] instanceof \Tainacan\Entities\Item_Metadata_Entity );
        $this->assertEquals( 'value_1', $meta_test[$metadata->get_id()]->get_value());
        
        $i = new \Tainacan\Entities\Item();
        $i->set_title('apple');
        $i->set_collection($collection2);
        $i->add_metadata($metadata2, 'value_2');
        $i->add_metadata($metadata3, 'value_2');
        $i->validate();
        $item = $Tainacan_Items->insert($i);
        
        $i = new \Tainacan\Entities\Item();
        $i->set_title('lemon');
        $i->set_collection($collection2);
        $i->add_metadata($metadata2, 'value_2');
        $i->add_metadata($metadata2, 'value_3'); // if we set twice, value is overridden
        $i->add_metadata($metadata3, 'value_3');
        $i->validate();
        $item = $Tainacan_Items->insert($i);
        
        $i = new \Tainacan\Entities\Item();
        $i->set_title('pinapple');
        $i->set_collection($collection2);
        $i->add_metadata($metadata2, 'value_3');
        $i->add_metadata($metadata3, 'value_6');
        $i->validate();
        $item = $Tainacan_Items->insert($i);
        
        // should return all 4 items
        $test_query = $Tainacan_Items->fetch([]);
        $this->assertEquals(4, $test_query->post_count );
        
        // should also return all 4 items
        $test_query = $Tainacan_Items->fetch([], [$collection, $collection2]);
        $this->assertEquals(4, $test_query->post_count);
        
        // should return 2 items
        $test_query = $Tainacan_Items->fetch(['posts_per_page' => 2], [$collection, $collection2]);
        $this->assertEquals(2, $test_query->post_count);
        
        // should return only the first item
        $test_query = $Tainacan_Items->fetch([], $collection);
        $this->assertEquals(1,$test_query->post_count);

        $test_query->the_post();
        $item = new \Tainacan\Entities\Item( get_the_ID() );
        $this->assertEquals('orange', $item->get_title() );

        $test_query = $Tainacan_Items->fetch(['title' => 'orange']);
        $test_query->the_post();
        $item = new \Tainacan\Entities\Item( get_the_ID() );

        $this->assertEquals(1, $test_query->post_count);
        $this->assertEquals('orange', $item->get_title());
        
        // should return the other 3 items
        $test_query = $Tainacan_Items->fetch([], $collection2);
        $this->assertEquals(3,$test_query->post_count);
        
        $test_query = $Tainacan_Items->fetch(['title' => 'apple']);
        $test_query->the_post();
        $item = new \Tainacan\Entities\Item( get_the_ID() );

        $this->assertEquals(1, $test_query->post_count);
        $this->assertEquals('apple', $item->get_title());
        $apple_meta = $item->get_metadata();
        $this->assertEquals(2, sizeof( $apple_meta ));
        $apple_meta_values = [];
        foreach ($apple_meta as $am) {
            $this->assertEquals('value_2', $am->get_value());
        }
        
        // should return 1 item
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $metadata2->get_id(),
                    'value' => 'value_2'
                ]
            ]
        ], $collection2);
        $this->assertEquals(1, $test_query->post_count);
        
        // should return 2 items
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $metadata2->get_id(),
                    'value' => 'value_3'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);
        
        // should return 2 item
        $test_query = $Tainacan_Items->fetch([
            'meta_query' => [
                [
                    'key' => $metadata3->get_id(),
                    'value' => 'value_2',
                    'compare' => '>'
                ]
            ]
        ], $collection2);
        $this->assertEquals(2, $test_query->post_count);

    }
}