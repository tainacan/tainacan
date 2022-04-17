<?php

/**
 * Renders the content of the item metadata block
 * using Tainacan template functions
 */
function tainacan_blocks_render_item_metadatum( $block_attributes, $content, $block ) {
	
	$item_id = !empty($block->context['tainacan/itemId']) ? $block->context['tainacan/itemId'] : (isset($block_attributes['itemId']) ? $block_attributes['itemId'] : false);
	$metadatum_id = isset($block_attributes['metadatumId']) ? $block_attributes['metadatumId'] : false;

	if ( !$item_id || !$metadatum_id )
		return '';

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'metadata-type-$type'
		)
	);

	return tainacan_get_the_metadata(
		array(
			'metadata' => $metadatum_id,
			'before' => '<div ' . $wrapper_attributes . '>',
			'after' => '</div>'
		),
		$item_id
	);
}