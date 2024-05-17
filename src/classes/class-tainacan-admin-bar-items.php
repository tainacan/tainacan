<?php
namespace Tainacan;

class Admin_Bar_Items {
	
	public function __construct() {
		add_action( 'admin_bar_menu', array($this, 'add_admin_bar_items'), 500 );
        add_action( 'init', array($this, 'add_admin_bar_items_styles') );
	}

	/**
	 * Color to style the admin bar items
	 */
	function add_admin_bar_items_styles() {
		global $TAINACAN_BASE_URL;

        if ( is_user_logged_in() )
		    wp_enqueue_style( 'tainacan-admin-bar', $TAINACAN_BASE_URL . '/assets/css/tainacan-admin-bar.css', [], TAINACAN_VERSION );
	}

    /*
    *
    * Adds Edit links to Tainacan item and collection pages
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
                                'title' => __( 'Edit this item on Tainacan Admin', 'tainacan' )
                            ]
                        ) );
                    }

                } else {
                    $post = get_queried_object();
            
                    // Is it a taxonomy-post-type post?
                    if ( property_exists($post, 'post_type') && $post->post_type == Entities\Taxonomy::$post_type && current_user_can('edit_posts', $post->ID) ) {

                        $url = admin_url( '?page=tainacan_admin#/taxonomies/' . $post->ID );

                        $admin_bar->add_menu( array(
                            'id'    => 'tainacan-taxonomy-edition-link',
                            'parent' => null,
                            'group'  => null,
                            'title' => __( 'Edit taxonomy', 'tainacan' ),
                            'href'  => $url,
                            'meta' => [
                                'title' => __( 'Edit this taxonomy on Tainacan Admin', 'tainacan' )
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
                            $url = admin_url( '?page=tainacan_admin#/taxonomies/'  . $tax_id);

                            $admin_bar->add_menu( array(
                                'id'    => 'tainacan-taxonomy-edition-link',
                                'parent' => null,
                                'group'  => null,
                                'title' => __( 'Edit taxonomy', 'tainacan' ),
                                'href'  => $url,
                                'meta' => [
                                    'title' => __( 'Edit this taxonomy on Tainacan Admin', 'tainacan' )
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

                } else if ( is_post_type_archive('tainacan-taxonomy') ) {
                    
                    $url = admin_url( '?page=tainacan_admin#/taxonomies/' );

                    $admin_bar->add_menu( array(
                        'id'    => 'tainacan-taxonomies-edition-link',
                        'parent' => null,
                        'group'  => null,
                        'title' => __( 'Edit taxonomies', 'tainacan' ),
                        'href'  => $url,
                        'meta' => [
                            'title' => __( 'Edit the taxonomies on Tainacan Admin', 'tainacan' )
                        ]
                    ) );
                } else {

                    global $wp_query;
                    if ( $wp_query->get( 'tainacan_repository_archive' ) == 1 ) {
                        
                        $url = admin_url( '?page=tainacan_admin#/items/' );

                        $admin_bar->add_menu( array(
                            'id'    => 'tainacan-repository-items-edition-link',
                            'parent' => null,
                            'group'  => null,
                            'title' => __( 'Edit items', 'tainacan' ),
                            'href'  => $url,
                            'meta' => [
                                'title' => __( 'Edit the items on Tainacan Admin', 'tainacan' )
                            ]
                        ) );
                    }
                }
            } 
        }
    }
}