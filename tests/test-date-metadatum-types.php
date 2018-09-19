<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Sample test case.
 */
class DateMetadatumTypes extends TAINACAN_UnitTestCase {

	
    function test_date_metadata_types() {

        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        
        $collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
			),
			true
		);
		
        
        $metadatum = $this->tainacan_entity_factory->create_entity(
        	'metadatum',
	        array(
	        	'name' => 'meta',
		        'description' => 'description',
		        'collection' => $collection,
		        'metadata_type' => 'Tainacan\Metadata_Types\Date',
				'status'	 => 'publish',
	        ),
	        true
        );
        
		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'         => 'item test',
				'description'   => 'adasdasdsa',
				'collection'    => $collection,
				'status'		   => 'publish',
			),
			true
		);
		

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($i, $metadatum);
        
        $item_metadata->set_value('2010-01-01');
        $this->assertTrue($item_metadata->validate());
        
        $item_metadata->set_value('2010-12-01');
        $this->assertTrue($item_metadata->validate());
        
        $item_metadata->set_value('2010-12-31');
        $this->assertTrue($item_metadata->validate());
        
        $item_metadata->set_value('2010-22-01');
        $this->assertFalse($item_metadata->validate());
        
        $item_metadata->set_value('3/3/1202');
        $this->assertFalse($item_metadata->validate());
        
        $item_metadata->set_value('2010-02-30');
		$this->assertFalse($item_metadata->validate());

    }
	
    
}