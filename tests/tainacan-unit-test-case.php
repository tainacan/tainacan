<?php

namespace Tainacan\Tests;

use Tainacan\Tests\Factories;

class TAINACAN_UnitTestCase extends \WP_UnitTestCase {
	protected $tainacan_entity_factory;
	protected $tainacan_field_factory;
	protected $tainacan_filter_factory;
	protected $user_id;

	public function setUp(){
		parent::setUp();
		$this->tainacan_entity_factory = new Factories\Entity_Factory();
		$this->tainacan_field_factory  = new Factories\Field_Factory();
		$this->tainacan_filter_factory = new Factories\Filter_Factory();
		
		$new_admin_user = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_admin_user);
		$this->user_id = $new_admin_user;
	}
}