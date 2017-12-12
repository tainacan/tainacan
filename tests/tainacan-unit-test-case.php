<?php

namespace Tainacan\Tests;

use Tainacan\Tests\Factories;

class TAINACAN_UnitTestCase extends \WP_UnitTestCase {
	protected $tainacan_entity_factory;
	protected $tainacan_field_factory;
	protected $tainacan_filter_factory;

	public function setUp(){
		parent::setUp();
		$this->tainacan_entity_factory = new Factories\Entity_Factory();
		$this->tainacan_field_factory  = new Factories\Field_Factory();
		$this->tainacan_filter_factory = new Factories\Filter_Factory();
	}
}