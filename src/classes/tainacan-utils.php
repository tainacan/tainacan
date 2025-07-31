<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * This file gathers functions usefull for theme and plugin developers
 * as well as some global filters/hooks.
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
 * @return boolean
 */
if ( !function_exists("is_post_status_viewable") ) {
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

/**
 * DEV Interface utility, used for debugging.
 * This functions checks if the TNC_ENABLE_DEV_WP_INTERFACE constant is defined and true.
 * If this returns true, Tainacan post types will be displayed in the WP Admin interface.
 *
 * @return boolean
 */
function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}

/**
 * Custom wp_kses function for Tainacan.
 */
function wp_kses_tainacan($content, $context = 'tainacan_content') {
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

/**
 * Makes untrashed posts return to their previous status instead of 'draft'.
 * 
 * @see https://core.trac.wordpress.org/ticket/23022#comment:13
 */
add_filter( 'wp_untrash_post_status', function( $new_status, $post_id, $previous_status ) {
	return $previous_status;
}, 10, 3 );