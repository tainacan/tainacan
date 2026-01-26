<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Handles WordPress admin bar items for Tainacan.
 *
 * Adds contextual edit links to the WordPress admin bar for Tainacan items,
 * collections, taxonomies, and terms when users have appropriate permissions.
 *
 * @since 0.1.0
 */
class Admin_Bar_Items {
	use \Tainacan\Traits\Singleton_Instance;
	
	/**
	 * Initializes the admin bar items functionality.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function init() {
		add_action( 'admin_bar_menu', array($this, 'add_admin_bar_items'), 500 );
		add_action( 'init', array($this, 'add_admin_bar_items_styles') );
	}

	/**
	 * Gets the CSS styles for admin bar items. (Too small to be a separate file)
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS content for admin bar items.
	 */
	private static function get_admin_bar_css() {
		return '/* Styles Tainacan links in the WordPress admin top bar */
		#wpadminbar .tainacan-admin-bar-link.tainacan-admin-bar-link--repository a,
		#wpadminbar .tainacan-admin-bar-link.tainacan-admin-bar-link--repository:hover a,
		#wpadminbar .tainacan-admin-bar-link.tainacan-admin-bar-link--repository:focus a {
			background-color: #1d3968 !important;
		}
		#wpadminbar .tainacan-admin-bar-link a,
		#wpadminbar .tainacan-admin-bar-link:hover a,
		#wpadminbar .tainacan-admin-bar-link:focus a {
			background-color: #187181 !important;
		}
		#wpadminbar .tainacan-admin-bar-link a::before,
		#wpadminbar .tainacan-admin-bar-link:hover a::before,
		#wpadminbar .tainacan-admin-bar-link:focus a::before {
			color: white !important;
			content: \'\\f464\';
			top: 2px;
		}
		#wpadminbar .tainacan-admin-bar-link:hover a,
		#wpadminbar .tainacan-admin-bar-link:focus a {
			color: white !important;
		}';
	}

	/**
	 * Adds inline styles for admin bar items.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_admin_bar_items_styles() {
		if ( is_user_logged_in() ) {
			// Register a minimal style handle and add inline CSS
			wp_register_style( 'tainacan-admin-bar-inline', false, array(), TAINACAN_VERSION );
			wp_enqueue_style( 'tainacan-admin-bar-inline' );
			wp_add_inline_style( 'tainacan-admin-bar-inline', self::get_admin_bar_css() );
		}
	}

	/**
	 * Adds contextual edit links to the WordPress admin bar.
	 *
	 * Adds edit links for Tainacan items, collections, taxonomies, and terms
	 * when users have appropriate permissions and are viewing relevant pages.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_Admin_Bar $admin_bar The WordPress admin bar object.
	 * @return void
	 */
	function add_admin_bar_items ( \WP_Admin_Bar $admin_bar ) {

		// No need to add this shortcuts on the admin
		if ( !is_admin() && is_user_logged_in() ) {

			// We should only do this in singular pages, as the items list also return the first item on loop
			if ( is_singular() ) {

				$item = tainacan_get_item();

				// There should exist a Tainacan item and the user should have permission for this
				if ( isset($item) && $item->can_edit() ) {
					
					$url = $item->get_edit_url();
				
					// The item edition link must be valid!
					if ( $url ) {
				
						$admin_bar->add_menu( array(
							'id'    => 'tainacan-item-edition-link',
							'parent' => null,
							'group'  => null,
							'title' => __( 'Edit item', 'tainacan' ),
							'href'  => $url,
							'meta' => [
								'title' => __( 'Edit this item on Tainacan Admin', 'tainacan' ),
								'class' => 'tainacan-admin-bar-link'
							]
						) );
					}

				} else {
					$post = get_queried_object();
			
					// Is it a taxonomy-post-type post?
					if ( property_exists($post, 'post_type') && $post->post_type == Entities\Taxonomy::$post_type && current_user_can('edit_posts', $post->ID) ) {

						$url = admin_url( 'admin.php?page=tainacan_admin#/taxonomies/' . $post->ID );

						$admin_bar->add_menu( array(
							'id'    => 'tainacan-taxonomy-edition-link',
							'parent' => null,
							'group'  => null,
							'title' => __( 'Edit taxonomy', 'tainacan' ),
							'href'  => $url,
							'meta' => [
								'title' => __( 'Edit this taxonomy on Tainacan Admin', 'tainacan' ),
								'class' => 'tainacan-admin-bar-link tainacan-admin-bar-link--repository'
							]
						) );
					}
				}
			}
			// In the term items list, display a link to the single taxonomy
			else if ( is_tax() ) {

				$term = get_queried_object();
				
				if ( isset($term->taxonomy) ) {
					$prefix = substr( $term->taxonomy, 0, strlen( Entities\Taxonomy::$db_identifier_prefix ) );

					if ( $prefix == Entities\Taxonomy::$db_identifier_prefix ) {
						
						$tax_id = \Tainacan\Repositories\Taxonomies::get_instance()->get_id_by_db_identifier($term->taxonomy);

						if ( $tax_id && current_user_can( 'edit_posts', $tax_id ) ) {
							$url = admin_url( 'admin.php?page=tainacan_admin#/taxonomies/'  . $tax_id);

							$admin_bar->add_menu( array(
								'id'    => 'tainacan-taxonomy-edition-link',
								'parent' => null,
								'group'  => null,
								'title' => __( 'Edit taxonomy', 'tainacan' ),
								'href'  => $url,
								'meta' => [
									'title' => __( 'Edit this taxonomy on Tainacan Admin', 'tainacan' ),
									'class' => 'tainacan-admin-bar-link tainacan-admin-bar-link--repository'
								]
							) );
						}
					}
				}
			}
			// In the collection and items list, we can also display links
			else if ( is_archive() ) {

				$collection = tainacan_get_collection();

				// There should exist a Tainacan collection and the user should have permission for edit it
				if ( $collection && $collection->can_edit() ) {

					$url = admin_url( 'admin.php?page=tainacan_admin#/collections/' . $collection->get_ID() . '/settings' );

					$admin_bar->add_menu( array(
						'id'    => 'tainacan-collection-edition-link',
						'parent' => null,
						'group'  => null,
						'title' => __( 'Edit collection', 'tainacan' ),
						'href'  => $url,
						'meta' => [
							'title' => __( 'Edit this collection on Tainacan Admin', 'tainacan' ),
							'class' => 'tainacan-admin-bar-link'
						]
					) );

				// If no single collection is found, we may be in a collections list
				} else if ( is_post_type_archive('tainacan-collection') ) {

					$url = admin_url( 'admin.php?page=tainacan_admin#/collections/' );

					$admin_bar->add_menu( array(
						'id'    => 'tainacan-collections-edition-link',
						'parent' => null,
						'group'  => null,
						'title' => __( 'Edit collections', 'tainacan' ),
						'href'  => $url,
						'meta' => [
							'title' => __( 'Edit the collections on Tainacan Admin', 'tainacan' ),
							'class' => 'tainacan-admin-bar-link'
						]
					) );

				} else if ( is_post_type_archive('tainacan-taxonomy') ) {
					
					$url = admin_url( 'admin.php?page=tainacan_admin#/taxonomies/' );

					$admin_bar->add_menu( array(
						'id'    => 'tainacan-taxonomies-edition-link',
						'parent' => null,
						'group'  => null,
						'title' => __( 'Edit taxonomies', 'tainacan' ),
						'href'  => $url,
						'meta' => [
							'title' => __( 'Edit the taxonomies on Tainacan Admin', 'tainacan' ),
							'class' => 'tainacan-admin-bar-link tainacan-admin-bar-link--repository'
						]
					) );
				} else {

					global $wp_query;
					if ( $wp_query->get( 'tainacan_repository_archive' ) == 1 ) {
						
						$url = admin_url( 'admin.php?page=tainacan_admin#/items/' );

						$admin_bar->add_menu( array(
							'id'    => 'tainacan-repository-items-edition-link',
							'parent' => null,
							'group'  => null,
							'title' => __( 'Edit items', 'tainacan' ),
							'href'  => $url,
							'meta' => [
								'title' => __( 'Edit the items on Tainacan Admin', 'tainacan' ),
								'class' => 'tainacan-admin-bar-link tainacan-admin-bar-link--repository'
							]
						) );
					}
				}
			} 
		}
	}
}