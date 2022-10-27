<?php

/**
 * Renders the content of the item metadata sections
 * using Tainacan template functions
 */
function tainacan_blocks_render_metadata_sections( $block_attributes, $content, $block ) {

    $is_dynamic = isset($block_attributes['isDynamic']) ? ($block_attributes['isDynamic'] === 'true' || $block_attributes['isDynamic'] == 1) : false;

    if ( $is_dynamic) {

        // Basic check, otherwise we don't have nothing to render here.
        $item_id = !empty($block->context['tainacan/itemId']) ? $block->context['tainacan/itemId'] : (isset($block_attributes['itemId']) ? $block_attributes['itemId'] : false);
        $collection_id = isset($block_attributes['collectionId']) ? $block_attributes['collectionId'] : false;
        $template_mode = isset($block_attributes['templateMode']) ? $block_attributes['templateMode'] : false;
        
        // Builds args from backend query
        $args = [];

        // Classes from block and Text alignment
        $text_align = isset($block_attributes['textAlign']) ? $block_attributes['textAlign'] : false;
        $wrapper_attributes = get_block_wrapper_attributes(
            array(
                'class' => 'metadata-section-slug-$slug' . ( $text_align ? (' has-text-align-' . $text_align) : '' ),
            )
        );
        $args['before'] = '<section id="metadata-section-$id ' . $wrapper_attributes . '>';
        $args['after'] = '</section>';

        // Checks if we are in the edit page or in the published
        $current_post = get_post();
        
        if ( $template_mode && $collection_id ) {
            $collection_pt_pattern = '/' . \Tainacan\Entities\Collection::$db_identifier_prefix . '\d+' . \Tainacan\Entities\Collection::$db_identifier_sufix . '/';

            if ( $current_post === NULL )
                return \Tainacan\Theme_Helper::get_instance()->get_tainacan_item_metadata_sections_template( [], $collection_id );
            else if ( $current_post->post_type !== false && preg_match($collection_pt_pattern, $current_post->post_type) ) 
                return tainacan_get_the_metadata_sections( $args, $current_post->ID );
            
        } else if ( $item_id ) {
            return tainacan_get_the_metadata_sections( $args, $item_id );
        }

    } else {
        
        // Classes from block and Text alignment
        $text_align = isset($block_attributes['textAlign']) ? $block_attributes['textAlign'] : false;
        $wrapper_attributes = get_block_wrapper_attributes(
            array(
                'class' => ( $text_align ? (' has-text-align-' . $text_align) : '' ),
            )
        );

        // Gets inner blocks and wraps them with this parent wrapper
        $inner_blocks = $block->inner_blocks;
        $inner_blocks_html = '';
        foreach ( $inner_blocks as $inner_block ) {
            $inner_blocks_html .= $inner_block->render();
        }
        return '<div ' . $wrapper_attributes . '>' . $inner_blocks_html . '</div>';
    }
}