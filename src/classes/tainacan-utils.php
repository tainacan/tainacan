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