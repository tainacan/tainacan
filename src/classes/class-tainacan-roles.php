<?php

namespace Tainacan;
use Tainacan\Repositories\Repository;

class Roles {
	
	
	public static $dependencies = [
		"tainacan-items" => [
			'edit_posts'           => 'upload_files',
			"edit_private_posts"   => 'upload_files',
			"edit_published_posts" => 'upload_files',
			"edit_others_posts"    => 'upload_files'
		]
	];
	
	private static $instance = null;
	
	private $capabilities;
	
	public static function get_instance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	* 
	*/
	private function __construct() {
		
		$this->capabilities = [
			'manage_tainacan' => [
				'display_name' => __('Manage Tainacan', 'tainacan'),
				'description' => __('Manage all Tainacan features and all Collections', 'tainacan')
			],
			'tnc_rep_edit_users' => [
				'display_name' => __('Manage Users', 'tainacan'),
				'description' => __('Manage users roles and permissions', 'tainacan')
			],
			'tnc_rep_create_collections' => [
				'display_name' => __('Create Collections', 'tainacan'),
				'description' => __('Create new collections to the repository', 'tainacan')
			],
			'tnc_rep_create_taxonomies' => [
				'display_name' => __('Create and edit taxonomies', 'tainacan'),
				'description' => __('Create new taxonomies and edit its terms', 'tainacan')
			],
			'tnc_rep_manage_taxonomies' => [
				'display_name' => __('Manage Taxonomies', 'tainacan'),
				'description' => __('Manage all taxonomies and terms, including taxonomies created by other users', 'tainacan')
			],
			'tnc_rep_manage_metadata' => [
				'display_name' => __('Manage Repository Metadata', 'tainacan'),
				'description' => __('Create/edit/delete metadata in repository level', 'tainacan')
			],
			'tnc_rep_manage_filters' => [
				'display_name' => __('Manage Repository Filters', 'tainacan'),
				'description' => __('Create/edit/delete filters in repository level', 'tainacan')
			],
			'tnc_rep_read_private_collections' => [
				'display_name' => __('View private collections', 'tainacan'),
				'description' => __('Access to view and browse private collections', 'tainacan')
			],
			'tnc_rep_read_private_taxonomies' => [
				'display_name' => __('View private taxonomies', 'tainacan'),
				'description' => __('Access to private taxonomies information', 'tainacan')
			],
			'tnc_rep_read_private_metadata' => [
				'display_name' => __('View private repository metadata', 'tainacan'),
				'description' => __('Access to private metadata in repository level', 'tainacan')
			],
			'tnc_rep_read_private_filters' => [
				'display_name' => __('View private repository filters', 'tainacan'),
				'description' => __('Access to private filters in repository level', 'tainacan')
			],
			'tnc_rep_read_logs' => [
				'display_name' => __('View Logs', 'tainacan'),
				'description' => __('Access to activities logs. Note that activity logs might contain information on private collections, items and metadata.', 'tainacan')
			],
			'tnc_rep_bulk_edit' => [
				'display_name' => __('Bulk edit items', 'tainacan'),
				'description' => __('Access to the Bulk edit items feature.', 'tainacan')
			],
			/**
			 * Collections capabilities
			 * There is a set of this capabilities for each collection, suffixed with collection ID
			 */
			'manage_tainacan_collection_%d' => [
				'display_name' => __('Manage Collection', 'tainacan'),
				'description' => __('Manage all collection settings, items, metadata, filters, etc.', 'tainacan')
			],
			'tnc_col_%d_edit' => [
				'display_name' => __('Manage Collection settings', 'tainacan'),
				'description' => __('Manage collection settings, such as name and description', 'tainacan'),
				'dependencies' => [
					'upload_files'
				]
			],
			'tnc_col_%d_manage_metadata' => [
				'display_name' => __('Manage metadata', 'tainacan'),
				'description' => __('Create/edit/delete metadata in this collection', 'tainacan')
			],
			'tnc_col_%d_manage_filters' => [
				'display_name' => __('Manage filters', 'tainacan'),
				'description' => __('Create/edit/delete filters in this collection', 'tainacan')
			],
			'tnc_col_%d_read_private_metadata' => [
				'display_name' => __('View private metadata', 'tainacan'),
				'description' => __('Access private metadata in this collection', 'tainacan')
			],
			'tnc_col_%d_read_private_filters' => [
				'display_name' => __('View private filters', 'tainacan'),
				'description' => __('Access private filters in this collection', 'tainacan')
			],
			'tnc_col_%d_read_private_items' => [
				'display_name' => __('View private items', 'tainacan'),
				'description' => __('Access to view private items in this collection', 'tainacan')
			],
			'tnc_col_%d_edit_items' => [
				'display_name' => __('Edit items', 'tainacan'),
				'description' => __('Create and edit items in this collection', 'tainacan'),
				'dependencies' => [
					'upload_files'
				]
			],
			'tnc_col_%d_publish_items' => [
				'display_name' => __('Publish items', 'tainacan'),
				'description' => __('Publish items in this collection', 'tainacan'),
				'dependencies' => [
					'upload_files'
				]
			],
			'tnc_col_%d_edit_others_items' => [
				'display_name' => __('Edit others items', 'tainacan'),
				'description' => __('Edit items created by other users in this collection', 'tainacan'),
				'dependencies' => [
					'upload_files'
				]
			],
			'tnc_col_%d_edit_published_items' => [
				'display_name' => __('Edit published items', 'tainacan'),
				'description' => __('Edit items in this collection after they are published', 'tainacan'),
				'dependencies' => [
					'upload_files'
				]
			],
			
		];
		
		add_filter( 'user_has_cap', [$this, 'user_has_cap_filter'], 10, 4 );
		
	}
	
	public function get_all_caps() {
		return $this->capabilities;
	}
	
	public function get_all_caps_slugs() {
		return array_keys($this->capabilities);
	}
	
	public function user_has_cap_filter( $allcaps, $caps, $args, $user ) {
		
		$requested_cap = $args[0];
		
		foreach ( $caps as $cap ) {
		
			if ( array_key_exists($cap, $allcaps) && $allcaps[$cap] === true ) {
				continue;
			}
			
			if ( \strpos($cap, 'tnc_') === 0 ) {
				
				if ( $user->has_cap('manage_tainacan') ) {
					
					$allcaps = array_merge($allcaps, [ $cap => true ]);
					
				} elseif ( \strpos($cap, 'tnc_col_') === 0 ) {
					
					$col_id = preg_replace('/[a-z_]+(\d+)[a-z_]+?$/', '$1', $cap );
					
					if ( $user->has_cap('manage_tainacan_collection_' . $col_id) ) {
						$allcaps = array_merge($allcaps, [ $cap => true ]);
					}
					
				}
			} 
		}
		
		return $allcaps;
		
		
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
		
	
		
		
}
