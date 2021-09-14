<?php
/**
 * This file gathers functions usefull for theme and plugin developers
 */


/**
 * Retrieves raw data sent to an API endpoint reading the php://input stream
 * @return Object PHP Raw Postdata
 */
function tainacan_get_api_postdata() {
	$postdata = file_get_contents("php://input");
	$post = json_decode($postdata);
	return $post;
}

/**
 * Retrieve if the post status is viewable
 * @return boollean
 */
if(!function_exists("is_post_status_viewable")) {
	function is_post_status_viewable( $post_status ) {
		if ( is_scalar( $post_status ) ) {
			$post_status = \get_post_status_object( $post_status );
			if ( ! $post_status ) {
				return false;
			}
		}
	 
		if (
			! \is_object( $post_status ) ||
			$post_status->internal ||
			$post_status->protected
		) {
			return false;
		}
	 
		return $post_status->publicly_queryable || ( $post_status->_builtin && $post_status->public );
	}
}


function tainacan_set_log_slug( $override, $slug, $post_ID, $post_status, $post_type, $post_parent ) {
    if ( 'tainacan-log' === $post_type ) {
        if ( $post_ID ) {
            return uniqid( $post_type . '-' . $post_ID );
        }
        return uniqid( $post_type . '-' );
    }
    return $override;
}
add_filter( 'pre_wp_unique_post_slug', 'tainacan_set_log_slug', 10, 6 );