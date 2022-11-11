<?php

/**
 * Renders the content of the item gallery block
 * using Tainacan template functions that create
 * a Swiper.js carousel and slider, with a PhotoSwipe.js 
 * lightbox
 */
function tainacan_blocks_render_items_gallery( $block_attributes, $content ) {
	$block_attributes['isBlock'] = true;
	$template_mode = isset($block_attributes['templateMode']) ? $block_attributes['templateMode'] : false;
	$collection_id = isset($block_attributes['collectionId']) ? $block_attributes['collectionId'] : false;
        
	if ( $template_mode && $collection_id ) {
		// Checks if we are in the edit page or in the published
		$current_post = get_post();
		$collection_pt_pattern = '/' . \Tainacan\Entities\Collection::$db_identifier_prefix . '\d+' . \Tainacan\Entities\Collection::$db_identifier_sufix . '/';

        if ( $current_post === NULL ) {
			return \Tainacan\Theme_Helper::get_instance()->get_tainacan_item_gallery_template($block_attributes);
		} else if ( $current_post->post_type !== false && preg_match($collection_pt_pattern, $current_post->post_type) ) {
			$block_attributes['item_id'] = $current_post->ID;
			return \Tainacan\Theme_Helper::get_instance()->get_tainacan_item_gallery($block_attributes);
		}
	} else {
		return \Tainacan\Theme_Helper::get_instance()->get_tainacan_item_gallery($block_attributes);
	}
}