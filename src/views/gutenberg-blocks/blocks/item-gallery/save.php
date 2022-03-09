<?php

/**
 * Renders the content of the item gallery block
 * using Tainacan template functions that create
 * a Swiper.js carousel and slider, with a PhotoSwipe.js 
 * lightbox
 */
function tainacan_blocks_render_items_gallery( $block_attributes, $content ) {
	return \Tainacan\Theme_Helper::get_instance()->get_tainacan_item_gallery($block_attributes);
}