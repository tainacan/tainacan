<?php

namespace Tainacan;
use Tainacan\Repositories\Repository;

class Capabilities {
	protected $defaults = [
		"tainacan-collections"=> [
			"administrator"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"editor"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read"
			],
			"subscriber"=> [
				"read"
			]
		],
		"tainacan-metadata"=> [
			"administrator"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"editor"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read"
			],
			"subscriber"=> [
				"read"
			]
		],
		"tainacan-filters"=> [
			"administrator"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"editor"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read"
			],
			"subscriber"=> [
				"read"
			]
		],
		"tainacan-taxonomies"=> [
			"administrator"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"editor"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read"
			],
			"subscriber"=> [
				"read"
			]
		],
		"tainacan-logs"=> [
			"administrator"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"editor"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read"
			],
			"subscriber"=> [
				"read"
			]
		],
		"tainacan-items"=> [ // abstract, will apply to collection Items
			"administrator"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"editor"=> [
				"delete_posts",
				"delete_private_posts",
				"edit_posts",
				"edit_private_posts",
				"publish_posts",
				"read_private_posts",
				"delete_published_posts",
				"edit_published_posts",
				"edit_others_posts",
				"delete_others_posts",
				"read"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read"
			],
			"subscriber"=> [
				"read"
			]
		],
	];
	
	/**
	 * Register hooks
	 */
	function __construct() {
		add_action('init', array(&$this, 'init'), 11);
		add_action('tainacan-insert-tainacan-collections', array(&$this, 'new_collection'));
		
        add_action('tainacan-add-collection-moderators', array(&$this, 'add_moderators'), 10, 2);
		add_action('tainacan-remove-collection-moderators', array(&$this, 'remove_moderators'), 10, 2);
        
        
	}
	
	/**
	 * Update post_type caps using WordPress basic roles
	 * @param string $name //capability name
	 */
	public function init() {
		$defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults);
		
		foreach ($defaults_caps as $post_type => $wp_append_roles) {
			if($post_type == 'tainacan-items') continue;
			$entity = Repository::get_entity_by_post_type($post_type);
			// append new capabilities to WordPress default roles
			foreach ($wp_append_roles as $role_name => $caps) {
				$role = get_role($role_name);
				if(!is_object($role)) {
					throw new \Exception(sprintf('Role "%s" not found', $role_name));
				}
				
				foreach ($caps as $cap) {
					$role->add_cap($entity->cap->$cap);
				}
			}
		}
		global $Tainacan_Collections;
		$collections = $Tainacan_Collections->fetch([], 'OBJECT');
		foreach ($collections as $collection) {
			$this->set_items_capabilities($collection, $defaults_caps);
		}
	}
	
	/**
	 * Set config roles for items
	 * @param \Tainacan\Entities\Collection $collection
	 */
	public function set_items_capabilities($collection, $defaults_caps = null) {
		if(is_null($defaults_caps))	$defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults); // External Call
		$item = new \Tainacan\Entities\Item();
		$item->set_collection($collection);
		
		foreach ($defaults_caps['tainacan-items'] as $role_name => $caps) {
			$role = get_role($role_name);
			if(!is_object($role)) {
				throw new \Exception(sprintf('Role "%s" not found', $role_name));
			}
			
			foreach ($caps as $cap) {
				$role->add_cap($collection->cap->$cap);
			}
		}
	}
	
	/**
	 * Return list of editable roles
	 * @return array List of roles
	 */
	public static function get_editable_roles()	{
		// TODO $this->security_check();
		global $wp_roles;
		
		if(! isset($wp_roles)) $wp_roles = new \WP_Roles();
			
		$all_roles = $wp_roles->get_names();
		$editable_roles = apply_filters('tainacan-editable-roles', $all_roles);
		
		return $all_roles;
	}
	
	/**
	 * Hook to set capabilities to the new created collection
	 * @param \Tainacan\Entities\Collection $collection
	 */
	public function new_collection($collection)
	{
		$this->set_items_capabilities($collection);
	}
    
    
    public function remove_moderators($collection, $moderators) {
        $defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults);
        if (is_array($moderators)) {
            foreach ($moderators as $moderator) {
                $user = get_userdata($moderator);
                $cpt_object = get_post_type_object($collection->get_db_identifier());
                
                if ($user instanceof \WP_User && $cpt_object instanceof \WP_Post_Type) {
                    $caps = $defaults_caps['tainacan-items']['editor'];
                    foreach ($caps as $cap) {
        				$user->remove_cap($cpt_object->cap->$cap);
        			}
                }
            }
        }
    }
    
    public function add_moderators($collection, $moderators) {
        $defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults);
        if (is_array($moderators)) {
            foreach ($moderators as $moderator) {
                $user = get_userdata($moderator);
                $cpt_object = get_post_type_object($collection->get_db_identifier());
                
                
                
                if ($user instanceof \WP_User && $cpt_object instanceof \WP_Post_Type) {
                    $caps = $defaults_caps['tainacan-items']['editor'];
                    foreach ($caps as $cap) {
        				$user->add_cap($cpt_object->cap->$cap);
        			}
                }
            }
        }
    }
    
    
    
}
