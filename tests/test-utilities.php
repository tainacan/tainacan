<?php

namespace Tainacan\Tests;

/**
 * Class TestUtilities
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class TestUtilities extends TAINACAN_UnitTestCase {


    function test_initials() {
        

        $string = 'Roberto Carlos';

        $this->assertEquals('RC', tainacan_get_initials($string));

        $string = 'Ronaldo';

        $this->assertEquals('RO', tainacan_get_initials($string));
        $this->assertEquals('R', tainacan_get_initials($string, true));

        $string = 'Marilia Mendonça das Neves Costa Silva Fonseca';

        $this->assertEquals('MF', tainacan_get_initials($string));
        $this->assertEquals('M', tainacan_get_initials($string, true));
        
        $string = 'Machado de Assis';

        $this->assertEquals('MA', tainacan_get_initials($string));

        $string = 'b';

        $this->assertEquals('B', tainacan_get_initials($string));

        $string = '';

        $this->assertEquals('', tainacan_get_initials($string));

	}
	
	function test_get_descendants_ids() {
		
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_c = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_gc = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2_c->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		$collection2_gc2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2_c->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$collection2_ggc = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test',
				'parent' => $collection2_gc->get_id(),
				'status'	 => 'publish',
			),
			true
		);
		
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2);
		$this->assertEquals(4, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_c);
		$this->assertEquals(3, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_gc);
		$this->assertEquals(1, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_ggc);
		$this->assertEquals(0, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection);
		$this->assertEquals(0, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2, 2);
		$this->assertEquals(3, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2, 1);
		$this->assertEquals(1, sizeof($test));
		
		$test = $Tainacan_Collections->get_descendants_ids($collection2_c, 1);
		$this->assertEquals(2, sizeof($test));
		
	}
	
	
}