<?php

/**
 * Renders the content of the item metadata
 * using Tainacan template functions
 */
function tainacan_blocks_render_item_metadata( $block_attributes, $content, $block ) {

    $is_dynamic = isset($block_attributes['isDynamic']) ? ($block_attributes['isDynamic'] === 'true' || $block_attributes['isDynamic'] == 1) : false;

    if ( $is_dynamic) {

        // Basic check, otherwise we don't have nothing to render here.
        $item_id = !empty($block->context['tainacan/itemId']) ? $block->context['tainacan/itemId'] : (isset($block_attributes['itemId']) ? $block_attributes['itemId'] : false);
        $collection_id = isset($block_attributes['collectionId']) ? $block_attributes['collectionId'] : false;
        $metadata = isset($block_attributes['metadata']) ? $block_attributes['metadata'] : [];
        $template_mode = isset($block_attributes['templateMode']) ? $block_attributes['templateMode'] : false;
        
        // Builds args from backend query
        $args = [
            'metadata' => array_map(function($metadatum) { return $metadatum['id']; }, $metadata)
        ];

        // Checks if we are in the edit page or in the published
        $current_post = get_post();
        
        if ( $template_mode && $collection_id ) {
            $collection_pt_pattern = '/' . \Tainacan\Entities\Collection::$db_identifier_prefix . '\d+' . \Tainacan\Entities\Collection::$db_identifier_sufix . '/';

            if ( $current_post === NULL )
                    return \Tainacan\Theme_Helper::get_instance()->get_tainacan_item_metadata_template($args, $collection_id );
            else if ( $current_post->post_type !== false && preg_match($collection_pt_pattern, $current_post->post_type) )
                return tainacan_get_the_metadata( $args, $current_post->ID );
            
        } else if ( $item_id ) {
            return tainacan_get_the_metadata( $args, $item_id );
        }

    } else {

        $inner_blocks = $block->inner_blocks;
        $inner_blocks_html = '';
        foreach ( $inner_blocks as $inner_block ) {
            $inner_blocks_html .= $inner_block->render();
        }
        return $inner_blocks_html;
    }
}
