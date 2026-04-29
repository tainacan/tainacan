<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Handles WordPress Command Palette integration for Tainacan.
 *
 * Registers navigation commands for Tainacan internal pages using the WordPress Command Palette API.
 * Commands are dynamically generated from the Tainacan admin menu structure.
 *
 * @since 1.0.2
 */
class Admin_Commands {
	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Root menu slug for all Tainacan admin pages.
	 *
	 * @var string
	 * @since 1.0.2
	 */
	private $tainacan_root_menu_slug = 'tainacan-root-menu';

	/**
	 * Menu slug for the "Others" menu collapse.
	 *
	 * @var string
	 * @since 1.0.2
	 */
	private $tainacan_other_links_slug = 'tainacan_other_links';

	/**
	 * Repository links slug (same as page_slug for the admin Vue component).
	 *
	 * @var string
	 * @since 1.0.2
	 */
	private $repository_links_slug = 'tainacan_admin';

	/**
	 * Collections links slug.
	 *
	 * @var string
	 * @since 1.0.2
	 */
	private $collections_links_slug = 'tainacan_collection_links';

	/**
	 * Initializes the admin commands functionality.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	private function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_command_palette_scripts' ) );
	}

	/**
	 * Enqueues scripts and localizes data for the command palette.
	 *
	 * Command Palette API is only available in WordPress 6.9+.
	 * This method checks the WordPress version before enqueuing scripts.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	function enqueue_command_palette_scripts() {
		global $TAINACAN_BASE_URL;

		// Only enqueue in admin area
		if ( ! is_admin() ) {
			return;
		}

		// Command Palette API is only available in WordPress 6.9+
		// Check WordPress version to prevent errors on older installations
		if ( version_compare( get_bloginfo( 'version' ), '6.9', '<' ) ) {
			return;
		}

		wp_enqueue_script(
			'tainacan-commands',
			$TAINACAN_BASE_URL . '/assets/js/tainacan_command_palette.js',
			[ 'wp-commands', 'wp-i18n', 'wp-element', 'wp-components' ],
			TAINACAN_VERSION,
			true
		);
		wp_set_script_translations( 'tainacan-commands', 'tainacan' );

		// Collect menu items for command palette
		$commands_data = $this->get_command_palette_menu_items();
		wp_localize_script( 'tainacan-commands', 'tainacan_commands', $commands_data );
	}

	/**
	 * Collects menu items from Tainacan root menu and other links for command palette.
	 *
	 * @return array Array of menu items with their labels, URLs, and children.
	 * @since 1.0.2
	 */
	private function get_command_palette_menu_items() {
		global $submenu;

		$commands = [];
		$admin_url = admin_url( 'admin.php' );

		// Get root menu items
		$tainacan_root_links = isset( $submenu[ $this->tainacan_root_menu_slug ] ) ? $submenu[ $this->tainacan_root_menu_slug ] : [];

		if ( count( $tainacan_root_links ) ) {
			foreach ( $tainacan_root_links as $tainacan_root_link ) {

				// Check user capability
				if ( ! current_user_can( $tainacan_root_link[1] ) ) {
					continue;
				}

				// Strip HTML from label (remove span tags and get text content)
				$parent_label = wp_strip_all_tags( $tainacan_root_link[0], true );

				// Skip if this is the "Others" menu slug (it's just a container)
				if ( isset( $tainacan_root_link[2] ) && $tainacan_root_link[2] === $this->tainacan_other_links_slug ) {
					
					// Process children of "Others" menu
					if ( isset( $submenu[ $this->tainacan_other_links_slug ] ) ) {

						foreach ( $submenu[ $this->tainacan_other_links_slug ] as $other_link ) {
							
							if ( ! current_user_can( $other_link[1] ) )
								continue;
							
							$other_label = wp_strip_all_tags( $other_link[0], true );
							$other_url = $this->build_menu_item_url( $other_link[2], $admin_url );
							
							if ( $other_url ) {
								$page_slug = isset( $other_link[2] ) ? $other_link[2] : '';
								// Generate unique name from hash route or page slug
								$name_slug = $this->generate_command_name_slug( $page_slug, $other_label );
								// Build hierarchical label: Tainacan > [Parent] > [Item]
								$hierarchical_label = $this->build_hierarchical_label( $other_label, $parent_label );
								$commands[] = [
									'name'  => 'tainacan/navigate-' . $name_slug,
									'label' => $hierarchical_label,
									'url'   => $other_url,
								];
							}
						}
					}
					continue;
				}

				// Check if this root link has children
				if ( isset( $tainacan_root_link[2] ) && isset( $submenu[ $tainacan_root_link[2] ] ) ) {
					// This is a parent menu with children
					foreach ( $submenu[ $tainacan_root_link[2] ] as $child_link ) {

						if ( ! current_user_can( $child_link[1] ) ) 
							continue;
						
						$child_label = wp_strip_all_tags( $child_link[0], true );
						$child_url = $this->build_menu_item_url( $child_link[2], $admin_url );

						if ( $child_url ) {
							$page_slug = isset( $child_link[2] ) ? $child_link[2] : '';
							// Generate unique name from hash route or page slug
							$name_slug = $this->generate_command_name_slug( $page_slug, $child_label );
							// Build hierarchical label based on parent type
							$hierarchical_label = $this->build_hierarchical_label( $child_label, $parent_label, $tainacan_root_link[2] );
							$commands[] = [
								'name'  => 'tainacan/navigate-' . $name_slug,
								'label' => $hierarchical_label,
								'url'   => $child_url,
							];
						}
					}

				} elseif ( isset( $tainacan_root_link[2] ) ) {

					// This is a standalone root link
					$url = $this->build_menu_item_url( $tainacan_root_link[2], $admin_url );
					
					if ( $url ) {
						$page_slug = isset( $tainacan_root_link[2] ) ? $tainacan_root_link[2] : '';
						// Generate unique name from hash route or page slug
						$name_slug = $this->generate_command_name_slug( $page_slug, $parent_label );
						// Build hierarchical label: Tainacan > [Item]
						$hierarchical_label = $this->build_hierarchical_label( $parent_label );
						$commands[] = [
							'name'  => 'tainacan/navigate-' . $name_slug,
							'label' => $hierarchical_label,
							'url'   => $url,
						];
					}
				}
			}
		}

		return $commands;
	}

	/**
	 * Builds a hierarchical label for command palette items.
	 *
	 * Format: "Tainacan > [Parent] > [Item]" or "Tainacan > [Item]"
	 *
	 * @param string      $item_label The label of the menu item.
	 * @param string|null $parent_label The label of the parent menu item. Default null.
	 * @param string|null $parent_slug The slug of the parent menu item. Default null.
	 * @return string The hierarchical label.
	 * @since 1.0.2
	 */
	private function build_hierarchical_label( $item_label, $parent_label = null, $parent_slug = null ) {
		$hierarchy = [ __( 'Tainacan', 'tainacan' ) ];

		// Add parent label if provided
		if ( ! empty( $parent_label ) ) {
			// Check if parent is "Repository" or "Collections" to use proper translation
			if ( $parent_slug === $this->repository_links_slug ) {
				$hierarchy[] = __( 'Repository', 'tainacan' );
			} elseif ( $parent_slug === $this->collections_links_slug ) {
				$hierarchy[] = __( 'Collections', 'tainacan' );
			} else {
				$hierarchy[] = $parent_label;
			}
		}

		// Add item label
		$hierarchy[] = $item_label;

		// Join with separator (same as WordPress uses: " > ")
		return implode( ' > ', $hierarchy );
	}

	/**
	 * Generates a unique command name slug from page slug or label.
	 *
	 * For hash routes (e.g., "tainacan_admin#/metadata"), uses the route part after #
	 * to create unique names. For regular pages, uses the page slug or label.
	 *
	 * @param string $page_slug The page slug from the menu item.
	 * @param string $fallback_label The label to use as fallback if page_slug is empty.
	 * @return string The sanitized command name slug.
	 * @since 1.0.2
	 */
	private function generate_command_name_slug( $page_slug, $fallback_label ) {
		if ( empty( $page_slug ) ) {
			return sanitize_key( str_replace( ' ', '-', strtolower( $fallback_label ) ) );
		}

		// Check if it's a hash route (contains #)
		if ( strpos( $page_slug, '#' ) !== false ) {
			// Extract the route part after #
			$hash_route = substr( $page_slug, strpos( $page_slug, '#' ) + 1 );
			
			// Remove query parameters if present (e.g., "my-items?authorid=1" -> "my-items")
			if ( strpos( $hash_route, '?' ) !== false ) {
				$hash_route = substr( $hash_route, 0, strpos( $hash_route, '?' ) );
			}
			
			// Remove leading slash and convert to slug
			$hash_route = ltrim( $hash_route, '/' );
			
			// Convert route to slug (e.g., "/metadata" -> "metadata", "/my-items" -> "my-items")
			if ( ! empty( $hash_route ) ) {
				return sanitize_key( str_replace( '/', '-', $hash_route ) );
			}
		}

		// For regular pages, use the page slug
		return sanitize_key( $page_slug );
	}

	/**
	 * Builds the URL for a menu item.
	 *
	 * @param string $page_slug The page slug from the menu item.
	 * @param string $admin_url The admin URL base.
	 * @return string|false The built URL or false if invalid.
	 * @since 1.0.2
	 */
	private function build_menu_item_url( $page_slug, $admin_url ) {
		if ( empty( $page_slug ) ) {
			return false;
		}

		// Check if it's a hash route (contains #)
		if ( strpos( $page_slug, '#' ) !== false ) {
			// It's already a hash route, just prepend admin.php?page=
			return $admin_url . '?page=' . $page_slug;
		}

		// Regular page, use add_query_arg
		return add_query_arg( 'page', $page_slug, $admin_url );
	}
}

