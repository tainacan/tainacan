<?php

namespace Tainacan;
use Tainacan\Repositories\Repository;

class Capabilities {
	public $defaults = [
		"tainacan-collection"=> [
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
				"read"
			],
			"subscriber"=> [
				"read"
			]
		],
		"tainacan-metadatum"=> [
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
		"tainacan-filter"=> [
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
		"tainacan-taxonomy"=> [
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
		"tainacan-log"=> [
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
	
	public static $dependencies = [
	    "tainacan-items" => [
	        'edit_posts'           => 'upload_files',
	        "edit_private_posts"   => 'upload_files',
	        "edit_published_posts" => 'upload_files',
	        "edit_others_posts"    => 'upload_files'
	    ]
	];
	
    private static $instance = null;

    public static function get_instance()
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

	/**
	 * Register hooks
	 */
	private function __construct() {
		add_action('tainacan-insert-tainacan-collection', array(&$this, 'new_collection'));
		
        add_action('tainacan-add-collection-moderators', array(&$this, 'add_moderators'), 10, 2);
		add_action('tainacan-remove-collection-moderators', array(&$this, 'remove_moderators'), 10, 2);
        
		add_filter( 'gettext_with_context', array(&$this, 'translate_user_roles'), 10, 4 );
		
		// Dummy calls for translation. 
		_x('Tainacan Author', 'User role', 'tainacan');
		_x('Tainacan Contributor', 'User role', 'tainacan');
		_x('Tainacan Editor', 'User role', 'tainacan');
        
	}
	
	/**
	 * Tainacan default roles
	 *
	 * These are roles relative to the native WordPress roles. They will have 
	 * the same capabilities of their relatives native roles in what is concerned to 
	 * tainacan activities, but will have no other WordPress capabilities.
	 * 
	 * @return array Tainacan roles
	 */
	public function get_tainacan_roles() {
		$tainacan_roles = [
			'editor' => [
				'slug' => 'tainacan-editor',
				'display_name' => 'Tainacan Editor'
			],
			'contributor' => [
				'slug' => 'tainacan-contributor',
				'display_name' => 'Tainacan Contributor'
			],
			'author' => [
				'slug' => 'tainacan-author',
				'display_name' => 'Tainacan Author'
			],
		];
		
		return $tainacan_roles;
	}
	
	/**
	 * Callback to gettext_with_context hook to translate custom ueser roles.
	 *
	 * Since user roles are stored in the database, we have to translate them on the fly 
	 * using translate_user_role() function.
	 *
	 * @see https://wordpress.stackexchange.com/questions/141551/how-to-auto-translate-custom-user-roles
	 */
	public function translate_user_roles( $translations, $text, $context, $domain ) {

		$plugin_domain = 'tainacan';

		$roles_names = array_map(function($role) {
			return $role['display_name'];
		}, $this->get_tainacan_roles());
		
		if ( $context === 'User role' && in_array( $text, $roles_names ) && $domain !== $plugin_domain ) {
			return translate_with_gettext_context( $text, $context, $plugin_domain );
		}

		return $translations;
	}
	
	protected function check_dependencies($role, $post_type, $cap, $add = true) {
	    if(
	        array_key_exists($post_type, self::$dependencies) &&
	        array_key_exists($cap, self::$dependencies[$post_type])
	    ) {
	        $added = false;
	        if(! $role->has_cap(self::$dependencies[$post_type][$cap]) && $add) {
	            $role->add_cap(self::$dependencies[$post_type][$cap]);
	            $added = true;
	        }
	        if($role instanceof \WP_User && $add) { //moderator
	            $append_caps = get_user_meta($role->ID, '.tainacan-dependecies-caps', true);
	            if(! is_array($append_caps)) $append_caps = [];
	            if( 
	                (! array_key_exists(self::$dependencies[$post_type][$cap], $append_caps) && $added ) || // we never added and need to add
	                (
	                    array_key_exists(self::$dependencies[$post_type][$cap], $append_caps) &&
	                    $append_caps[self::$dependencies[$post_type][$cap]] === false &&
	                    $added
	                ) // we added but before is not need to add
	            ) {
	                $append_caps[self::$dependencies[$post_type][$cap]] = 0;
	            }
	            else { // we to not added this cap
	                $append_caps[self::$dependencies[$post_type][$cap]] = false;
	            }
	            if($append_caps[self::$dependencies[$post_type][$cap]] !== false) {
	               $append_caps[self::$dependencies[$post_type][$cap]]++; // add 1 to each collection he is a moderator
	               update_user_meta($role->ID, '.tainacan-dependecies-caps', $append_caps);
	            }
	        }
	        return self::$dependencies[$post_type][$cap];
	    }
	    return false;
	}
	
	
	/**
	 * Update post_type caps using WordPress basic roles and register tainacan roles
	 */
	public function init() {
		$defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults);
		
		foreach ($defaults_caps as $post_type => $wp_append_roles) {
			if($post_type == 'tainacan-items') continue;
			$entity = Repository::get_entity_by_post_type($post_type);
			$entity_cap = $entity->get_capabilities();
			// append new capabilities to WordPress default roles
			foreach ($wp_append_roles as $role_name => $caps) {
				$role = get_role($role_name);
				if(!is_object($role)) {
					throw new \Exception(sprintf('Role "%s" not found', $role_name));
				}
				
				foreach ($caps as $cap) {
					$role->add_cap($entity_cap->$cap);
					$this->check_dependencies($role, $post_type, $cap);
				}
				
				$tainacan_roles = $this->get_tainacan_roles();
				
				if (array_key_exists($role_name, $tainacan_roles)) {
					$tainacan_role = add_role( $tainacan_roles[$role_name]['slug'], $tainacan_roles[$role_name]['display_name'] );
					if(!is_object($tainacan_role)) {
						$tainacan_role = get_role($tainacan_roles[$role_name]['slug']);
					}
					
					if(!is_object($tainacan_role)) {
						throw new \Exception(sprintf('Role "%s" not found', $tainacan_roles[$role_name]['slug']));
					}
						
					foreach ($caps as $cap) {
						$tainacan_role->add_cap($entity_cap->$cap);
						$this->check_dependencies($tainacan_role, $post_type, $cap);
					}
				}
				
			}
		}
		
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
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
		
        $collection_items_caps = $collection->get_items_capabilities();
        
		foreach ($defaults_caps['tainacan-items'] as $role_name => $caps) {
			$role = get_role($role_name);
			if(!is_object($role)) {
				throw new \Exception(sprintf('Role "%s" not found', $role_name));
			}
			
			foreach ($caps as $cap) {
				$role->add_cap($collection_items_caps->$cap);
				$this->check_dependencies($role, 'tainacan-items', $cap);
			}
			
			// Tainacan relative role 
			$role = get_role('tainacan-' . $role_name);
			if (\is_object($role)) {
				foreach ($caps as $cap) {
					$role->add_cap($collection_items_caps->$cap);
					$this->check_dependencies($role, 'tainacan-items', $cap);
				}
			}
			
		}
        
        // Refresh roles capabilities for current user to have instant effect
        global $current_user;
        $current_user->get_role_caps();
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
    
    /**
     * Hooke to revoke the capabilities for the items post type of the collection
     * @param  \Tainacan\Entities\Collection $collection The collection object
     * @param  array $moderators List of IDs of user IDs removed from the moderators list of the collection
     * @return void
     */
    public function remove_moderators($collection, $moderators) {
        $defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults);
        if (is_array($moderators)) {
            $collection_items_caps = $collection->get_items_capabilities();
            foreach ($moderators as $moderator) {
                $user = get_userdata($moderator);
                
                if ($user instanceof \WP_User && is_object($collection_items_caps)) {
                    $caps = $defaults_caps['tainacan-items']['editor'];
                    foreach ($caps as $cap) {
        				$user->remove_cap($collection_items_caps->$cap);
        				$dep_cap = $this->check_dependencies($user, 'tainacan-items', $cap, false);
        				if($dep_cap !== false) {
            				$appended_caps = get_user_meta($user->ID, '.tainacan-dependecies-caps', true);
            				if(array_key_exists($dep_cap, $appended_caps) && $appended_caps[$dep_cap] !== false && $appended_caps[$dep_cap] > 0) {
            				    $appended_caps[$dep_cap]--;
            				    update_user_meta($user->ID, '.tainacan-dependecies-caps', $appended_caps);
            				    if($appended_caps == 0) { // they are not a moderator of a collection, remove cap at all
            				        $user->remove_cap($cap);
            				    }
            				}
        				}
        			}
                }
            }
        }
    }
    
    /**
     * Hooke to grant the capabilities for the items post type of the collection
     * @param  \Tainacan\Entities\Collection $collection The collection object
     * @param  array $moderators List of IDs of user IDs added to the moderators list of the collection
     * @return void
     */
    public function add_moderators($collection, $moderators) {
        $defaults_caps = apply_filters('tainacan-defaults-capabilities', $this->defaults);
        if (is_array($moderators)) {
            $collection_items_caps = $collection->get_items_capabilities();
            foreach ($moderators as $moderator) {
                $user = get_userdata($moderator);
                
                if ($user instanceof \WP_User && is_object($collection_items_caps)) {
                    $caps = $defaults_caps['tainacan-items']['editor'];
                    foreach ($caps as $cap) {
        				$user->add_cap($collection_items_caps->$cap);
        				$this->check_dependencies($user, 'tainacan-items', $cap);
        			}
                }
            }
        }
    }
    
    
    
}
