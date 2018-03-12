<?php

namespace Tainacan\Tests;
use Tainacan\Repositories\Repository;

/**
 * Class DefaultCapabilities
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class DefaultCapabilities extends TAINACAN_UnitTestCase {


    function test_capabilities_present() {
        
		global $Tainacan_Capabilities;
		$c = new \Tainacan\Entities\Collection();
		$caps = $c->get_capabilities();
		
		
		$defaults_caps = $Tainacan_Capabilities->defaults;
		
		foreach ($defaults_caps as $post_type => $wp_append_roles) {
			if($post_type == 'tainacan-items') continue;
			$entity = Repository::get_entity_by_post_type($post_type);
			$entity_cap = $entity->get_capabilities();

			foreach ($wp_append_roles as $role_name => $caps) {
				$role = get_role($role_name);
				
				$new_user = $this->factory()->user->create(array( 'role' => $role_name ));
				wp_set_current_user($new_user);
				
				foreach ($caps as $cap) {
					$this->assertTrue(current_user_can($entity_cap->$cap), "$role_name does not have capability {$entity_cap->$cap}" );
				}
				
				$new_user = $this->factory()->user->create(array( 'role' => 'tainacan-' . $role_name ));
				wp_set_current_user($new_user);
				
				foreach ($caps as $cap) {
					$this->assertTrue(current_user_can($entity_cap->$cap), "tainacan-$role_name does not have capability {$entity_cap->$cap}");
				}
				
				
			}
		}
		
    }
}