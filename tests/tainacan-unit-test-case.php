<?php

namespace Tainacan\Tests;

use Tainacan\Tests\Factories;

class TAINACAN_UnitTestCase extends \WP_UnitTestCase {
	protected $tainacan_entity_factory;

	public function setUp(){
		$this->tainacan_entity_factory = new Factories\Entity_Factory();
	}
}