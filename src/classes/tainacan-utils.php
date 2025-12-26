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
 * Determines whether a post status is viewable by visitors.
 *
 * Checks if a post status object or string represents a status that can be
 * viewed by non-logged-in users on the frontend.
 *
 * @since 0.1.0
 *
 * @param string|\WP_Post_Status $post_status Post status name or object.
 * @return bool True if the post status is viewable, false otherwise.
 */
if ( !function_exists("tainacan_is_post_status_viewable") ) {
	function tainacan_is_post_status_viewable( $post_status ) {
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
 * This functions checks if the tainacan_enable_dev_wp_interface constant is defined and true.
 * If this returns true, Tainacan post types will be displayed in the WP Admin interface.
 *
 * @return boolean
 */
function tainacan_enable_dev_wp_interface() {
    return defined('tainacan_enable_dev_wp_interface') && true === tainacan_enable_dev_wp_interface ? true : false;
}

/**
 * Custom wp_kses function for Tainacan content.
 *
 * Sanitizes content using WordPress kses with Tainacan-specific allowed HTML tags.
 * Extends the default 'post' context to include iframe elements for embedded content.
 *
 * @since 0.1.0
 *
 * @param string $content The content to sanitize.
 * @param string $context The kses context to use. Default 'tainacan_content'.
 * @return string Sanitized content.
 */
function tainacan_wp_kses($content, $context = 'tainacan_content') {
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
		case 'tainacan_menu_link':
			$post_allowed_html = wp_kses_allowed_html('post');
			return array_merge(
				$post_allowed_html,
				[
					'svg' => array(
						'xmlns'   => true,
						'width'   => true,
						'height'  => true,
						'viewbox' => true,
						'fill'    => true,
						'class'   => true,
						'role'    => true,
						'aria-hidden' => true,
					),
					'path' => array(
						'd'       => true,
						'fill'    => true,
						'stroke'  => true,
						'stroke-width' => true,
						'stroke-linecap' => true,
						'stroke-linejoin' => true,
					),
					'g' => array(
						'transform' => true,
						'fill'      => true,
					),
					'circle' => array(
						'cx' => true,
						'cy' => true,
						'r'  => true,
						'fill' => true,
					),
					'rect' => array(
						'x'      => true,
						'y'      => true,
						'width'  => true,
						'height' => true,
						'fill'   => true,
					)
				]
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