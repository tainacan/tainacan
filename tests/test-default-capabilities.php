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
        
		$Tainacan_Capabilities = \Tainacan\Capabilities::get_instance();
		$c = new \Tainacan\Entities\Collection();
		$caps = $c->get_capabilities();
		
		
		$defaults_caps = $Tainacan_Capabilities->defaults;
		
		$tainacan_roles = $Tainacan_Capabilities->get_tainacan_roles();
		
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
				
				if(in_array($role_name, $tainacan_roles) ) {
					foreach ($caps as $cap) {
						$this->assertTrue(current_user_can($entity_cap->$cap), "tainacan-$role_name does not have capability {$entity_cap->$cap}");
					}
				}
			}
		}
    }
    
    /**
     * @group capabilities_denpendecies
     */
    function test_capabilities_denpendecies() {
        
        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'   => 'test capabilities denpendecies',
            ),
            true
        );
        $item = $this->tainacan_entity_factory->create_entity(
            'item',
            array(
                'title'      => 'test capabilities denpendecies Item',
                'collection' => $collection,
            ),
            true
        );
        
        $Tainacan_Capabilities = \Tainacan\Capabilities::get_instance();
        $deps = $Tainacan_Capabilities::$dependencies;
        $defaults_caps = $Tainacan_Capabilities->defaults;
        $tainacan_roles = $Tainacan_Capabilities->get_tainacan_roles();
        
        foreach ($defaults_caps as $post_type => $wp_append_roles) {
            if(array_key_exists($post_type, $deps)) {
                $entity = false; 
                $entity_cap = false;
                
                if($post_type != 'tainacan-items') {
                    $entity = Repository::get_entity_by_post_type($post_type);
                    $entity_cap = $entity->get_capabilities();
                }
                
                foreach ($wp_append_roles as $role_name => $caps) {
                    $role = get_role($role_name);
                    
                    $new_user = $this->factory()->user->create(array( 'role' => $role_name ));
                    wp_set_current_user($new_user);
                    
                    foreach ($caps as $cap) {
                        if(array_key_exists($cap, $deps[$post_type])) {
                            $dep_cap = $deps[$post_type][$cap];
                            if($post_type == 'tainacan-items') {
                                $this->assertTrue(current_user_can($dep_cap), "$role_name does not have a dependency capability {$dep_cap} for tainacan-items" );
                            } else {
                                $this->assertTrue(current_user_can($dep_cap), "$role_name does not have a dependency capability {$dep_cap} for {$entity_cap->$cap}" );
                            }
                        }
                    }
                    
                    $new_user = $this->factory()->user->create(array( 'role' => 'tainacan-' . $role_name ));
                    wp_set_current_user($new_user);
                    
                    if(in_array($role_name, $tainacan_roles) ) {
                        foreach ($caps as $cap) {
                            if(array_key_exists($cap, $deps[$post_type])) {
                                $dep_cap = $deps[$post_type][$cap];
                                if($post_type == 'tainacan-items') {
                                    $this->assertTrue(current_user_can($dep_cap), "tainaca-$role_name does not have a dependency capability {$dep_cap} for tainacan-items" );
                                } else {
                                    $this->assertTrue(current_user_can($dep_cap), "tainaca-$role_name does not have a dependency capability {$dep_cap} for {$entity_cap->$cap}" );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}