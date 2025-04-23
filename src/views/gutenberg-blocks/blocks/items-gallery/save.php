<?php

/**
 * Renders the content of the items gallery block
 * using Tainacan template functions that create
 * a Swiper.js carousel and slider, with a PhotoSwipe.js 
 * lightbox
 */
function tainacan_blocks_render_items_gallery( $block_attributes, $content ) {
	$block_attributes['isBlock'] = true;
	
	return \Tainacan\Theme_Helper::get_instance()->get_tainacan_items_gallery($block_attributes);
}