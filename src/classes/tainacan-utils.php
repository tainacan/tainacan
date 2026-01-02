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
 * @deprecated Use wp_kses() with wp_kses_allowed_html() directly instead.
 *            Example: wp_kses($content, wp_kses_allowed_html('tainacan_content'))
 *
 * @param string $content The content to sanitize.
 * @param string $context The kses context to use. Default 'tainacan_content'.
 * @return string Sanitized content.
 */
function tainacan_wp_kses($content, $context = 'tainacan_content') {
	_deprecated_function(
		__FUNCTION__,
		'0.21.0',
		'wp_kses($content, wp_kses_allowed_html($context))'
	);
	$allowed_html = wp_kses_allowed_html($context);
	return wp_kses($content, $allowed_html);
}
/**
 * Adds CSS properties to the safe_style_css filter
 * These properties are needed for the media gallery component (e.g., .media-full-content)
 * 
 * @param array $styles Array of allowed CSS property names
 * @return array Modified array with additional CSS properties
 * @since 1.0.0
 */
function tainacan_get_default_allowed_styles($styles) {
	$additional_styles = ['display', 'position', 'visibility'];
	foreach ($additional_styles as $style) {
		if (!in_array($style, $styles, true)) {
			$styles[] = $style;
		}
	}
	return $styles;
}

/**
 * Returns SVG allowed HTML elements and attributes for wp_kses
 * 
 * @return array Array of SVG-related HTML elements and their allowed attributes
 */
function tainacan_get_svg_allowed_html() {
	return [
		'svg'      => array(
			'class'           => true,
			'aria-hidden'     => true,
			'aria-labelledby' => true,
			'role'            => true,
			'xmlns'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		),
		'g'       => array(
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'transform'       => true,
		),
		'title'   => array( 'title' => true ),
		'path'    => array(
			'd'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'transform'       => true,
		),
		'rect'    => array(
			'x'            => true,
			'y'            => true,
			'width'        => true,
			'height'       => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
			'rx'           => true,
			'ry'           => true,
		),
		'circle'  => array(
			'cx'           => true,
			'cy'           => true,
			'r'            => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'ellipse' => array(
			'cx'           => true,
			'cy'           => true,
			'rx'           => true,
			'ry'           => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'line'    => array(
			'x1'           => true,
			'x2'           => true,
			'y1'           => true,
			'y2'           => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'polyline' => array(
			'points'       => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'polygon'  => array(
			'points'       => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'text'     => array(
			'x'           => true,
			'y'           => true,
			'fill'        => true,
			'font-size'   => true,
			'font-family' => true,
			'text-anchor' => true,
		),
		'defs'     => array(),
		'style'    => array( 'type' => true ),
		'use'      => array(
			'xlink:href' => true,
			'href'       => true,
		),
	];
}

add_filter('wp_kses_allowed_html', function($allowedposttags, $context) {
	switch ( $context ) {
		case 'tainacan_content':
			$post_allowed_html = wp_kses_allowed_html('post');
			// Add data-module attribute to div elements
			// Note: style attribute (for div/img) and img width/height are already allowed in 'post' context
			// The safe_style_css filter (added globally above) ensures CSS properties like display, position, visibility are allowed
			if (isset($post_allowed_html['div'])) {
				$post_allowed_html['div']['data-module'] = true;
			} else {
				$post_allowed_html['div'] = array('data-module' => true);
			}
			// Add iframe support
			$post_allowed_html['iframe'] = array(
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
			);
			// Add SVG support (reusing shared SVG rules)
			return array_merge($post_allowed_html, tainacan_get_svg_allowed_html());
		case 'tainacan_menu_link':
			$post_allowed_html = wp_kses_allowed_html('post');
			return array_merge(
				$post_allowed_html,
				tainacan_get_svg_allowed_html()
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