<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Renders the content of the related items block
 * 
 */
function tainacan_blocks_render_related_items_list( $block_attributes, $content ) {
    $template_mode = isset($block_attributes['templateMode']) ? $block_attributes['templateMode'] : false;
	$collection_id = isset($block_attributes['collectionId']) ? $block_attributes['collectionId'] : false;
        
	$is_dynamic = isset($block_attributes['isDynamic']) ? ($block_attributes['isDynamic'] === 'true' || $block_attributes['isDynamic'] == 1) : false;

	if ( $template_mode && $collection_id ) {
        // Checks if we are in the edit page or in the published
		$current_post = get_post();
		$collection_post_type_pattern = '/' . \Tainacan\Entities\Collection::$db_identifier_prefix . '\d+' . \Tainacan\Entities\Collection::$db_identifier_sufix . '/';

        if ( $current_post === NULL ) {
			return \Tainacan\Theme_Helper::get_instance()->get_tainacan_related_items_list($block_attributes);
		} else if ( $current_post->post_type !== false && preg_match($collection_post_type_pattern, $current_post->post_type) ) {
			$block_attributes['item_id'] = $current_post->ID;
			return \Tainacan\Theme_Helper::get_instance()->get_tainacan_related_items_list($block_attributes);
		}
    } else if ( $is_dynamic ) {
        return \Tainacan\Theme_Helper::get_instance()->get_tainacan_related_items_list($block_attributes);
    } else {
        return $content;
    }
}