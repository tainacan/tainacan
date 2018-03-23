<?php

namespace Tainacan\Tests;
use Tainacan\Entities\Log;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Logs extends TAINACAN_UnitTestCase {


    /**
     * Teste da insercao de um log simples apenas se criar o dado bruto
     */
    function test_add() {
        $Tainacan_Logs = \Tainacan\Repositories\Logs::getInstance();

        $log = $this->tainacan_entity_factory->create_entity(
        	'log',
	        array(
	        	'title'       => 'blame someone',
		        'description' => 'someone did that'
	        ),
	        true
        );
        
        $user_id = get_current_user_id();
        $blog_id = get_current_blog_id();

        //retorna a taxonomia
        $test = $Tainacan_Logs->fetch($log->get_id());

        $this->assertEquals( 'blame someone', $test->get_title() );
        $this->assertEquals( 'someone did that', $test->get_description() );
        $this->assertEquals( $user_id, $test->get_user_id() );
        $this->assertEquals( $blog_id, $test->get_blog_id() );
        
        $value = $this->tainacan_entity_factory->create_entity(
        	'collection',
	        array(
	        	'name'          => 'testeLogs',
		        'description'   => 'adasdasdsa123',
		        'default_order' => 'DESC'
	        ),
	        true
        );
        
        $Tainacan_Collections = \Tainacan\Repositories\Collections::getInstance();
        
        $value->set_name('new_testeLogs');
        $value->validate();
        $new_value = $Tainacan_Collections->insert($value);
        
        $create_log = Log::create('teste create', 'testing a log creation function', $new_value, $value);
        
        $this->assertEquals( 'teste create', $create_log->get_title() );
        $this->assertEquals( 'testing a log creation function', $create_log->get_description() );
        $this->assertEquals( $new_value, $create_log->get_value() );
        $this->assertEquals( $value, $create_log->get_old_value() );
        
        $testDB = $Tainacan_Logs->fetch($create_log->get_id());
        
        $this->assertEquals( 'teste create', $testDB->get_title() );
        $this->assertEquals( 'testing a log creation function', $testDB->get_description() );
        $this->assertEquals( $new_value, $testDB->get_value() );
        $this->assertEquals( $value, $testDB->get_old_value() );
        
        $last_log = $Tainacan_Logs->fetch_last();
        $this->assertTrue(is_object($last_log));
        $collection = $last_log->get_value();
        
        $this->assertEquals($collection->get_name(), 'new_testeLogs');
        $this->assertEquals($collection->get_description(), 'adasdasdsa123');
        $this->assertEquals($collection->get_default_order(), 'DESC');
    }
}