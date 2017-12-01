<?php

namespace Tainacan\Tests;
use Tainacan\Entities\Log;
use Tainacan\Entities\Collection;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class Logs extends \WP_UnitTestCase {


    /**
     * Teste da insercao de um log simples apenas se criar o dado bruto
     */
    function test_add() {
        global $Tainacan_Logs;

        $log = new Log();

        //setando os valores na classe do tainacan
        $log->set_title('blame someone');
        $log->set_description('someone did that');
        
        $user_id = get_current_user_id();
        $blog_id = get_current_blog_id();

        //inserindo
        $log->validate();
        $log = $Tainacan_Logs->insert($log);

        //retorna a taxonomia
        $test = $Tainacan_Logs->fetch($log->get_id());

        $this->assertEquals( 'blame someone', $test->get_title() );
        $this->assertEquals( 'someone did that', $test->get_description() );
        $this->assertEquals( $user_id, $test->get_user_id() );
        $this->assertEquals( $blog_id, $test->get_blog_id() );
        
        $value = new Collection();
        $value->set_name('testeLogs');
        $value->set_description('adasdasdsa123');
        $value->set_default_order('DESC');
        
        global $Tainacan_Collections;
        $value->validate();
        $value = $Tainacan_Collections->insert($value);
        
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
        $collection = $last_log->get_value();
        
        $this->assertEquals($collection->get_name(), 'new_testeLogs');
        $this->assertEquals($collection->get_description(), 'adasdasdsa123');
        $this->assertEquals($collection->get_default_order(), 'DESC');
    }
}