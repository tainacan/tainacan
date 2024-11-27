<?php

namespace Tainacan;
use Tainacan\Repositories\Repository;

class Roles {

	private static $instance = null;
	private $capabilities = array();
	private $meta_caps;
	private $meta_section_caps;
	private $filters_caps;

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
		$this->meta_caps = (new \Tainacan\Entities\Metadatum())->get_capabilities();
		$this->meta_section_caps = (new \Tainacan\Entities\Metadata_Section())->get_capabilities();
		$this->filters_caps = (new \Tainacan\Entities\Filter())->get_capabilities();

		/**
		 * This needs to be done after 'init' is fired because translated strings are used.
		 * 
		 * @see https://make.wordpress.org/core/2024/10/21/i18n-improvements-6-7/
		 */
		add_action( 'init', array( $this, 'populate_tainacan_capabilities' ));

		add_filter( 'user_has_cap', [$this, 'user_has_cap_filter'], 10, 4 );
		add_filter( 'map_meta_cap', [$this, 'map_meta_cap'], 10, 4 );

		add_filter( 'gettext_with_context', array(&$this, 'translate_user_roles'), 10, 4 );

	}

	public function populate_tainacan_capabilities() {

		$this->capabilities = [
			'manage_tainacan' => [
				'display_name' => __('Manage Tainacan', 'tainacan'),
				'description' => __('Manage all Tainacan features and all Collections', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => []
			],
			'tnc_rep_edit_users' => [
				'display_name' => __('Manage Users', 'tainacan'),
				'description' => __('Manage users roles and permissions', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_edit_collections' => [
				'display_name' => __('Create Collections', 'tainacan'),
				'description' => __('Create new collections to the repository and edit its details', 'tainacan'),
				'dependencies' => [
					'upload_files'
				],
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_delete_collections' => [
				'display_name' => __('Delete Collections', 'tainacan'),
				'description' => __('Delete their own collections from the repository', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_edit_taxonomies' => [
				'display_name' => __('Create and edit taxonomies', 'tainacan'),
				'description' => __('Create new taxonomies and edit its terms', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_edit_others_taxonomies' => [
				'display_name' => __('Edit all Taxonomies', 'tainacan'),
				'description' => __('Edit all taxonomies and terms, including taxonomies created by other users', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_delete_taxonomies' => [
				'display_name' => __('Delete Taxonomies', 'tainacan'),
				'description' => __('Delete taxonomies', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_delete_others_taxonomies' => [
				'display_name' => __('Delete all Taxonomies', 'tainacan'),
				'description' => __('Delete all taxonomies and terms, including taxonomies created by other users', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_edit_metadata' => [
				'display_name' => __('Manage Repository Metadata', 'tainacan'),
				'description' => __('Create/edit metadata in repository level', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_edit_filters' => [
				'display_name' => __('Manage Repository Filters', 'tainacan'),
				'description' => __('Create/edit filters in repository level', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_delete_metadata' => [
				'display_name' => __('Delete Repository Metadata', 'tainacan'),
				'description' => __('Delete metadata in repository level', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_delete_filters' => [
				'display_name' => __('Delete Repository Filters', 'tainacan'),
				'description' => __('Delete filters in repository level', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_read_private_collections' => [
				'display_name' => __('View private collections', 'tainacan'),
				'description' => __('Access to view and browse private collections', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_read_private_taxonomies' => [
				'display_name' => __('View private taxonomies', 'tainacan'),
				'description' => __('Access to private taxonomies information', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_read_private_metadata' => [
				'display_name' => __('View private repository metadata', 'tainacan'),
				'description' => __('Access to private metadata in repository level', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_read_private_filters' => [
				'display_name' => __('View private repository filters', 'tainacan'),
				'description' => __('Access to private filters in repository level', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],
			'tnc_rep_read_logs' => [
				'display_name' => __('View Logs', 'tainacan'),
				'description' => __('Access to activities logs. Note that activity logs might contain information on private collections, items and metadata.', 'tainacan'),
				'scope' => 'repository',
				'supercaps' => [
					'manage_tainacan'
				]
			],

			/**
			 * Collections capabilities
			 * There is a set of this capabilities for each collection, where %d is collection ID
			 * If %d is "all" then the user will have this capability to all collections
			 */
			'manage_tainacan_collection_%d' => [
				'display_name' => __('Manage Collection', 'tainacan'),
				'description' => __('Manage all collection settings, items, metadata, filters, etc.', 'tainacan'),
				'scope' => 'collection',
				'dependencies' => [
					$this->meta_caps->read_private_posts,
					$this->meta_section_caps->read_private_posts,
					$this->filters_caps->read_private_posts
				],
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all'
				]
			],
			'tnc_col_%d_edit_users' => [
				'display_name' => __('Edit users permissions', 'tainacan'),
				'description' => __('Configure which roles and users have permission to perform actions in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_edit_users',
				]
			],
			'tnc_col_%d_bulk_edit' => [
				'display_name' => __('Bulk edit items', 'tainacan'),
				'description' => __('Access to the Bulk edit items feature.', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_bulk_edit',
				]
			],
			'tnc_col_%d_edit_metadata' => [
				'display_name' => __('Manage metadata', 'tainacan'),
				'description' => __('Create/edit metadata in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_edit_metadata',
				]
			],
			'tnc_col_%d_edit_metasection' => [
				'display_name' => __('Manage metadata sections', 'tainacan'),
				'description' => __('Create/edit metadata section in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_edit_metasection',
				]
			],
			'tnc_col_%d_edit_filters' => [
				'display_name' => __('Manage filters', 'tainacan'),
				'description' => __('Create/edit filters in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_edit_filters',
				]
			],
			'tnc_col_%d_delete_metadata' => [
				'display_name' => __('Delete metadata', 'tainacan'),
				'description' => __('Delete metadata in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_delete_metadata',
				]
			],
			'tnc_col_%d_delete_metasection' => [
				'display_name' => __('Delete metadata sections', 'tainacan'),
				'description' => __('Delete metadata section in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_delete_metasection',
				]
			],
			'tnc_col_%d_delete_filters' => [
				'display_name' => __('Delete filters', 'tainacan'),
				'description' => __('Delete filters in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_delete_filters',
				]
			],
			'tnc_col_%d_read_private_metadata' => [
				'display_name' => __('View private metadata', 'tainacan'),
				'description' => __('Access private metadata in this collection', 'tainacan'),
				'scope' => 'collection',
				'dependencies' => [
					$this->meta_caps->read_private_posts, // e.g.: 'read_private_tainacan-metadata'
				],
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_read_private_metadata',
				]
			],
			'tnc_col_%d_read_private_metasection' => [
				'display_name' => __('View private metadata sections', 'tainacan'),
				'description' => __('Access private metadata section in this collection', 'tainacan'),
				'scope' => 'collection',
				'dependencies' => [
					$this->meta_caps->read_private_posts, // e.g.: 'read_private_tainacan-metasection'
					$this->meta_section_caps->read_private_posts,
				],
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_read_private_metasection',
				]
			],
			'tnc_col_%d_read_private_filters' => [
				'display_name' => __('View private filters', 'tainacan'),
				'description' => __('Access private filters in this collection', 'tainacan'),
				'scope' => 'collection',
				'dependencies' => [
					$this->filters_caps->read_private_posts, // e.g.: 'read_private_tainacan-filters'
				],
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_read_private_filters',
				]
			],
			'tnc_col_%d_read_private_items' => [
				'display_name' => __('View private items', 'tainacan'),
				'description' => __('Access to view private items in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_read_private_items',
				]
			],
			'tnc_col_%d_edit_items' => [
				'display_name' => __('Edit items', 'tainacan'),
				'description' => __('Create and edit items in this collection', 'tainacan'),
				'dependencies' => [
					'upload_files'
				],
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_edit_items',
				]
			],
			'tnc_col_%d_publish_items' => [
				'display_name' => __('Publish items', 'tainacan'),
				'description' => __('Publish items in this collection', 'tainacan'),
				'dependencies' => [
					'upload_files'
				],
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_publish_items',
				]
			],
			'tnc_col_%d_edit_others_items' => [
				'display_name' => __('Edit others items', 'tainacan'),
				'description' => __('Edit items created by other users in this collection', 'tainacan'),
				'dependencies' => [
					'upload_files'
				],
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_edit_others_items',
				]
			],
			'tnc_col_%d_edit_published_items' => [
				'display_name' => __('Edit published items', 'tainacan'),
				'description' => __('Edit items in this collection after they are published', 'tainacan'),
				'dependencies' => [
					'upload_files'
				],
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_edit_published_items',
				]
			],
			'tnc_col_%d_delete_items' => [
				'display_name' => __('Delete items', 'tainacan'),
				'description' => __('Delete items in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_delete_items',
				]
			],
			'tnc_col_%d_delete_others_items' => [
				'display_name' => __('Delete others items', 'tainacan'),
				'description' => __('Delete items created by other users in this collection', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_delete_others_items',
				]
			],
			'tnc_col_%d_delete_published_items' => [
				'display_name' => __('Delete published items', 'tainacan'),
				'description' => __('Delete items in this collection after they are published', 'tainacan'),
				'scope' => 'collection',
				'supercaps' => [
					'manage_tainacan',
					'manage_tainacan_collection_all',
					'manage_tainacan_collection_%d',
					'tnc_col_all_delete_published_items',
				]
			],
		];
	} 

	/**
	 * Tainacan default roles
	 *
	 * @return array Tainacan roles
	 */
	public function get_tainacan_roles() {
		$tainacan_roles = [
			'tainacan-administrator' => [
				'slug' => 'tainacan-administrator',
				'display_name' => 'Tainacan Administrator',
				'caps' => [
					'manage_tainacan' => true,
					'read' => true
				]
			],
			'tainacan-editor' => [
				'slug' => 'tainacan-editor',
				'display_name' => 'Tainacan Editor',
				'caps' => [
					'tnc_rep_edit_collections' => true,
					'tnc_rep_delete_collections' => true,
					'tnc_rep_edit_taxonomies' => true,
					'tnc_rep_edit_others_taxonomies' => true,
					'tnc_rep_delete_taxonomies' => true,
					'tnc_rep_delete_others_taxonomies' => true,
					'tnc_rep_edit_metadata' => true,
					'tnc_rep_edit_filters' => true,
					'tnc_rep_delete_metadata' => true,
					'tnc_rep_delete_filters' => true,
					'tnc_rep_read_private_collections' => true,
					'tnc_rep_read_private_taxonomies' => true,
					'tnc_rep_read_private_metadata' => true,
					'tnc_rep_read_private_filters' => true,
					'tnc_rep_read_logs' => true,
					'manage_tainacan_collection_all' => true,
					'read' => true
				]
			],
			'tainacan-author' => [
				'slug' => 'tainacan-author',
				'display_name' => 'Tainacan Author',
				'caps' => [
					'tnc_rep_edit_collections' => true,
					'tnc_rep_edit_taxonomies' => true,
					'tnc_rep_read_private_collections' => true,
					'tnc_rep_read_private_taxonomies' => true,
					'tnc_rep_read_private_metadata' => true,
					'tnc_rep_read_private_filters' => true,
					'read' => true
				]
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

	public function get_all_caps() {
		return $this->capabilities;
	}

	public function get_collection_caps() {
		return array_filter( $this->get_all_caps(), function($el) { return $el['scope'] == 'collection'; } );
	}

	public function get_repository_caps() {
		return array_filter( $this->get_all_caps(), function($el) { return $el['scope'] == 'repository'; } );
	}

	public function get_all_caps_slugs() {
		return array_keys($this->get_all_caps());
	}

	public function get_collection_caps_slugs() {
		return array_keys($this->get_collection_caps());
	}

	public function get_repository_caps_slugs() {
		return array_keys($this->get_repository_caps());
	}

	public function init_default_roles() {

		foreach ($this->get_tainacan_roles() as $role) {
			$new_role = add_role(
				$role['slug'],
				$role['display_name'],
				$role['caps']
			);
		}

		$admin = get_role('administrator');
		$admin->add_cap('manage_tainacan');

		$editor = get_role('editor');
		$editor->add_cap('manage_tainacan');

	}

	/**
	 * Gets the capabilty generic name as present in
	 * Tainacan\Roles::capabilities
	 *
	 * For example: tnc_col_12_edit or tnc_col_all_edit will return tnc_col_%d_edit
	 *
	 * @param string $cap
	 * @return string Capability slug as in the keys of $this->capabilities
	 */
	public function get_cap_generic_name($cap) {
		$cap = preg_replace('/^(.+_)[0-9]+(_.+)?$/', '${1}%d${2}', $cap);
		$cap = preg_replace('/^(.+_)all(_.+)?$/', '${1}%d${2}', $cap);
		return $cap;
	}

	public function user_has_cap_filter( $allcaps, $caps, $args, $user ) {

		$requested_cap = $args[0];

		// Administrators will always be able to edit users
		if ( $requested_cap == 'tnc_rep_edit_users' ) {
			if ( array_key_exists('edit_users', $allcaps) && $allcaps['edit_users'] === true ) {
				$allcaps['tnc_rep_edit_users'] = true;
				return $allcaps;
			}
		}

		$collection_capabilities = tainacan_collections()->get_capabilities();

		foreach ( $caps as $cap ) {

			if ( isset($cap) && $cap !== false && array_key_exists($cap, $allcaps) && $allcaps[$cap] === true ) {
				continue;
			}

			if( in_array($requested_cap, [
					$this->meta_caps->read_private_posts,
					$this->meta_section_caps->read_private_posts,
					$this->filters_caps->read_private_posts]
				) && (
					$user->has_cap('manage_tainacan') ||
					$user->has_cap('manage_tainacan_collection_all')
				)
			) {
				$allcaps = array_merge($allcaps, [ $requested_cap => true ]);
			}

			if ( \strpos($cap, 'tnc_') === 0 ) {

				if ( $user->has_cap('manage_tainacan') ) {

					$allcaps = array_merge($allcaps, [ $cap => true ]);

				/**
				 * Handle checks for collection specific capabilities.
				 * Either tnc_col_* or tnc_rep_*_collections
				 */
				} elseif ( \strpos($cap, 'tnc_col_') === 0 || in_array( $cap, (array) $collection_capabilities ) ) {

					$check_all_collections_cap = false;
					$has_all_collections_cap   = false;


					/**
					 * We are only interested in checks for a specific collection.
					 * $args[2] will be set if this came from a meta cap of a specific collection ( e.g. current_user_can('tnc_rep_edit_collection', 3) ).
					 */
					if ( in_array( $cap, (array) $collection_capabilities ) && isset( $args[2] ) && is_numeric( $args[2] ) ) {
						$col_id = $args[2];
					/**
					 * Or we extract the collection id from the capability itself. Example: tnc_col_3_delete_items
					 */
					} else {
						$col_id = preg_replace('/[a-z_]+(\d+)[a-z_]+?$/', '$1', $cap );
						$check_all_collections_cap = true;
					}

					/**
					 * If there is no specific collection, do nothing.
					 */
					if ( ! is_numeric($col_id) ) {
						continue;
					}

					// In case of a tnc_col_* capability check,
					// Let's see if the user has the respective tnc_col_all_* capability
					if ( $check_all_collections_cap ) {
						$all_collections_cap     = preg_replace('/([a-z_]+)(\d+)([a-z_]+?)$/', '${1}all${3}', $cap );
						$has_all_collections_cap = $user->has_cap( $all_collections_cap );
					}

					if (
							$user->has_cap('manage_tainacan_collection_' . $col_id) ||
							$user->has_cap('manage_tainacan_collection_all') ||
							$has_all_collections_cap
						) {

						$allcaps = array_merge($allcaps, [ $cap => true ]);

						/**
						 * If a user is trying to edit a collection relying on the manage_tainacan_collection_* cap
						 * they will also need the edit_others_posts capability. But since it is 'manage_tainacan',
						 * we have to treat this here because this check will not get here since we are only handling
						 * caps that starts with tnc_
						 */
						if ( $collection_capabilities->edit_posts === $cap ) {
							$allcaps = array_merge($allcaps, [ $collection_capabilities->edit_others_posts => true ]);
						}

					} elseif ( \strpos($cap, 'tnc_col_') === 0 ) {
						// check if the user is the owner only when checking tnc_col_* capabilities
						$collection = tainacan_collections()->fetch( (int) $col_id );
						if ( $collection instanceof \Tainacan\Entities\Collection ) {
							if ( (int) $collection->get_author_id() == (int) $user->ID ) {
								$allcaps = array_merge($allcaps, [ $cap => true ]);
							}
						}
					}

				}
			}
		}

		return $allcaps;

	}


	public function add_dependencies($role, $cap) {
		// convert cap name to the name declared in the roles of this class. tnc_col_12_edit or tnc_col_all_edit should become tnc_col_%d_edit
		$cap = $this->get_cap_generic_name($cap);

		if ( isset( $this->capabilities[$cap] ) && isset( $this->capabilities[$cap]['dependencies'] ) ) {
			$role = \get_role($role);
			if ( ! $role instanceof \WP_Role ) {
				return;
			}
			foreach ( $this->capabilities[$cap]['dependencies'] as $dep ) {
				$role->add_cap($dep);
			}
		}

	}

	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		$meta_caps = new \Tainacan\Entities\Metadatum();
		$meta_caps = $meta_caps->get_capabilities();

		$meta_section_caps = new \Tainacan\Entities\Metadata_Section();
		$meta_section_caps = $meta_section_caps->get_capabilities();

		$filters_caps = new \Tainacan\Entities\Filter();
		$filters_caps = $filters_caps->get_capabilities();

		$edit_meta = [
			$meta_caps->edit_posts,
			$meta_caps->edit_others_posts,
			$meta_caps->publish_posts,
			$meta_caps->delete_posts,
			$meta_caps->delete_private_posts,
			$meta_caps->delete_published_posts,
			$meta_caps->delete_others_posts,
			$meta_caps->edit_private_posts,
			$meta_caps->edit_published_posts,
			$meta_caps->create_posts,
		];

		$read_private_meta = [
			$meta_caps->read_private_posts
		];

		$edit_filters = [
			$filters_caps->edit_posts,
			$filters_caps->edit_others_posts,
			$filters_caps->publish_posts,
			$filters_caps->delete_posts,
			$filters_caps->delete_private_posts,
			$filters_caps->delete_published_posts,
			$filters_caps->delete_others_posts,
			$filters_caps->edit_private_posts,
			$filters_caps->edit_published_posts,
			$filters_caps->create_posts,
		];

		$read_private_filters = [
			$filters_caps->read_private_posts
		];

		$edit_meta_section = [
			$meta_section_caps->edit_posts,
			$meta_section_caps->edit_others_posts,
			$meta_section_caps->publish_posts,
			$meta_section_caps->delete_posts,
			$meta_section_caps->delete_private_posts,
			$meta_section_caps->delete_published_posts,
			$meta_section_caps->delete_others_posts,
			$meta_section_caps->edit_private_posts,
			$meta_section_caps->edit_published_posts,
			$meta_section_caps->create_posts,
		];

		$read_private_meta_section = [
			$meta_section_caps->read_private_posts
		];

		if ( !is_array( $args ) || !array_key_exists( 0, $args ) ) {
			return $caps;
		}

		$object = $args[0];

		switch ($cap) {
			case 'edit_post':
			case 'delete_post':

				$action = \str_replace('_post', '', $cap);

				foreach ($caps as $i => $c) {

					// Handle edit metadata section
					if ( in_array($c, $edit_meta_section) ) {
						if (is_numeric($object)) {
							$object = tainacan_metadata_sections()->fetch ( (int) $object );
						}
						if ( $object instanceof \Tainacan\Entities\Metadata_Section ) {
							if ( is_numeric($object->get_collection_id()) ) {
								unset($caps[$i]);
								$caps[] = 'tnc_col_' . $object->get_collection_id() . '_' . $action. '_metasection';
							}
						}
					}

					// Handle edit metadata
					if ( in_array($c, $edit_meta) ) {
						if (is_numeric($object)) {
							$object = tainacan_metadata()->fetch ( (int) $object );
						}
						if ( $object instanceof \Tainacan\Entities\Metadatum ) {
							if ( $object->get_collection_id() == 'default' ) {
								unset($caps[$i]);
								$caps[] = 'tnc_rep_' . $action. '_metadata';
							} elseif ( is_numeric($object->get_collection_id()) ) {
								unset($caps[$i]);
								$caps[] = 'tnc_col_' . $object->get_collection_id() . '_' . $action. '_metadata';
							}
						}
					}


					// Handle edit filters
					if ( in_array($c, $edit_filters) ) {
						if (is_numeric($object)) {
							$object = tainacan_filters()->fetch ( (int) $object );
						}
						if ( $object instanceof \Tainacan\Entities\Filter ) {
							if ( $object->get_collection_id() == 'default' ) {
								unset($caps[$i]);
								$caps[] = 'tnc_rep_' . $action. '_filters';
							} elseif ( is_numeric($object->get_collection_id()) ) {
								unset($caps[$i]);
								$caps[] = 'tnc_col_' . $object->get_collection_id() . '_' . $action. '_filters';
							}
						}
					}

				}


				break;

			case 'read_post':

				foreach ($caps as $i => $c) {

					// Handle read private metadata section
					if ( in_array($c, $read_private_meta_section) ) {
						if (is_numeric($object)) {
							$object = tainacan_metadata_sections()->fetch ( (int) $object );
						}
						if ( $object instanceof \Tainacan\Entities\Metadata_Section ) {
							if ( is_numeric($object->get_collection_id()) ) {
								unset($caps[$i]);
								$caps[] = 'tnc_col_' . $object->get_collection_id() . '_read_private_metasection';
							}
						}
					}

					// Handle read private metadata
					if ( in_array($c, $read_private_meta) ) {
						if (is_numeric($object)) {
							$object = tainacan_metadata()->fetch ( (int) $object );
						}
						if ( $object instanceof \Tainacan\Entities\Metadatum ) {
							if ( $object->get_collection_id() == 'default' ) {
								unset($caps[$i]);
								$caps[] = 'tnc_rep_read_private_metadata';
							} elseif ( is_numeric($object->get_collection_id()) ) {
								unset($caps[$i]);
								$caps[] = 'tnc_col_' . $object->get_collection_id() . '_read_private_metadata';
							}
						}
					}

					// Handle read private filters
					if ( in_array($c, $read_private_filters) ) {
						if (is_numeric($object)) {
							$object = tainacan_filters()->fetch ( (int) $object );
						}
						if ( $object instanceof \Tainacan\Entities\Filter ) {
							if ( $object->get_collection_id() == 'default' ) {
								unset($caps[$i]);
								$caps[] = 'tnc_rep_read_private_filters';
							} elseif ( is_numeric($object->get_collection_id()) ) {
								unset($caps[$i]);
								$caps[] = 'tnc_col_' . $object->get_collection_id() . '_read_private_filters';
							}
						}
					}

				}


				break;

			default:
				# code...
				break;
		}

		return $caps;

	}


}
