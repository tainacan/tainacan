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
				"read_post"
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
				"read_post"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read_post"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read_post"
			],
			"subscriber"=> [
				"read_post"
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
				"read_post"
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
				"read_post"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read_post"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read_post"
			],
			"subscriber"=> [
				"read_post"
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
				"read_post"
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
				"read_post"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read_post"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read_post"
			],
			"subscriber"=> [
				"read_post"
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
				"read_post"
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
				"read_post"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read_post"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read_post"
			],
			"subscriber"=> [
				"read_post"
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
				"read_post"
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
				"read_post"
			],
			"author"=> [
				"delete_posts",
				"edit_posts",
				"publish_posts",
				"delete_published_posts",
				"edit_published_posts",
				"read_post"
			],
			"contributor"=> [
				"delete_posts",
				"edit_posts",
				"read_post"
			],
			"subscriber"=> [
				"read_post"
			]
		]
	];
	
	/**
	 * Register hooks
	 */
	function __construct() {
		add_action('init', array(&$this, 'init'), 11);
	}
	
	/**
	 * Update post_type caps using WordPress basic roles
	 * @param string $name //capability name
	 */
	public function init() {
		$defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults);
		
		foreach ($defaults_caps as $post_type => $wp_append_roles)
		{
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
	}
	
	/**
	 * Return list of editable roles
	 * @return array List of roles
	 */
	public static function get_editable_roles()
	{
		// TODO $this->security_check();
		global $wp_roles;
		
		if(! isset($wp_roles))
			$wp_roles = new \WP_Roles();
			
			$all_roles = $wp_roles->get_names();
			$editable_roles = apply_filters('tainacan-editable-roles', $all_roles);
			
			return $all_roles;
	}
}