<?php
/*
Plugin Name: Tainacan
Plugin URI: https://tainacan.org/
Description: Open source, powerful and flexible repository platform for WordPress. Manage and publish you digital collections as easily as publishing a post to your blog, while having all the tools of a professional repository platform.
Author: Tainacan.org
Version: 0.19.1
Requires at least: 5.0
Tested up to: 6.0
Requires PHP: 5.6
Stable tag: 0.19.1
Text Domain: tainacan
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

const TAINACAN_VERSION = '0.19.1';

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$TAINACAN_BASE_URL = plugins_url('', __FILE__);
const TAINACAN_BASE_DIR     = __DIR__;
const TAINACAN_API_DIR     = __DIR__ . '/classes/api/';
const TAINACAN_CLASSES_DIR = __DIR__ . '/classes/';
$TAINACAN_API_MAX_ITEMS_PER_PAGE = defined('TAINACAN_API_MAX_ITEMS_PER_PAGE') ? TAINACAN_API_MAX_ITEMS_PER_PAGE : 96;
require_once(TAINACAN_CLASSES_DIR . 'tainacan-creator.php');
require_once(TAINACAN_API_DIR     . 'tainacan-rest-creator.php');
require_once('migrations.php');

// DEV Interface, used for debugging
function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}

add_action( 'after_setup_theme', function() {
	add_image_size( 'tainacan-small', 40, 40, true );
	add_image_size( 'tainacan-medium', 275, 275, true );
	add_image_size( 'tainacan-medium-full', 205, 1500 );
	add_image_size( 'tainacan-large-full', 480, 860 );
} );

// This enables Tainacan media sizes in the admin interface, including Gutenberg blocks
add_filter( 'image_size_names_choose', function ( $sizes ) {
    return array_merge( $sizes, array(
        'tainacan-small' 		=> __( 'Tainacan small (40x40 - cropped)', 'tainacan' ),
		'tainacan-medium' 		=> __( 'Tainacan medium (275x275 - cropped)', 'tainacan' ),
		'tainacan-medium-full'	=> __( 'Tainacan medium full (205x1500 - not cropped)', 'tainacan' ),
		'tainacan-large-full'	=> __( 'Tainacan large full (480x860 - not cropped)', 'tainacan' )
    ) );
} );


add_action('init', ['Tainacan\Migrations', 'run_migrations']);

// _TODO_ Try to move this to the prepare_filters inside class-tainacan-rest-items-controller.php. Instead of a $query, we receive a $request object there.
// Won't work now because the prepare_meta() inside the class-tainacan-rest-controller cannot deal inner metaqueries	
// Checks if we have an option set to add core title and description to the repository-level advanced search
if ( defined('TAINACAN_ENABLE_CORE_METADATA_ON_ADVANCED_SEARCH') && true === TAINACAN_ENABLE_CORE_METADATA_ON_ADVANCED_SEARCH ) {
	function tainacan_enable_core_metadata_on_advanced_search($query) {

		// Check if there is a meta_query set
		$is_repository_level = $query->get('collection_id') === 'default' || empty($query->get('collection_id'));//!isset($request['collection_id']) || ( $request['collection_id'] === 'default' ) ? true : false;
		$meta_query = $query->get('meta_query');//isset( $request['metaquery'] ) ? $request['metaquery'] : [];

		if ( $is_repository_level && $meta_query && !empty($meta_query) && is_array($meta_query) ) {
			
			$tainacan_core_title_meta_query = [];
			$tainacan_core_description_meta_query = [];

			foreach( $meta_query as $meta_query_unit ) {
				
				// Finds a special key value, that should represent all core title or core description
				if (
					isset($meta_query_unit['key']) && 
					($meta_query_unit['key'] === 'tainacan_core_title' || $meta_query_unit['key'] === 'tainacan_core_description')
				) {
					// Gets every collection to build the OR query
					$collections = \Tainacan\Repositories\Collections::get_instance()->fetch([], 'OBJECT');//$this->collections_repository->fetch([], 'OBJECT');

					foreach ($collections as $collection) {
						if ($meta_query_unit['key'] === 'tainacan_core_title' ) {
							$title_meta = $collection->get_core_title_metadatum();

							// Builds inner meta_queries for each collection, using the same settings of the special one
							$tainacan_core_title_meta_query[] = array(
								'key' => $title_meta->get_id(),
								'compare' => $meta_query_unit['compare'],
								'value' => $meta_query_unit['value']
							);

						} elseif ($meta_query_unit['key'] === 'tainacan_core_description' ) {
							$description_meta = $collection->get_core_description_metadatum();

							// Builds inner meta_queries for each collection, using the same settings of the special one
							$tainacan_core_description_meta_query[] = array(
								'key' => $description_meta->get_id(),
								'compare' => $meta_query_unit['compare'],
								'value' => $meta_query_unit['value']
							);
							
						}
					}
				}
			}

			// May be done in the previous loop, doing here for safety
			foreach( $meta_query as $meta_query_unit_key => $meta_query_unit) {

				// After building the custom meta query, we don't need the special one anymore
				if ( isset($meta_query_unit['key']) && ( $meta_query_unit['key'] === 'tainacan_core_title' || $meta_query_unit['key'] === 'tainacan_core_description' ) )
					unset($meta_query[$meta_query_unit_key]);
			}

			// If we build a custom meta_query, lets add it to the real one
			if ( count($tainacan_core_title_meta_query) || count($tainacan_core_description_meta_query) ) {
				if ( count($tainacan_core_title_meta_query) ) {

					// Internally, the meta_query relation is OR, outside of this group is AND per default
					$tainacan_core_title_meta_query['relation'] = 'OR';
					$meta_query[] = $tainacan_core_title_meta_query;
				}
				if ( count($tainacan_core_description_meta_query) ) {

					// Internally, the meta_query relation is OR, outside of this group is AND per default
					$tainacan_core_description_meta_query['relation'] = 'OR';
					$meta_query[] = $tainacan_core_description_meta_query;
				}

				//$request['metaquery'] = $meta_query;
				$query->set('meta_query', $meta_query);
			}
		}
	}
	add_action( 'pre_get_posts', 'tainacan_enable_core_metadata_on_advanced_search' );
}

//https://core.trac.wordpress.org/ticket/23022
//https://core.trac.wordpress.org/ticket/23022#comment:13
add_filter( 'wp_untrash_post_status', function( $new_status, $post_id, $previous_status ) {
	return $previous_status;
}, 10, 3 );

/*
 *
 * Adds Edit links to Tainacan item and collection pages
 */
function tainacan_add_admin_bar_items ( WP_Admin_Bar $admin_bar ) {

	// No need to add this shortcuts on the admin
	if ( !is_admin() ) {

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
							'title' => __( 'Edit this item on Tainacan Admin', 'tainacan' )
						]
					) );
			
				}
			}
		}

		// In the collection and items list, we can also display links
		else if ( is_archive() ) {

			$collection = tainacan_get_collection();

			// There should exist a Tainacan collection and the user should have permission for edit it
			if ( $collection && $collection->can_edit() ) {

				$url = admin_url( '?page=tainacan_admin#/collections/' . $collection->get_ID() . '/settings' );

				$admin_bar->add_menu( array(
					'id'    => 'tainacan-collection-edition-link',
					'parent' => null,
					'group'  => null,
					'title' => __( 'Edit collection', 'tainacan' ),
					'href'  => $url,
					'meta' => [
						'title' => __( 'Edit this collection on Tainacan Admin', 'tainacan' )
					]
				) );

			// If no single collection is found, we may be in a collections list
			} else if ( is_post_type_archive('tainacan-collection') ) {

				$url = admin_url( '?page=tainacan_admin#/collections/' );

				$admin_bar->add_menu( array(
					'id'    => 'tainacan-collections-edition-link',
					'parent' => null,
					'group'  => null,
					'title' => __( 'Edit collections', 'tainacan' ),
					'href'  => $url,
					'meta' => [
						'title' => __( 'Edit the collections on Tainacan Admin', 'tainacan' )
					]
				) );
			}
		}
	}
}
add_action( 'admin_bar_menu', 'tainacan_add_admin_bar_items', 500 );

function wp_kses_tainacan($content, $context='tainacan_content') {
	$allowed_html = wp_kses_allowed_html($context);
	return wp_kses($content, $allowed_html);
}

add_filter('wp_kses_allowed_html', function($allowedposttags, $context) {
	switch ( $context ) {
		case 'tainacan_content':
			$post_allowed_html = wp_kses_allowed_html('post');
			return  array_merge(
				$post_allowed_html, 
				['iframe' => array(
					'src'             => true,
					'height'          => true,
					'width'           => true,
					'frameborder'     => true,
					'allowfullscreen' => true,
				)]
			);
		default:
			return $allowedposttags;
	}
}, 10, 2);