<?php

/**
 * Renders the content of the item gallery block
 * using Tainacan template functions that create
 * a Swiper.js carousel and slider, with a PhotoSwipe.js 
 * lightbox
 */
function tainacan_blocks_render_items_gallery( $block_attributes, $content ) {
	
	if ( !isset($block_attributes['itemId']) )
		return '';

	// Gets options from block attributes
	$item_id = $block_attributes['itemId'];
	$block_id = $block_attributes['blockId'];
	$layout_elements = isset($block_attributes['layoutElements']) ? $block_attributes['layoutElements'] : array('main' => true, 'thumbnails' => true);
	$media_sources = isset($block_attributes['mediaSources']) ? $block_attributes['mediaSources'] : array( 'document' => true, 'attachments' => true, 'metadata' => false);
	$hide_file_name_main = isset($block_attributes['hideFileNameMain']) ? $block_attributes['hideFileNameMain'] : true;
	$hide_file_caption_main = isset($block_attributes['hideFileCaptionMain']) ? $block_attributes['hideFileCaptionMain'] : false;
	$hide_file_description_main = isset($block_attributes['hideFileDescriptionMain']) ? $block_attributes['hideFileDescriptionMain'] : true;
	$hide_file_name_thumbnails = isset($block_attributes['hideFileNameThumbnails']) ? $block_attributes['hideFileNameThumbnails'] : true;
	$hide_file_caption_thumbnails = isset($block_attributes['hideFileCaptionThumbnails']) ? $block_attributes['hideFileCaptionThumbnails'] : true;
	$hide_file_description_thumbnails = isset($block_attributes['hideFileDescriptionThumbnails']) ? $block_attributes['hideFileDescriptionThumbnails'] : true;
	$hide_file_name_lightbox = isset($block_attributes['hideFileNameLightbox']) ? $block_attributes['hideFileNameLightbox'] : false;
	$hide_file_caption_lightbox = isset($block_attributes['hideFileCaptionLightbox']) ? $block_attributes['hideFileCaptionLightbox'] : false;
	$hide_file_description_lightbox = isset($block_attributes['hideFileDescriptionLightbox']) ? $block_attributes['hideFileDescriptionLightbox'] : false;
	$open_lightbox_on_click = isset($block_attributes['openLightboxOnClick']) ? $block_attributes['openLightboxOnClick'] : true;

	$media_items_main = array();
	$media_items_thumbnails = array();

	if ( isset($media_sources['attachments']) && ($media_sources['attachments'] === true || $media_sources['attachments'] == 'true') )
		$attachments = tainacan_get_the_attachments(null, $item_id);

	if ( isset($layout_elements['main']) && ($layout_elements['main'] === true || $layout_elements['main'] == 'true') ) {

		$class_slide_metadata = '';
		if ($hide_file_name_main)
			$class_slide_metadata .= ' hide-name';
		if ($hide_file_description_main)
			$class_slide_metadata .= ' hide-description';
		if ($hide_file_caption_main)
			$class_slide_metadata .= ' hide-caption';

		if ( isset($media_sources['document']) && ($media_sources['document'] === true || $media_sources['document'] == 'true') && !empty(tainacan_get_the_document($item_id)) ) {
			$is_document_type_attachment = tainacan_get_the_document_type($item_id) === 'attachment';
			$media_items_main[] =
				tainacan_get_the_media_component_slide(array(
					'after_slide_metadata' => (( !get_theme_mod( 'tainacan_single_item_hide_download_document', false ) && tainacan_the_item_document_download_link($item_id) != '' ) ?
													('<span class="tainacan-item-file-download">' . tainacan_the_item_document_download_link($item_id) . '</span>')
											: ''),
					'media_content' => tainacan_get_the_document($item_id),
					'media_content_full' => $is_document_type_attachment ? tainacan_get_the_document($item_id, 'full') : ('<div class="attachment-without-image">' . tainacan_get_the_document($item_id, 'full') . '</div>'),
					'media_title' => $is_document_type_attachment ? get_the_title(tainacan_get_the_document_raw($item_id)) : '',
					'media_description' => $is_document_type_attachment ? get_the_content(null, false, tainacan_get_the_document_raw($item_id)) : '',
					'media_caption' => $is_document_type_attachment ? wp_get_attachment_caption(tainacan_get_the_document_raw($item_id)) : '',
					'media_type' => tainacan_get_the_document_type($item_id),
					'class_slide_metadata' => $class_slide_metadata
				));
		}
		
		if ( isset($media_sources['attachments']) && ($media_sources['attachments'] === true || $media_sources['attachments'] == 'true') ) {
			foreach ( $attachments as $attachment ) {
				$media_items_main[] =
					tainacan_get_the_media_component_slide(array(
						'after_slide_metadata' => (( !get_theme_mod( 'tainacan_single_item_hide_download_document', false ) && tainacan_the_item_attachment_download_link($attachment->ID) != '' ) ?
														'<span class="tainacan-item-file-download">' . tainacan_the_item_attachment_download_link($attachment->ID) . '</span>'
												: ''),
						'media_content' => tainacan_get_attachment_as_html($attachment->ID, $item_id),
						'media_content_full' => wp_attachment_is('image', $attachment->ID) ? wp_get_attachment_image( $attachment->ID, 'full', false) : ('<div class="attachment-without-image tainacan-embed-container"><iframe id="tainacan-attachment-iframe" src="' . tainacan_get_attachment_html_url($attachment->ID) . '"></iframe></div>'),
						'media_title' => $attachment->post_title,
						'media_description' => $attachment->post_content,
						'media_caption' => $attachment->post_excerpt,
						'media_type' => $attachment->post_mime_type,
						'class_slide_metadata' => $class_slide_metadata
					));
			}
		}
	}
	
	if ( isset($layout_elements['thumbnails']) && ($layout_elements['thumbnails'] === true || $layout_elements['thumbnails'] == 'true') ) {

		$class_slide_metadata = '';
		if ($hide_file_name_thumbnails)
			$class_slide_metadata .= ' hide-name';
		if ($hide_file_description_thumbnails)
			$class_slide_metadata .= ' hide-description';
		if ($hide_file_caption_thumbnails)
			$class_slide_metadata .= ' hide-caption';

		if ( isset($media_sources['document']) && ($media_sources['document'] === true && $media_sources['document'] == 'true') && !empty(tainacan_get_the_document($item_id)) ) {
			$is_document_type_attachment = tainacan_get_the_document_type($item_id) === 'attachment';
			$media_items_thumbnails[] =
				tainacan_get_the_media_component_slide(array(
					'media_content' => get_the_post_thumbnail(null, 'tainacan-medium'),
					'media_content_full' => $is_document_type_attachment ? tainacan_get_the_document($item_id, 'full') : ('<div class="attachment-without-image">' . tainacan_get_the_document($item_id, 'full') . '</div>'),
					'media_title' => $is_document_type_attachment ? get_the_title(tainacan_get_the_document_raw($item_id)) : '',
					'media_description' => $is_document_type_attachment ? get_the_content(null, false, tainacan_get_the_document_raw($item_id)) : '',
					'media_caption' => $is_document_type_attachment ? wp_get_attachment_caption(tainacan_get_the_document_raw($item_id)) : '',
					'media_type' => tainacan_get_the_document_type($item_id),
					'class_slide_metadata' => $class_slide_metadata
				));			
		}

		if ( isset($media_sources['attachments']) && ($media_sources['attachments'] === true || $media_sources['attachments'] == 'true') ) {
			foreach ( $attachments as $attachment ) {
				$media_items_thumbnails[] = 
					tainacan_get_the_media_component_slide(array(
						'media_content' => wp_get_attachment_image( $attachment->ID, 'tainacan-medium', false ),
						'media_content_full' => wp_attachment_is('image', $attachment->ID) ? wp_get_attachment_image( $attachment->ID, 'full', false) : ('<div class="attachment-without-image tainacan-embed-container"><iframe id="tainacan-attachment-iframe" src="' . tainacan_get_attachment_html_url($attachment->ID) . '"></iframe></div>'),
						'media_title' => $attachment->post_title,
						'media_description' => $attachment->post_content,
						'media_caption' => $attachment->post_excerpt,
						'media_type' => $attachment->post_mime_type,
						'class_slide_metadata' => $class_slide_metadata
					));
			}
		}
	}
	
	$block_custom_css = '';
	
	// Text color. First we check for custom preset colors, then actual values
	$block_custom_css .= isset($block_attributes['textColor']) ? ('--tainacan-media-metadata-color:  var(--wp--preset--color--' . $block_attributes['textColor'] . ');') : '';
	$block_custom_css .= (isset($block_attributes['style']) && isset($block_attributes['style']['color']) && isset($block_attributes['style']['color']['text'])) ? ('--tainacan-media-metadata-color: ' . $block_attributes['style']['color']['text'] . ';') : '';
	
	// Background color. First we check for custom preset colors, then actual values
	$block_custom_css .= isset($block_attributes['backgroundColor']) ? ('--tainacan-media-background:  var(--wp--preset--color--' . $block_attributes['backgroundColor'] . ');') : '';
	$block_custom_css .= (isset($block_attributes['style']) && isset($block_attributes['style']['color']) && isset($block_attributes['style']['color']['background'])) ? ('--tainacan-media-background: ' . $block_attributes['style']['color']['background'] . ';') : '';

	// Link color, if enabled. Firts we check for custom preset colors, then actual values.
	$block_custom_css .= isset($block_attributes['linkColor']) ? ('--swiper-theme-color: var(--wp--preset--color--' . $block_attributes['linkColor'] . ');') : '';
	if (
		isset($block_attributes['style']) &&
		isset($block_attributes['style']['elements']) &&
		isset($block_attributes['style']['elements']['link']) &&
		isset($block_attributes['style']['elements']['link']['color']) &&
		isset($block_attributes['style']['elements']['link']['color']['text'])
	) {
		$link_color = $block_attributes['style']['elements']['link']['color']['text'];
		if ( strpos( $link_color, 'var:' ) !== false ) {
			$link_color = str_replace('|', '--', $link_color);
			$link_color = str_replace('var:', 'var(--wp--', $link_color) . ')';
		}
		$block_custom_css .= '--swiper-theme-color: ' . $link_color . ';';
	}
		
	// Other values are obtained directly from the attributes
	$block_custom_css .= (isset($block_attributes['arrowsSize']) && is_numeric($block_attributes['arrowsSize'])) ? ('--swiper-navigation-size: ' . $block_attributes['arrowsSize'] . 'px;') : '';
	$block_custom_css .= (isset($block_attributes['mainSliderHeight']) && is_numeric($block_attributes['mainSliderHeight'])) ? ('--tainacan-media-main-carousel-height: ' . $block_attributes['mainSliderHeight'] . 'vh;') : '';
	$block_custom_css .= (isset($block_attributes['mainSliderWidth']) && is_numeric($block_attributes['mainSliderWidth'])) ? ('--tainacan-media-main-carousel-width: ' . $block_attributes['mainSliderWidth'] . '%;') : '';
	$block_custom_css .= (isset($block_attributes['thumbnailsCarouselWidth']) && is_numeric($block_attributes['thumbnailsCarouselWidth'])) ? ('--tainacan-media-thumbs-carousel-width: ' . $block_attributes['thumbnailsCarouselWidth'] . '%;') : '';
	$block_custom_css .= (isset($block_attributes['thumbnailsCarouselItemSize']) && is_numeric($block_attributes['thumbnailsCarouselItemSize'])) ? ('--tainacan-media-thumbs-carousel-item-size: ' . $block_attributes['thumbnailsCarouselItemSize'] . 'px;') : '';
	// var_dump($block_attributes['style']['elements']);
	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'style' => $block_custom_css,
			'class' => 'tainacan-media-component'
		)
	);
	return tainacan_get_the_media_component(
		'tainacan-item-gallery-block_id-' . $block_id,
		(isset($layout_elements['thumbnails']) && ($layout_elements['thumbnails'] === true || $layout_elements['thumbnails'] == 'true')) ? $media_items_thumbnails : null,
		(isset($layout_elements['main']) && ($layout_elements['main'] === true || $layout_elements['main'] == 'true')) ? $media_items_main : null,
		array(
			'wrapper_attributes' => $wrapper_attributes,
			'class_main_div' => '',
			'class_thumbs_div' => '',
			'swiper_main_options' => (isset($layout_elements['main']) && ($layout_elements['main'] === true || $layout_elements['main'] == 'true')) ? array(
				'navigation' => array(
					'nextEl' => '.swiper-navigation-next_' . 'tainacan-item-gallery-block_id-' . $block_id . '-main',
					'prevEl' => '.swiper-navigation-prev_' . 'tainacan-item-gallery-block_id-' . $block_id . '-main',
					'preloadImages' => false,
					'lazy' => true
				)
			) : '',
			'swiper_thumbs_options' => (isset($layout_elements['thumbnails']) && ($layout_elements['thumbnails'] === true || $layout_elements['thumbnails'] == 'true') && (!isset($layout_elements['main']) || !($layout_elements['main'] === true || $layout_elements['main'] == 'true')) ) ? array(
				'navigation' => array(
					'nextEl' => '.swiper-navigation-next_' . 'tainacan-item-gallery-block_id-' . $block_id . '-thumbs',
					'prevEl' => '.swiper-navigation-prev_' . 'tainacan-item-gallery-block_id-' . $block_id . '-thumbs',
					'preloadImages' => false,
					'lazy' => true
				)
			) : '',
			'disable_lightbox' => !$open_lightbox_on_click,
		)
	);
}