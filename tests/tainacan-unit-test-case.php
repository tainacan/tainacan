<?php

namespace Tainacan\Tests;

use Tainacan\Tests\Factories;

#[\AllowDynamicProperties]
class TAINACAN_UnitTestCase extends \WP_UnitTestCase {
	protected $tainacan_entity_factory;
	protected $tainacan_metadatum_factory;
	protected $tainacan_filter_factory;
	protected $tainacan_item_metadata_factory;
	protected $user_id;

	public function setUp(): void {
		parent::setUp();
		$this->tainacan_entity_factory = new Factories\Entity_Factory();
		$this->tainacan_metadatum_factory  = new Factories\Metadatum_Factory();
		$this->tainacan_filter_factory = new Factories\Filter_Factory();
		$this->tainacan_item_metadata_factory = new Factories\Item_Metadata_Factory();
		
		$new_admin_user = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_admin_user);
		$this->user_id = $new_admin_user;
	}
}

/**
 * Test double used to detect accidental object instantiation via unserialize().
 */
class Tainacan_POI_Canary {
	public static $woke = false;

	public function __wakeup() {
		self::$woke = true;
	}

	public static function reset() {
		self::$woke = false;
	}
}