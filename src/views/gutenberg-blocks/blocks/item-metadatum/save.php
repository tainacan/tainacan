<?php

/**
 * Renders the content of the item metadata block
 * using Tainacan template functions
 */
function tainacan_blocks_render_item_metadatum( $block_attributes, $content, $block ) {
	
	// Basic check, otherwise we don't have nothing to render here.
	$item_id = !empty($block->context['tainacan/itemId']) ? $block->context['tainacan/itemId'] : (isset($block_attributes['itemId']) ? $block_attributes['itemId'] : false);
	$metadatum_id = isset($block_attributes['metadatumId']) ? $block_attributes['metadatumId'] : false;

	if ( !$item_id || !$metadatum_id )
		return '';

	$args = array(
		'metadata' => $metadatum_id,
		'before_title' => '<h3 class="wp-block-tainacan-item-metadatum__metadatum-label">',
		'after_title' => '</h3>',
		'before_value' => '<p class="wp-block-tainacan-item-metadatum__metadatum-value">',
		'after_value' => '</p>'
	);

	// Label heading level
	$label_level = (isset($block_attributes['labelLevel']) && is_numeric($block_attributes['labelLevel'])) ? $block_attributes['labelLevel'] : false;
	if ($label_level) {
		$args['before_title'] = '<h' . $label_level . ' class="wp-block-tainacan-item-metadatum__metadatum-label">';
		$args['after_title'] = '</h' . $label_level . '>';
	}

	// Classes from block and Text alignment
	$text_align = isset($block_attributes['textAlign']) ? $block_attributes['textAlign'] : false;
	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'metadata-type-$type' . ( $text_align ? (' has-text-align-' . $text_align) : '' ),
		)
	);
	$args['before'] = '<div ' . $wrapper_attributes . '>';
	$args['after'] = '</div>';

	return tainacan_get_the_metadata( $args, $item_id );
}