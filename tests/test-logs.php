<?php

namespace Tainacan\Tests;

use Tainacan\Entities\Collection;
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
		$Tainacan_Logs = \Tainacan\Repositories\Logs::get_instance();

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
		$test = $Tainacan_Logs->fetch( $log->get_id() );

		$this->assertEquals( 'blame someone', $test->get_title() );
		$this->assertEquals( 'someone did that', $test->get_description() );
		$this->assertEquals( $user_id, $test->get_user_id() );
		$this->assertEquals( $blog_id, $test->get_blog_id() );
	}

	public function test_log_diff() {
		$Tainacan_Logs    = \Tainacan\Repositories\Logs::get_instance();
		$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

		$filter = $this->tainacan_entity_factory->create_entity(
			'filter',
			array(
				'name'        => 'No name',
			),
			true
		);

		// Modify filter name
		$filter->set_name( 'With name' );

		$Tainacan_Filters->update( $filter );

		$log = $Tainacan_Logs->fetch_last();

		$diff = $log->get_log_diffs();

		$this->assertEquals( 'With name', "{$diff['name']['new'][0]} {$diff['name']['new'][1]}" );
		$this->assertEquals( 'No name', $diff['name']['old'] );
		$this->assertEquals( 'With', $diff['name']['diff_with_index'][0] );
	}
}