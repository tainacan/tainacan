<?php

/**
 * Renders the content of the item metadata block
 * using Tainacan template functions
 */
function tainacan_blocks_render_geocoordinate_item_metadatum( $block_attributes, $content, $block ) {
	
	// Basic check, otherwise we don't have nothing to render here.
	$item_id = !empty($block->context['tainacan/itemId']) ? $block->context['tainacan/itemId'] : (isset($block_attributes['itemId']) ? $block_attributes['itemId'] : false);
	$metadatum_id = isset($block_attributes['metadatumId']) ? $block_attributes['metadatumId'] : false;
	$collection_id = isset($block_attributes['collectionId']) ? $block_attributes['collectionId'] : false;
	$data_source = isset($block_attributes['dataSource']) ? $block_attributes['dataSource'] : 'parent';
	$template_mode = isset($block_attributes['templateMode']) ? $block_attributes['templateMode'] : false;
	
	if ( !$metadatum_id )
		return '';
	
	// Builds args from backend query
	$args = array(
		'metadata' => $metadatum_id,
		'before_title' => '<h3 class="wp-block-tainacan-item-metadatum__metadatum-label">',
		'after_title' => '</h3>',
		'before_value' => '<p class="wp-block-tainacan-item-metadatum__metadatum-value">',
		'after_value' => '</p>'
	);

	// Classes from block and Text alignment
	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'metadata-type-$type',
		)
	);
	$args['before'] = '<div ' . $wrapper_attributes . '>';
	$args['after'] = '</div>';
	
	if ( $template_mode && $collection_id ) {
		// Checks if we are in the edit page or in the published
		$current_post = get_post();
		$collection_pt_pattern = '/' . \Tainacan\Entities\Collection::$db_identifier_prefix . '\d+' . \Tainacan\Entities\Collection::$db_identifier_sufix . '/';

		if ( $current_post === NULL )
			return '<div>Map Demo</div>';
		else if ( $current_post->post_type !== false && preg_match($collection_pt_pattern, $current_post->post_type) )  {
			return tainacan_get_the_metadata( $args, $current_post->ID );
		}
		
	} else if ( $item_id ) {
		return tainacan_get_the_metadata( $args, $item_id );
	}
}