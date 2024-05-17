<?php

use \Tainacan\Entities;
use \Tainacan\Repositories;


/**
 * To be used inside The Loop
 *
 * Return the item metadata as a HTML string to be used as output.
 *
 * Each metadata is a label with the metadatum name and the value.
 *
 * If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
 * it returns all metadata
 *
 * @param array|string $args {
	 *     Optional. Array or string of arguments.
	 *
	 * 	   @type mixed		 $metadata					Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata
	 *     @type array		 $metadata__in				Array of metadata IDs or Slugs to be retrieved. Default none
	 *     @type array		 $metadata__not_in			Array of metadata IDs (slugs not accepted) to excluded. Default none
	 *     @type bool		 $exclude_title				Exclude the Core Title Metadata from result. Default false
	 *     @type bool		 $exclude_description		Exclude the Core Description Metadata from result. Default false
	 *     @type bool		 $exclude_core				Exclude Core Metadata (title and description) from result. Default false
	 *     @type bool        $hide_empty                Whether to hide or not metadata the item has no value to
	 *                                                  Default: true
	 *     @type string      $empty_value_message       Message string to display if $hide_empty is false and there is not metadata value. Default ''
	 *     @type string      $before                    String to be added before each metadata block
	 *                                                  Default '<div class="metadata-type-$type">' where $type is the metadata type slug
	 *     @type string      $after		                String to be added after each metadata block
	 *                                                  Default '</div>'
	 *     @type string      $before_title              String to be added before each metadata title
	 *                                                  Default '<h3>'
	 *     @type string      $after_title               String to be added after each metadata title
	 *                                                  Default '</h3>'
	 *     @type string      $before_value              String to be added before each metadata value
	 *                                                  Default '<p>'
	 *     @type string      $after_value               String to be added after each metadata value
	 *                                                  Default '</p>'
	 * }
 * 
 * @param int|string $item_id       (Optional) The item ID to retrive the metadatum as a HTML string to be used as output. Default is the global $post
 * 
 * 
 * @return string        The HTML output
 */
function tainacan_get_the_metadata($args = array(), $item_id = 0) {
	
	$item = tainacan_get_item( $item_id );

	if ($item instanceof \Tainacan\Entities\Item) {
		return $item->get_metadata_as_html($args);
	}

	return '';

}

function tainacan_the_metadata($args = array()) {
	echo tainacan_get_the_metadata($args);
}

/**
 * To be used inside The Loop
 *
 * Return the item document as a HTML string to be used as output.
 *
 * @param int|string $item_id   (Optional) The item ID. Default is the global $post
 * @param string $img_size      (Optional) The image size, in case of an imagen document. Default is 'large'
 *
 * @return string        The HTML output
 */
function tainacan_get_the_document($item_id = 0, $img_size = 'large') {
	$item = tainacan_get_item($item_id);

	if (!$item)
		return;

	return apply_filters('tainacan-get-the-document', $item->get_document_as_html($item_id, $img_size), $item);
}

/**
 * To be used inside The Loop
 *
 * Return the item document in raw form (ID if an Attachment, textual content if URL or Text)
 *
 * @param int|string $item_id   (Optional) The item ID. Default is the global $post
 *
 * @return string        The raw output
 */
function tainacan_get_the_document_raw($item_id = 0) {
	$item = tainacan_get_item($item_id);

	if (!$item)
		return;

	return apply_filters('tainacan_get_the_document_raw', $item->get_document($item_id), $item);
}

function tainacan_get_the_item_document_url($item_id = 0) {
	$item = tainacan_get_item($item_id);

	if (!$item)
		return;

	return apply_filters('tainacan_get_the_item_document_url', $item->get_document_download_url(), $item);
}

function tainacan_get_the_document_type($item_id = 0) {
	$item = tainacan_get_item($item_id);

	if (!$item)
		return;

	return apply_filters('tainacan_get_the_document_type', $item->get_document_type(), $item);
}

function tainacan_the_item_document_download_link($item_id = 0) {
	$item = tainacan_get_item($item_id);

	if (!$item)
		return;

	$link = $item->get_document_download_url();
	
	if (!$link || $item->get_document_type() == 'text' || $item->get_document_type() == 'url')
		return;

	return '<a name="' . __('Download the item document', 'tainacan') . '" download="'. esc_url($link) . '" href="' . esc_url($link) . '" target="_blank">' . __('Download', 'tainacan') . '</a>';
}


function tainacan_the_item_attachment_download_link($attachment_id) {

	if ( !$attachment_id || !wp_get_attachment_url($attachment_id) )
		return;

	$link = wp_get_attachment_url($attachment_id);

	return '<a name="' . __('Download the item attachment', 'tainacan') . '" download="'. esc_url($link) . '" href="' . esc_url($link) . '">' . __('Download', 'tainacan') . '</a>';
}

function tainacan_the_document() {
	echo tainacan_get_the_document();
}

/**
 * To be used inside The Loop
 * 
 * echoes HTML display-ready version of an attachment
 */
function tainacan_get_single_attachment_as_html($attachment_id, $item_id = 0, $img_size = 'large') {
	echo tainacan_get_attachment_as_html($attachment_id, $item_id, $img_size);
}

/**
 * Return HTML display-ready version of an attachment
 */
function tainacan_get_attachment_as_html($attachment_id, $item_id = 0, $img_size = 'large') {

	$item = tainacan_get_item($item_id);

	if (!$attachment_id) {
		return '';
	}

	return $item->get_attachment_as_html($attachment_id, $img_size);
}

/**
 * To be used inside The Loop
 *
 * Check whether the current item has a document or not
 *
 * @return bool True if item has document, false if it does not
 */
function tainacan_has_document() {

	$document = tainacan_get_the_document();

	return ! empty($document);

}

/**
 * When visiting a collection archive or single, returns the current collection id
 *
 * @uses get_post_type() WordPress function via Theme Helper, which looks for the global $wp_query variable
 */
function tainacan_get_collection_id() {
	return \Tainacan\Theme_Helper::get_instance()->tainacan_get_collection_id();
}

/**
 * When visiting a collection archive or single, returns the current collection object
 *
 * @uses tainacan_get_collection_id()
 * @return \Tainacan\Entities\Collection | false
 */
function tainacan_get_collection($args = []) {
	return \Tainacan\Theme_Helper::get_instance()->tainacan_get_collection($args);
}

/**
 * When visiting a collection archive or single, returns the collection name
 *
 * @return string
 */
function tainacan_get_the_collection_name() {
	$collection = tainacan_get_collection();
	$name = '';
	if ( $collection ) {
		$name = $collection->get_name();
	}
	return apply_filters('tainacan-get-collection-name', esc_html($name), $collection);
}					

/**
 * When visiting an item single page containing a search query, returns the previous and next items
 *
 * @return array containing next and previous items with basic url, title and thumbnail information
 */
function tainacan_get_adjacent_items() {
	if ( is_single() ) {
		return \Tainacan\Theme_Helper::get_instance()->get_adjacent_items();
	}
	return false;
}


/**
 * When visiting a collection archive or single, prints the collection name
 *
 * @return void
 */
function tainacan_the_collection_name() {
	echo esc_html(tainacan_get_the_collection_name());
}

/**
 * When visiting a collection archive or single, returns the collection description
 *
 * @return string
 */
function tainacan_get_the_collection_description() {
	$collection = tainacan_get_collection();
	$description = '';
	if ( $collection ) {
		$description = $collection->get_description();
	}
	return apply_filters('tainacan-get-collection-description', esc_html($description), $collection);
}

/**
 * When visiting a collection archive or single, prints the collection description
 *
 * @return void
 */
function tainacan_the_collection_description() {
	echo esc_html(tainacan_get_the_collection_description());
}

/**
 * Tainacan Gallery component, used to render document, attachments and other files
 *
 * @return string
 */
function tainacan_the_media_component($media_id, $media_items_thumbs, $media_items_main, $args) {
	echo tainacan_get_the_media_component($media_id, $media_items_thumbs, $media_items_main, $args);
}


/**
 * Tainacan Media Gallery component, used to render document, attachments and other files
 * 
 * @param string       $media_id               ID to be added to the gallery div. If both main and thumbnail items are passed, each div ID will be posfixed with '-main' or '-thumbs'.
 * @param array        $media_items_thumbs     Array of media items to be rendered inside smaller the carousel. Default to empty array
 * @param array        $media_items_main       Array of media items to be rendered inside main, bigger the carousel. Default to empty array
 * @param array|string $args {
 *   Optional. Array of arguments.
 * 	   @type string      wrapper_attributes       		String containing attrs (style, class) for the wrapper div. If used, remember to pass class="tainacan-media-component"
 *     @type string      before_main_div          		String to be added before the main gallery div
 *     @type string      after_main_div           		String to be added after the main gallery div
 *     @type string      before_thumbs_div        		String to be added before the thumbs gallery div
 *     @type string      after_thumbs_div         		String to be added after the thumbs gallery div
 *     @type string      before_main_ul           		String to be added before the main gallery ul
 *     @type string      after_main_ul            		String to be added after the main gallery ul
 *     @type string      before_thumbs_ul         		String to be added before the thumbs gallery ul
 *     @type string      after_thumbs_ul          		String to be added after the thumbs gallery ul
 *     @type string      class_main_div           		Class to be added to the main gallery div
 *     @type string      class_main_ul	          		Class to be added to the main gallery ul
 *     @type string      class_main_li            		Class to be added to the main gallery li
 *     @type string      class_thumbs_div         		Class to be added to the thumbs gallery div
 *     @type string      class_thumbs_ul          		Class to be added to the thumbs gallery ul
 *     @type string      class_thumbs_li          		Class to be added to the thumbs gallery li
 *     @type array       swiper_main_options      		Object with SwiperJS options for the main gallery
 *     @type array       swiper_thumbs_options    		Object with SwiperJS options for the thumb gallery
 * 	   @type bool		 swiper_arrows_as_svg	  		Uses SVG icons insetead of Tainacan Icon font for navigation arrows
 *     @type string      swiper_arrow_next_custom_svg 	Custom SVG icon to render next navigation arrow
 *     @type string      swiper_arrow_prev_custom_svg 	Custom SVG icon to render previous navigation arrow
 *     @type bool 		 disable_lightbox				Do not open Photoswiper layer on click
 *     @type bool        show_share_button        		Shows share button on lightbox
 *	   @type bool	 	 lightbox_has_light_background  Show a light background instead of dark in the lightbox 
 * }
 * @return string
 */
	
function tainacan_get_the_media_component(
	$media_id = 'tainacan-media-component',
	$media_items_thumbs = array(),
	$media_items_main = array(),
	$args = array()
) {
	global $TAINACAN_BASE_URL;

	$args = array_merge(array(
		'wrapper_attributes' => 'class="tainacan-media-component"',
		'before_main_div' => '',
		'after_main_div' => '',
		'before_thumbs_div' => '',
		'after_thumbs_div' => '',
		'before_main_ul' => '',
		'after_main_ul' => '',
		'before_thumbs_ul' => '',
		'after_thumbs_ul' => '',
		'class_main_div' => '',
		'class_main_ul' => '',
		'class_main_li' => '',
		'class_thumbs_div' => '',
		'class_thumbs_ul' => '',
		'class_thumbs_li' => '',
		'swiper_main_options' => [],
		'swiper_thumbs_options' => [],
		'swiper_arrows_as_svg' => false,
		'swiper_arrow_next_custom_svg' => '',
		'swiper_arrow_prev_custom_svg' => '',
		'disable_lightbox' => false,
		'show_share_button' => false,
		'lightbox_has_light_background' => false
	), $args);

	$args['has_media_main'] = $media_items_main && is_array($media_items_main) && count($media_items_main);
	$args['has_media_thumbs'] = $media_items_thumbs && is_array($media_items_thumbs) && count($media_items_thumbs);
	$args['media_main_id'] = $media_id . '-main';
	$args['media_thumbs_id'] = $media_id . '-thumbs';
	$args['media_id'] = $media_id;

	if (!function_exists('tainacan_get_default_allowed_styles')) {
		function tainacan_get_default_allowed_styles ( $styles ) {
			$styles[] = 'display';
			$styles[] = 'position';		// Adding position to this list will not be necessary anymore from WP 6.2 on... but lets keep for backwards.
			$styles[] = 'visibility';
			return $styles;
		}
	}
	$allowed_html = array(
		'svg' => array(
			'xmlns' => true,
			'fill' => true,
			'viewbox' => true,
			'role' => true,
			'aria-hidden' => true,
			'focusable' => true,
			'width' => true,
			'height' => true,
		),
		'path' => array(
			'd' => true,
			'fill' => true,
		)
	);
	add_filter( 'safe_style_css', 'tainacan_get_default_allowed_styles');

	ob_start();
	if ( $args['has_media_main'] || $args['has_media_thumbs'] ) :
	
		wp_enqueue_style( 'tainacan-media-component', $TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-item-gallery.css', array(), TAINACAN_VERSION);
		?>

		<script>
			try {
				tainacan_plugin = (typeof tainacan_plugin != undefined) ? tainacan_plugin : {};
			} catch(err) {
				tainacan_plugin = {};
			}
			tainacan_plugin.tainacan_media_components = (typeof tainacan_plugin.tainacan_media_components != "undefined") ? tainacan_plugin.tainacan_media_components : {};
			tainacan_plugin.tainacan_media_components['<?php echo esc_attr($args['media_id']) ?>'] = <?php echo json_encode($args) ?>;
		</script>	

		<div id="<?php echo esc_attr($media_id) ?>" data-module="item-gallery" <?php echo wp_kses_post($args['wrapper_attributes']); ?>>
			<?php if ( $args['has_media_main'] ) : ?>
				
				<!-- Slider main container -->
				<?php echo wp_kses_post($args['before_main_div']) ?>
				<div id="<?php echo esc_attr($args['media_main_id']) ?>" class="tainacan-media-component__swiper-main swiper <?php echo esc_attr($args['class_main_div']) ?>">

					<!-- Additional required wrapper -->
					<?php echo wp_kses_post($args['before_main_ul']) ?>
					<ul class="swiper-wrapper <?php echo esc_attr($args['class_main_ul']) ?>">
						<?php foreach($media_items_main as $media_item) { ?>
							<li class="swiper-slide <?php echo esc_attr($args['class_main_li']) ?>">
								<?php 
									echo wp_kses_tainacan($media_item);
								 ?>
							</li>
						<?php }; ?>
					</ul>
					<?php echo wp_kses_post($args['before_main_ul']) ?>

					<?php if ( $args['swiper_main_options'] && isset($args['swiper_main_options']['pagination']) ) : ?>
						<!-- If we need pagination -->
						<div class="swiper-pagination swiper-pagination_<?php echo esc_attr($args['media_main_id']) ?>"></div>
					<?php endif; ?>

					<?php if ( $args['swiper_main_options'] && isset($args['swiper_main_options']['navigation']) ) : ?>

						<!-- If we need navigation buttons -->
						<div class="swiper-button-prev swiper-navigation-prev_<?php echo esc_attr($args['media_main_id']) ?> <?php echo ($args['swiper_arrows_as_svg'] ? 'swiper-button-has-svg' : '' ) ?>">
							<?php if ( $args['swiper_arrows_as_svg'] ): ?>
								<?php if ( $args['swiper_arrow_prev_custom_svg'] ): ?>
									<?php echo wp_kses($args['swiper_arrow_prev_custom_svg'], $allowed_html); ?>
								<?php else: ?>
									<svg width="var(--swiper-navigation-size)" height="var(--swiper-navigation-size)" viewBox="0 0 24 24">
										<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
										<path d="M0 0h24v24H0z" fill="none"/>
									</svg>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="swiper-button-next swiper-navigation-next_<?php echo esc_attr($args['media_main_id']) ?> <?php echo ($args['swiper_arrows_as_svg'] ? 'swiper-button-has-svg' : '' ) ?>">
							<?php if ( $args['swiper_arrows_as_svg'] ): ?>	
								<?php if ( $args['swiper_arrow_next_custom_svg'] ): ?>
									<?php echo wp_kses($args['swiper_arrow_next_custom_svg'], $allowed_html); ?>
								<?php else: ?>
									<svg width="42" height="42" viewBox="0 0 24 24">
										<path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
										<path d="M0 0h24v24H0z" fill="none"/>
									</svg>
								<?php endif; ?>
							<?php endif; ?>
						</div>

					<?php endif; ?>
				</div>
				<?php echo wp_kses_post($args['after_main_div']) ?>
			<?php endif; ?>

			<?php if ( $args['has_media_thumbs'] ) : ?>

				<!-- Slider thumbs container -->
				<?php echo wp_kses_post($args['before_thumbs_div']) ?>
				<div id="<?php echo esc_attr($args['media_thumbs_id']) ?>" class="tainacan-media-component__swiper-thumbs swiper <?php echo esc_attr($args['class_thumbs_div']) ?>">

					<!-- Additional required wrapper -->
					<?php echo wp_kses_post($args['before_thumbs_ul']) ?>
					<ul class="swiper-wrapper <?php echo esc_attr($args['class_thumbs_ul']) ?>">
						<?php foreach($media_items_thumbs as $media_item) { ?>
							<li class="swiper-slide <?php echo esc_attr($args['class_thumbs_li']) ?>">
								<?php echo wp_kses_tainacan($media_item); ?>
							</li>
						<?php }; ?>
					</ul>
					<?php echo wp_kses_post($args['before_thumbs_ul']) ?>

					<?php if ( $args['swiper_thumbs_options'] && isset($args['swiper_thumbs_options']['pagination']) ) : ?>
						<!-- If we need pagination -->
						<div class="swiper-pagination swiper-pagination_<?php echo esc_attr($args['media_thumbs_id']) ?>"></div>
					<?php endif; ?>

					<?php if ( $args['swiper_thumbs_options'] && isset($args['swiper_thumbs_options']['navigation']) ) : ?>
						<!-- If we need navigation buttons -->

						<div class="swiper-button-prev swiper-navigation-prev_<?php echo esc_attr($args['media_thumbs_id']) ?> <?php echo ($args['swiper_arrows_as_svg'] ? 'swiper-button-has-svg' : '' ) ?>">
							<?php if ( $args['swiper_arrows_as_svg'] ): ?>
								<?php if ( $args['swiper_arrow_prev_custom_svg'] ): ?>
									<?php echo wp_kses($args['swiper_arrow_prev_custom_svg'], $allowed_html); ?>
									
								<?php else: ?>
									<svg width="var(--swiper-navigation-size)" height="var(--swiper-navigation-size)" viewBox="0 0 24 24">
										<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
										<path d="M0 0h24v24H0z" fill="none"/>
									</svg>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="swiper-button-next swiper-navigation-next_<?php echo esc_attr($args['media_thumbs_id']) ?> <?php echo ($args['swiper_arrows_as_svg'] ? 'swiper-button-has-svg' : '' ) ?>">
							<?php if ( $args['swiper_arrows_as_svg'] ): ?>	
								<?php if ( $args['swiper_arrow_next_custom_svg'] ): ?>
									<?php echo wp_kses($args['swiper_arrow_next_custom_svg'], $allowed_html); ?>
								<?php else: ?>
									<svg width="42" height="42" viewBox="0 0 24 24">
										<path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
										<path d="M0 0h24v24H0z" fill="none"/>
									</svg>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- These elements will create a gradient on the side of the carousel -->
					<div class="swiper-start-border"></div>
					<div class="swiper-end-border"></div>
				</div>
				<?php echo wp_kses_post($args['after_thumbs_div']) ?>
			<?php endif; ?>

		</div>
	<?php
	endif;  // <!-- End of if ($args['has_media_main'] || $args['has_media_thumbs'] ) --> 
	remove_filter( 'safe_style_css', 'tainacan_get_default_allowed_styles');
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}


/**
 * Tainacan Media Item for the Media Gallery component, used to render a single link displayed in the carousel
 * 
 * @param array|string $args {
 *   Optional. Array of arguments.
 *     @type string      before_slide_content    String to be added before the slide-content div or a tag
 *     @type string      after_slide_content     String to be added after the slide-content div or a tag
 *     @type string      class_slide_content     Class to be added to the slide-content div or a tag
 *     @type string      before_slide_metadata   String to be added before the slide-metadata div
 *     @type string      after_slide_metadata    String to be added after the slide-metadata div
 *     @type string      class_slide_metadata    Class to be added to the slide-metadata div
 *     @type string      media_content           The content of the slide itself, such as an image, audio, video or iframe tag. It may be wrapped by a link to the full content
 *     @type string      media_content_full      The media full content, either an image, an html
 *     @type string      media_title             The media title, if available
 *     @type string      media_description       The media description, if available
 *     @type string      media_caption           The media caption, if available
 *     @type string      media_type              The media type or mime_type, used to render an icon if media_content is empty
 * }
 * @return string
 */
	
function tainacan_get_the_media_component_slide( $args = array() ) {

	$args = array_merge(array(
		'before_slide_content' => '',
		'after_slide_content' => '',
		'class_slide_content' => '',
		'before_slide_metadata' => '',
		'after_slide_metadata' => '',
		'class_slide_metadata' => '',
		'media_content' => '',
		'media_content_full' => '',
		'media_title' => '',
		'media_description' => '',
		'media_caption' => '',
		'media_type' => ''
	), $args);

	ob_start();

?>
	<?php echo wp_kses_post($args['before_slide_content']) ?>

	<div class="swiper-slide-content <?php echo esc_attr($args['class_slide_content']) ?>">

		<?php if ( isset($args['media_content']) && !empty($args['media_content']) && $args['media_content'] !== false ) :?>
			<?php echo wp_kses_tainacan($args['media_content']) ?>
		<?php else: ?>
			<img src="<?php echo esc_url(tainacan_get_the_mime_type_icon($args['media_type'])) ?>" alt="<?php echo ( !empty($args['media_title']) ? esc_attr($args['media_title']) : __('File', 'tainacan') ) ?>" >
		<?php endif; ?>
		
		<?php echo wp_kses_post($args['before_slide_metadata']); ?>

		<?php if ( !empty($args['media_title']) || !empty($args['description']) || !empty($args['media_caption']) ) : ?>
			<div class="swiper-slide-metadata  <?php echo wp_kses_post($args['class_slide_metadata']); ?>">
				<?php if ( !empty($args['media_caption']) ) :?>
					<span class="swiper-slide-metadata__caption">
						<?php echo wp_kses_post($args['media_caption']); ?>
						<br>
					</span>
				<?php endif; ?>	
				<?php if ( !empty($args['media_title']) ) :?>
					<span class="swiper-slide-metadata__name">
						<?php echo wp_kses_post($args['media_title']); ?>
					</span>
				<?php endif; ?>
				<br>
				<?php if ( !empty($args['media_description']) ) :?>
					<span class="swiper-slide-metadata__description">
						<?php echo wp_kses_post($args['media_description']); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( !empty($args['media_content_full']) ) : ?>
			<div class="media-full-content" style="display: none; position: absolute; visibility: hidden;">
				<?php echo wp_kses_tainacan($args['media_content_full']) ?>
			</div>
		<?php endif; ?>

		<?php echo wp_kses_post($args['after_slide_metadata']) ?>

	</div>

	<?php echo wp_kses_post($args['after_slide_content']) ?>

<?php

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * When visiting a collection archive or single, returns the collection url link
 *
 * @return string
 */
function tainacan_get_the_collection_url() {
	$collection = tainacan_get_collection();
	$url = '';
	
	if ( $collection ) {
		$url = $collection->get_url();
	}
	return apply_filters('tainacan-get-collection-url', esc_url($url), $collection);
}					


/**
 * When visiting a collection archive or single, prints the collection url link
 *
 * @return void
 */
function tainacan_the_collection_url() {
	echo esc_url(tainacan_get_the_collection_url());
}


/**
 * Get related to view modes
 *
 * @return array ['default_view_mode'=> '', '$enabled_view_modes'=> [], , '$registered_view_modes'=> [] ]
 */
function tainacan_get_the_view_modes() {
	$default_view_mode = apply_filters( 'tainacan-default-view-mode-for-themes', 'masonry' );
	$registered_view_modes = \Tainacan\Theme_Helper::get_instance()->get_registered_view_modes();
	$registered_view_modes_slugs = [];
	foreach ($registered_view_modes as $key => $value) {
		array_push($registered_view_modes_slugs, $key);
	}
	$enabled_view_modes = apply_filters( 'tainacan-enabled-view-modes-for-themes', $registered_view_modes_slugs );

	// If in a collection page
	$collection = tainacan_get_collection();
	if ($collection) {
		$default_view_mode = $collection->get_default_view_mode();
		$enabled_view_modes = $collection->get_enabled_view_modes();
	}
	
	return [
		'default_view_mode' => $default_view_mode,
		'enabled_view_modes' => $enabled_view_modes,
		'registered_view_modes' => $registered_view_modes
	];
}

/** 
 * Checks whether a view mode is enabled in the current list instance
 *
 * @return boolean 
 */
function tainacan_is_view_mode_enabled($view_mode_slug) {
	$view_modes = tainacan_get_the_view_modes();
	
	if (is_array($view_modes['enabled_view_modes'])) {
		foreach($view_modes['enabled_view_modes'] as $view_mode) {
			if ($view_mode == $view_mode_slug)
				return true;
		}
	}
	return false;
}

/**
 * Outputs the div used by Vue to render the Items List with a powerful faceted search
 *
 * The items list bellong to a collection, to the whole repository or a taxonomy term, according to where
 * it is used on the loop, or to given params
 * 
 * The following params are all optional for customizing the rendered vue component
 *
 * @param array $args {
	 *     Optional. Array of arguments.
	 *     @type string $collection_id								Collection ID for a collection items list
	 *     @type string $term_id									Term ID for a taxonomy term items list
	 *     @type bool 	$hide_filters								Completely hide filter sidebar or modal
	 *     @type bool 	$hide_hide_filters_button					Hides the button resonsible for collpasing filters sidebar on desktop
	 *     @type bool 	$hide_search								Hides the complete search bar, including advanced search link
	 *     @type bool 	$hide_advanced_search						Hides only the advanced search link
	 *     @type bool	$hide_displayed_metadata_dropdown			Hides the "Displayed metadata" dropdown even if the current view modes allows it	
	 *     @type bool	$hide_sorting_area							Completely hides all sorting controls	
	 *     @type bool 	$hide_sort_by_button						Hides the button where user can select the metadata to sort by items (keeps the sort direction)
	 *     @type bool 	$hide_items_thumbnail						Forces the thumbnail to be hiden on every listing. This setting also disables view modes that contain the 'requires-thumbnail' attr. By default is false or inherited from collection setting
	 *     @type bool	$hide_exposers_button						Hides the "View as..." button, a.k.a. Exposers modal
	 *     @type bool 	$hide_items_per_page_button					Hides the button for selecting amount of items loaded per page
	 *     @type bool 	$hide_go_to_page_button						Hides the button for skiping to a specific page
	 *     @type bool	$hide_pagination_area						Completely hides pagination controls
	 *     @type int	$default_items_per_page						Default number of items per page loaded
	 *     @type bool 	$show_filters_button_inside_search_control	Display the "hide filters" button inside of the search control instead of floating
	 *     @type bool 	$start_with_filters_hidden					Loads the filters list hidden from start
	 *     @type bool 	$filters_as_modal							Display the filters as a modal instead of a collapsible region on desktop
	 *     @type bool 	$show_inline_view_mode_options				Display view modes as inline icon buttons instead of the dropdown
	 *     @type bool 	$show_fullscreen_with_view_modes			Lists fullscreen viewmodes alongside with other view modes istead of separatelly
	 *     @type string $default_view_mode							The default view mode
	 *     @type bool	$is_forced_view_mode						Ignores user prefs to always render the choosen default view mode
	 *     @type string[] $enabled_view_modes						The list os enable view modes to display
 * @return string  The HTML div to be used for rendering the items list vue component
 */
function tainacan_the_faceted_search($args = array()) {
	$theme_helper = \Tainacan\Theme_Helper::get_instance();
	echo $theme_helper->get_tainacan_items_list($args);
}

/**
 * When visiting a term archive, returns the current term object if it belongs to a Tainacan taxonomy
 *
 * @return false|\WP_Term
 */
function tainacan_get_term($args = []) {
	if ( isset( $args['term_id'] ) ) {
		$term = get_term($args['term_id']);

		if ( $term instanceof \WP_Error ) {
			return false;
		}
		return $term;
	}
	if ( is_tax() ) {
		$term = get_queried_object();
		$theme_helper = \Tainacan\Theme_Helper::get_instance();
		if ( isset($term->taxonomy) && $theme_helper->is_taxonomy_a_tainacan_tax($term->taxonomy) ) {
			return $term;
		}
	}
	return false;
}

/**
 * When visiting a taxonomy archive, returns the term name
 *
 * @return string
 */
function tainacan_get_the_term_name() {
	$term = tainacan_get_term();
	$name = '';
	if ( $term ) {
		$name = $term->name;
	}
	return apply_filters('tainacan-get-term-name', esc_html($name), $term);
}

/**
 * When visiting a taxonomy archive, prints the term name
 *
 * @return void
 */
function tainacan_the_term_name() {
	echo esc_html(tainacan_get_the_term_name());
}

/**
 * When visiting a taxonomy archive, returns the term description
 *
 * @return string
 */
function tainacan_get_the_term_description() {
	$term = tainacan_get_term();
	$description = '';
	if ( $term ) {
		$description = $term->description;
	}
	return apply_filters('tainacan-get-term-description', esc_html($description), $term);
}

/**
 * When visiting a taxonomy archive, prints the term description
 *
 * @return void
 */
function tainacan_the_term_description() {
	echo esc_html(tainacan_get_the_term_description());
}

/**
 * To be used inside The Loop
 *
 * Return the list of attachments of the current item (by default, excluding the document and the thumbnail)
 *
 * @param string|array IDs of attachments to be excluded (by default this function already excludes the document and the thumbnail)
 * @param int|string $item_id (Optional) The item ID to retrive attachments. Default is the global $post
 * @return array      Array of WP_Post objects. @see https://developer.wordpress.org/reference/functions/get_children/
 */
function tainacan_get_the_attachments($exclude = null, $item_id = 0) {
	$item = tainacan_get_item($item_id);

	if (!$item)
		return [];

	return apply_filters('tainacan-get-the-attachments', $item->get_attachments($exclude), $item);

}

function tainacan_get_attachment_html_url($attachment_id) {
	return \Tainacan\Media::get_instance()->get_attachment_html_url($attachment_id);
}

/**
 * @see \Tainacan\Theme_Helper->register_view_mode()
 */
function tainacan_register_view_mode($slug, $args = []) {
	\Tainacan\Theme_Helper::get_instance()->register_view_mode($slug, $args);
}

/**
 * Gets the Tainacan Item Entity object
 *
 * If used inside the Loop of items, will get the Item object for the current post
 */
function tainacan_get_item($post_id = 0) {
	return \Tainacan\Theme_Helper::get_instance()->tainacan_get_item($post_id);
}

/**
 * To be used inside The Loop of a faceted serach view mode template.
 *
 * Returns true or false indicating whether a certain property or metadata is
 * selected to be displayed
 *
 * @param string|integer The property to be checked. If a string is passed, it will check against
 * 	one of the native property of the item, such as title, description and creation_date.
 *  If an integer is passed, it will check against the IDs of the metadata.
 * 
 * @param int|string $item_id       (Optional) The item ID. Default is the global $post
 *
 * @return bool
 */
function tainacan_current_view_displays($property, $item_id = 0) {
	global $view_mode_displayed_metadata;

	// Core metadata appear in fetch_only as metadata
	if ($property == 'title' || $property == 'description') {
		$item = tainacan_get_item($item_id);
		$core_getter_method = "get_core_{$property}_metadatum";
		$property = $item->get_collection()->$core_getter_method()->get_id();
	}

	if (is_string($property)) {
		return in_array($property, $view_mode_displayed_metadata);
	} elseif (is_integer($property) && array_key_exists('meta', $view_mode_displayed_metadata)) {
		return in_array($property, $view_mode_displayed_metadata['meta']);
	}
	return false;
}

/**
 *
 * Displays the link to the edit page of an item, if current user have permission
 *
 * Can be used outside The Lopp if an ID is provided.
 *
 * The same as edit_post_link() (@see https://developer.wordpress.org/reference/functions/edit_post_link/) but for
 * Tainacan Items
 *
 * @param string $text 	(optional) Anchor text. If null, default is 'Edit this item'.
 * @param string $before 	(optional) Display before edit link
 * @param string $afer 	(optional) Display after edit link
 * @param int|WP_Post $id 	(optional) Post ID or post object. Default is the global $post.
 * @param string $class 	(optional) Add custom class to link
 *
 */
function tainacan_the_item_edit_link( $text = null, $before = '', $after = '', $id = 0, $class = 'post-edit-link' ) {
	if ( ! $item = tainacan_get_item( $id ) ) {
		return;
	}

	if ( ! $item->can_edit() || ! $url = $item->get_edit_url() ) {
		return;
	}

	if ( null === $text ) {
		$text = __( 'Edit this item', 'tainacan' );
	}

	$link = '<a class="' . esc_attr($class) . '" href="' . esc_url( $url ) . '">' . $text . '</a>';

	echo wp_kses_post($before . $link . $after);
}

/**
 * Gets the initials from a name.
 *
 * By default, returns 2 uppercase letters representing the name. The first letter from the first name and the first letter from the last.
 *
 * @param string $string The name to extract the initials from
 * @param bool $one whether to return only the first letter, instead of two
 *
 * @return string
 */
function tainacan_get_initials($string, $one = false) {

	if (empty($string)) {
		return '';
	}

	$string = remove_accents($string);

	if (strlen($string) == 1) {
		return strtoupper($string);
	}

	$first = strtoupper(substr($string,0,1));

	if ($one) {
		return $first;
	}

	$words=explode(" ",$string);

	if (sizeof($words) < 2) {
		$second = $string[1];
	} else {
		$second = $words[ sizeof($words) - 1 ][0];
	}

	$result = strtoupper($first . $second);
	return apply_filters('tainacan-get-initials', $result, $string, $one);
}

/**
 * Gets the icon mime type using our custom plugin thumbnails
 * 
 * @param string $mime_type The mime_type or type of the file
 * @param string $image_size The image size
 * 
 * @return string
 */
function tainacan_get_the_mime_type_icon($mime_type, $image_size = 'medium') {
	global $TAINACAN_BASE_URL;
	$images_path = $TAINACAN_BASE_URL . '/assets/images/';

	$icon_file = '';

	switch($image_size) {
		case 'full':
		case 'large':
		case 'tainacan-large-full':
			$image_size = '';
			break;
		case 'small':
		case 'tainacan-small':
		case 'thumbnail':
			$image_size = '_small';
			break;
		case '':
		case 'medium':
		case 'tainacan-medium':
		case 'tainacan-medium-full':
		case 'medium_large':
		default:
			$image_size = '_medium';
	}

	switch($mime_type) {
		case 'image':
		case 'image/png':
		case 'image/jpeg':
		case 'image/gif':
		case 'image/bmp':
		case 'image/webp':
		case 'image/svg+xml':
			$icon_file = 'placeholder_image';
			break;
		case 'audio':
		case 'audio/midi':
		case 'audio/mpeg':
		case 'audio/mp3':
		case 'audio/webm':
		case 'audio/ogg':
		case 'audio/wav':
			$icon_file = 'placeholder_audio';
			break;
		case 'text':
		case 'text/plain':
		case 'text/html':
		case 'text/css':
		case 'text/javascript':
		case 'text/csv':
			$icon_file = 'placeholder_text';
			break;
		case 'video':
		case 'video/webm':
		case 'video/ogg':
		case 'video/mpeg':
		case 'video/mp4':
			$icon_file = 'placeholder_video';
			break;
		case 'url':
			$icon_file = 'placeholder_url';
			break;
		case 'application/pdf':
			$icon_file = 'placeholder_pdf';
			break;
		case 'attachment':
			$icon_file = 'placeholder_attachment';
			break;
		case 'empty':
		default:
			$icon_file = 'placeholder_square';
	}
	
	/**
	 * Filter the image source for the empty thumbnail placeholder.
	 * 
	 * @param string src The image source for the empty thumbnail placeholder.
	 *               Default is 'placeholder_square'.
	 * @param string mime_type The document type of the item.
	 * @param string image_size The size of the image to be loaded.
	 */
	return apply_filters('tainacan-get-the-mime-type-icon', $images_path . $icon_file . $image_size . '.png', $mime_type, $image_size);
}

/**
 * Displays a carousel of items, the same of the gutenberg block
 *
 * @param array $args {
 	*     Optional. Array of arguments.
 	*     @type string  $collection_id					The Collection ID
 	*     @type string  $search_URL						A query string to fetch items from, if load strategy is 'search'
 	*     @type array   $selected_items					An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'
 	*     @type string  $load_strategy					Either 'search' or 'selection', to determine how items will be fetch
 	*     @type integer $max_items_number				Maximum number of items to be fetch
 	*     @type integer $max_tems_per_screen			Maximum columns of items to be displayed on a row of the carousel
 	*     @type string  $arrows_position				How the arrows will be positioned regarding the carousel ('around', 'left', 'right')
 	*     @type bool    $large_arrows					Should large arrows be displayed?
 	*     @type bool    $auto_play						Should the Caroulsel start automatically to slide?
 	*     @type integer $auto_play_speed				The time in s to translate to the next slide automatically 
 	*     @type bool    $loop_slides					Should slides loop when reached the end of the Carousel?
 	*     @type bool    $hide_title						Should the title of the items be displayed?
 	*     @type string  $image_size					Item image size. Defaults to 'tainacan-medium'
 	*     @type bool    $show_collection_header			Should it display a small version of the collection header?
 	*     @type bool    $show_collection_label			Should it displar a 'Collection' label before the collection name on the collection header?
 	*     @type string  $collection_background_color	Color of the collection header background
 	*     @type string  $collection_text_color			Color of the collection header text
 	*     @type string  $tainacan_api_root				Path of the Tainacan api root (to make the items request)
 	*     @type string  $tainacan_base_url				Path of the Tainacan base URL (to make the links to the items)
 	*     @type string  $class_name					Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-items-list
 * @return void  The HTML div to be used for rendering the items carousel vue component
*/
function tainacan_the_items_carousel($args = []) {
	echo \Tainacan\Theme_Helper::get_instance()->get_tainacan_items_carousel($args);
}

/**
 * Displays a group of related items lists
 * For each metatada, the collection name, the metadata name and a button linking
 * the items list filtered is presented
 *
 * @param array $args {
	 *     Optional. Array of arguments.
	 *     @type string  $item_id					The Item ID
 * @return void
 */
function tainacan_the_related_items($args = []) {
	echo \Tainacan\Theme_Helper::get_instance()->get_tainacan_related_items_list($args);
}

/**
 * Displays a group of related items carousels
 * This is a preset version of tainacan_the_related_items, to keep compatibility with previous versions
 *
 * @param array $args {
	 *     Optional. Array of arguments.
	 *     @type string  $item_id					The Item ID
 * @return void
 */
function tainacan_the_related_items_carousel($args = []) {
	echo \Tainacan\Theme_Helper::get_instance()->get_tainacan_related_items_carousel($args);
}


/**
 * Checks if the current item has or not related items
 */
function tainacan_has_related_items($item_id = false) {
	// Gets the current Item
	$item = $item_id ? \Tainacan\Theme_Helper::get_instance()->tainacan_get_item($item_id) : \Tainacan\Theme_Helper::get_instance()->tainacan_get_item();
	if (!$item)
		return;
	
	// Then fetches related ones
	$related_items = $item->get_related_items();// TODO: handle this inside the item so we don't have to load things here.
	if ( !$related_items || !is_array($related_items) || !count($related_items) )
		return false;

	// If we have at least one total_items, there are related items
	foreach($related_items as $related_group) {
		if ( isset($related_group['total_items']) && (int)$related_group['total_items'] > 0 )
			return true;
	}
	return false;
}


/**
 * Renders the content of the item gallery block
 * using Tainacan template functions that create
 * a Swiper.js carousel and slider, with a PhotoSwipe.js 
 * lightbox
 *
 * @param array $args {
	*     Optional. Array of arguments.
	*      @type string  $item_id						  The Item ID
	* 	   @type string	 $blockId 						  A unique identifier for the gallery, will be generated automatically if not provided,
	* 	   @type array 	 $layoutElements 				  Array of elements present in the gallery. Possible values are 'main' and 'carousel'
	* 	   @type array 	 $mediaSources 					  Array of sources for the gallery. Possible values are 'document' and 'attachments'
	* 	   @type bool 	 $hideFileNameMain 				  Hides the Main slider file name
	* 	   @type bool 	 $hideFileCaptionMain 			  Hides the Main slider file caption
	* 	   @type bool 	 $hideFileDescriptionMain		  Hides the Main slider file description
	* 	   @type bool 	 $hideFileNameThumbnails 		  Hides the Thumbnails carousel file name
	* 	   @type bool 	 $hideFileCaptionThumbnails 	  Hides the Thumbnails carousel file caption
	* 	   @type bool 	 $hideFileDescriptionThumbnails   Hides the Thumbnails carousel file description
	* 	   @type bool 	 $hideFileNameLightbox 			  Hides the Lightbox file name
	* 	   @type bool 	 $hideFileCaptionLightbox 		  Hides the Lightbox file caption
	* 	   @type bool 	 $hideFileDescriptionLightbox	  Hides the Lightbox file description
	* 	   @type bool 	 $openLightboxOnClick 			  Enables the behaviour of opening a lightbox with zoom when clicking on the media item
	*	   @type bool	 $showDownloadButtonMain		  Displays a download button below the Main slider
	*	   @type bool	 $lightboxHasLightBackground      Show a light background instead of dark in the lightbox 
	*	   @type bool    $showArrowsAsSVG			      Decides if the swiper carousel arrows will be an SVG icon or font icon
	*	   @type string  $thumbnailsSize				  Media size for the thumbnail images. Defaults to 'tainacan-medium'
	*	   @type bool  	 $thumbsHaveFixedHeight			  If thumbs should have a fixed height and auto widht. Defaults to false.
	* }		
	* @return void
 */
function tainacan_the_item_gallery($args = []) {
	echo \Tainacan\Theme_Helper::get_instance()->get_tainacan_item_gallery($args);
}


/**
 * Render the item metadata sections as a HTML string.
 *
 * Each metadata section is a label with the list of its metadata name and value.
 *
 * If an ID, a slug or a Tainacan\Entities\Metadata_Section object is passed in the 'metadata_section' argument, it returns only one metadata section, otherwise
 * it returns all metadata section
 *
 * @param array|string $args {
	*     Optional. Array or string of arguments.
	*
	* 	  @type mixed		 $metadata_section				Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata_sections
	*
	*     @type array		 $metadata_sections__in			Array of metadata_sections IDs or Slugs to be retrieved. Default none
	*
	*     @type array		 $metadata_sections__not_in		Array of metadata_sections IDs (slugs not accepted) to excluded. Default none
	* 
	*     @type bool		 $hide_name						Do not display the Metadata Section name. Default false
	*
	*     @type bool		 $hide_description				Do not display the Metadata Section description. Default true
	*
	*     @type bool        $hide_empty                	Whether to hide or not metadata sections if there are no metadata list or they are empty
	*                                                  	Default: true
	*     @type string      $empty_metadata_list_message 	Message string to display if $hide_empty is false and there is not metadata section metadata list.
	*                                                  	Default: ''
	*     @type bool        $display_slug_as_class     	Show metadata slug as a class in the div before the metadata block
	*                                                  	Default: true
	*     @type string      $before                    	String to be added before each metadata section block
	*                                                  	Default '<section $id>'
	*     @type string      $after		                String to be added after each metadata section block
	*                                                  	Default '</section>'
	*     @type string      $before_name              	String to be added before each metadata section name
	*                                                  	Default '<h2>'
	*     @type string      $after_name               	String to be added after each metadata section name
	*                                                  	Default '</h2>'
	* 	  @type string      $before_description         String to be added before each metadata section description
	*                                                  	Default '<p>'
	*     @type string      $after_description          String to be added after each metadata section description
	*                                                  	Default '</p>'
	*     @type string      $before_metadata_list      	String to be added before each metadata section inner metadata list
	*                                                  	Default '<div class="metadata-section__metadata-list">'
	*     @type string      $after_metadata_list       	String to be added after each metadata section inner metadata list
	*                                                  	Default '</div>'
	*	  @type array		$metadata_list_args			Arguments to be passed to the get_metadata_as_html function when calling section metadata
	* }
	*
	* @return string        The HTML output
 */
function tainacan_get_the_metadata_sections($args = array(), $item_id = 0) {

	$item = tainacan_get_item( $item_id );
	
	if ($item instanceof \Tainacan\Entities\Item) {
		return $item->get_metadata_sections_as_html($args);
	}

	return '';

}

function tainacan_the_metadata_sections($args = array()) {
	echo tainacan_get_the_metadata_sections($args);
}

/**
 * Render the taxonomy single template HTML string.
 *
 * This works as an archive of the taxonomy terms, and uses the CPT tainacan-taxonomy.
 * 
 * It should display the list of terms, and it is used in the the_content filter of the theme helper to override the cpt single.
 *
 * @param object $post			The original tainacan-taxonomy post object. It contains the $post->ID, which can be used to query the taxonomy of slug tnc_tax_<$post-id>
 * @param array|string $args {
	*     Optional. Array or string of arguments.
	*	  @type bool		$hide_hierarchy_header				Do not display the Term hiearachy header before the list. Default false

	*	  @type bool		$hide_term_thumbnail				Do not display the Term thumbnail. Default false
	*	  @type bool		$hide_term_thumbnail_placeholder 	Do not display the Term thumbnail placeholder (if no image is found). Default true
	*     @type bool		$hide_term_hierarchy_path			Do not display the Term hierarchy path. Default true
	*     @type bool		$hide_term_name						Do not display the Term name. Default false
	*     @type bool		$hide_term_description				Do not display the Term description. Default true
	*     @type bool		$hide_term_children_link			Do not display the Term children link. Default false
	*     @type bool		$hide_term_items_link				Do not display the Term items list link. Default false
	*	  @type bool		$hide_term_children_count			Do not display the Term children count. Default true
	*	  @type string		$term_children_count_position		Position of the term children count relative to the term 'children' label. Default 'after'
	*     @type bool		$hide_term_items_count				Do not display the Term items count. Default true
	*	  @type string		$term_items_count_position			Position of the term items count relative to the term 'items' label. Default 'after'
	*     @type bool		$hide_term_empty_name				Do not display the Term name area if it is empty. Default true
	*     @type bool		$hide_term_empty_hierarchy_path		Do not display the Term hierarchy path area if it is empty. Default true
	*     @type bool		$hide_term_empty_description		Do not display the Term description area if it is empty. Default true
	*     @type bool		$hide_term_empty_children_link		Do not display the Term children link area if it has no children. Default true
	*     @type bool		$hide_term_empty_items_link			Do not display the Term items list link area if it has no item. Default true
	*     @type string		$term_empty_name_message			Term name area if it is empty. Default 'Term without name'
	*	  @type string		$term_empty_hierarchy_path_message  Term hierarchy path area if the term is root-level. Default 'Root term'
	*     @type string		$term_empty_description_message		Term description area if it is empty. Default 'Term without description'
	*     @type string		$term_empty_children_link_message	Term children link area if it is empty. Default 'Term without children'
	*     @type string		$term_empty_items_link_message		Term items list link area if it is empty. Default 'Term without items'
	*	  @type integer		$trim_description_words				Amount of words to trim the term description by. Default -1, which means no trimming
	*     @type string      $before_terms_list_container 		String to be added before the taxonomy terms list container
	*                                                  			Default '<div class="wp-block-query tainacan-taxonomy-terms-list-container">'
	*     @type string      $after_terms_list_container 		String to be added after the taxonomy terms list container
	*                                                  			Default '</div>'
	*     @type string      $before_terms_list         			String to be added before the taxonomy terms list
	*                                                  			Default '<ul class="wp-block-post-template is-layout-flow tainacan-taxonomy-terms-list" style="list-style: none; padding: 0;">'
	*     @type string      $after_terms_list           		String to be added after the taxonomy terms list
	*                                                  			Default '</ul>'
	*     @type string      $before_term			    		String to be added before each term inside the loop
	*                                                  			Default '<li class="wp-block-post tainacan-term-single" id="term-id-$id">'
	*     @type string      $after_term			        		String to be added after each term inside the loop
	*                                                  			Default '</li>'	
	*     @type string      $before_term_thumbnail      		String to be added before each term thumbnail
	*                                                  			Default '<figure class="term-thumbnail wp-block-post-featured-image">'
	*     @type string      $after_term_thumbnail       		String to be added after each term thumbnail
	*                                                  			Default '</figure>'
	*     @type string      $before_term_information     		String to be added before each term information area (everything except thumbnail)
	*                                                  			Default ''
	*     @type string      $after_term_information	     		String to be added after each term information area (everything except thumbnail)
	*                                                  			Default ''	
	* 	  @type string      $before_term_hierarchy_path 		String to be added before each term hierarchy path
	*                                                  			Default '<span class="term-hierarchy-path"><em>'
	*     @type string      $after_term_hierarchy_path  		String to be added after each term hierarchy path
	*                                                  			Default '</em></span>'
	*     @type string      $before_term_name           		String to be added before each term name
	*                                                  			Default '<h2 class="term-name">'
	*     @type string      $after_term_name            		String to be added after each term name
	*                                                  			Default '</h2>'
	* 	  @type string      $before_term_description    		String to be added before each term description
	*                                                  			Default '<p class="term-description">'
	*     @type string      $after_term_description     		String to be added after each term description
	*                                                  			Default '</p>'
	*     @type string      $before_term_links		     		String to be added before each term links area
	*                                                  			Default ''
	*     @type string      $after_term_links		     		String to be added after each term links area
	*                                                  			Default ''
	* 	  @type string      $before_term_children_link  		String to be added before each term children link
	*                                                  			Default '<span class="term-children-link">'
	*     @type string      $after_term_children_link   		String to be added after each term children link
	*                                                  			Default '</span>'
	* 	  @type string      $before_term_items_link  			String to be added before each term items link
	*                                                  			Default '<span class="term-items-link">'
	*     @type string      $after_term_items_link   			String to be added after each term items link
	*                                                  			Default '</span>'
	*     @type string      $thumbnails_size 		  			String to be added after each term items link
	*                                                  			Default 'tainacan-large-full'
	* }
	*
	* @return string        The HTML output
 */
function tainacan_get_single_taxonomy_content($post, $args = []) {

	$args = array_merge(array(
		'hide_hierarchy_header' => false,
		'hide_term_thumbnail' => false,
		'hide_term_thumbnail_placeholder' => true,
		'hide_term_hierarchy_path' => true,
		'hide_term_name' => false,
		'hide_term_description' => true,
		'hide_term_children_link' => false,
		'hide_term_items_link' => false,
		'hide_term_children_count' => true,
		'term_children_count_position' => 'after',
		'hide_term_items_count' => true,
		'term_items_count_position' => 'after',
		'hide_term_empty_name' => true,
		'hide_term_empty_hierarchy_path' => true,
		'hide_term_empty_description' => true,
		'hide_term_empty_children_link' => true,
		'hide_term_empty_items_link' => true,
		'term_empty_name_message' => __( 'Term without name', 'tainacan' ),
		'term_empty_hierarchy_path_message' => __( 'Root term', 'tainacan' ),
		'term_empty_description_message' => __( 'Term without description', 'tainacan' ),
		'term_empty_children_link_message' => __( 'Term without children', 'tainacan' ),
		'term_empty_items_link_message' => __( 'Term without items', 'tainacan' ),
		'trim_description_words' => -1,
		'before_terms_list_container' => '<div class="wp-block-query tainacan-taxonomy-terms-list-container">',
		'after_terms_list_container' => '</div>',
		'before_terms_list' => '<ul class="wp-block-post-template is-layout-flow tainacan-taxonomy-terms-list" style="list-style: none; padding: 0;">',
		'after_terms_list' => '</ul>',
		'before_term' => '<li class="wp-block-post tainacan-term-single" id="term-id-$id">',
		'after_term' => '</li>',
		'before_term_thumbnail' => '<figure class="term-thumbnail wp-block-post-featured-image">',
		'after_term_thumbnail' => '</figure>',
		'before_term_information' => '',
		'after_term_information' => '',
		'before_term_hierarchy_path' => '<span class="term-hierarchy-path"><em>',
		'after_term_hierarchy_path' => '</em></span>',
		'before_term_name' => '<h2 class="term-name wp-block-post-title">',
		'after_term_name' => '</h2>',
		'before_term_description' => '<div class="term-description wp-block-post-excerpt"><p class="wp-block-post-excerpt__excerpt">',
		'after_term_description' => '</p></div>',
		'before_term_links' => '',
		'after_term_links' => '',
		'before_term_children_link' => '<span class="term-children-link">',
		'after_term_children_link' => '</span>',
		'before_term_items_link' => '<span class="term-items-link">',
		'after_term_items_link' => '</span>',
		'thumbnails_size' => 'tainacan-large-full'
	), $args);

	/* Gets query arguments to build fetch params */
	$current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();

	$terms_query_args = array(
		'taxonomy' => 'tnc_tax_' . $post->ID,
		'order' => $current_args['order'],
		'orderby' => $current_args['orderby'],
		'hide_empty' => false,
		'offset' => ($current_args['termspaged'] - 1) * $current_args['perpage'],
		'number' => $current_args['perpage'],
		'search' => $current_args['search'],
		'parent' => $current_args['termsparent']
	);
	$terms_query_args = apply_filters('tainacan_single_taxonomy_terms_query', $terms_query_args, $post);
	$terms = get_terms( $terms_query_args );

	unset( $terms_query_args['number'], $terms_query_args['offset'] ); // necessary so wp_count_terms can work
	$total_terms = wp_count_terms( 'tnc_tax_' . $post->ID, $terms_query_args );
	
	$content = '';

	if ( !empty( $terms ) && !is_wp_error( $terms ) ) {

		$content = $args['before_terms_list_container'] . $content;

		$separator = strip_tags(apply_filters('tainacan-terms-hierarchy-html-separator', '>'));

		if ( !$args['hide_hierarchy_header'] && isset($current_args['termsparent']) && $current_args['termsparent'] ) {
			$content .= '<div class="terms-hierarachy-header"><p>' . __('Showing terms children of', 'tainacan') . '&nbsp;';
			
			$parent = get_term($current_args['termsparent']);
			$tainacan_parent_term = new Entities\Term( $parent );

			$content .= '<em>' . $tainacan_parent_term->get_name() . '</em>.&nbsp;';

			if ( $tainacan_parent_term->get_parent() ) {
				$grandparent = get_term($tainacan_parent_term->get_parent());
				$tainacan_grandparent_term = new Entities\Term( $grandparent );
				
				$content .= '<a href="' . add_query_arg( 'termsparent', $tainacan_parent_term->get_parent() ) . '">' . __('Return to the list of terms children of ', 'tainacan') . '<em>' . $tainacan_grandparent_term->get_name() . '</em>.</a>';
			} else
				$content .= '<a href="' . remove_query_arg( 'termsparent' ) . '">' . __('Return to the terms list.', 'tainacan') . '</a>';

			$content .= '</p></div>';
		}

		$content .= $args['before_terms_list'];

		foreach ( $terms as $term ) {
			$tainacan_term = new Entities\Term( $term );

			ob_start();

			$before_term = $args['before_term'];
			$before_term = str_replace('$id', $tainacan_term->get_id(), $before_term);

			echo $before_term;
			
			// If the term children is hidden but not the items, we set the whole area as a link for the term items list.
			if ( !$args['hide_term_items_link'] && $args['hide_term_children_link'] ) 
				echo '<a href="' . $tainacan_term->get_url() .'">';

			if ( !$args['hide_term_thumbnail'] ) {
				$thumbnail = wp_get_attachment_image( $tainacan_term->get_header_image_id(), $args['thumbnails_size'], false );
				
				if ( !$thumbnail && !$args['hide_term_thumbnail_placeholder'] )
					echo $args['before_term_thumbnail'] . '<img src="' . esc_url(tainacan_get_the_mime_type_icon('empty', $args['thumbnails_size'])) . '">' . $args['after_term_thumbnail'];
				else
					echo $thumbnail ? ($args['before_term_thumbnail'] . $thumbnail . $args['after_term_thumbnail'] ) : '';
			}

			echo $args['before_term_information'];

			if ( !$args['hide_term_hierarchy_path'] ) {
				$term_hierarchy_path = get_term_parents_list($tainacan_term->get_id(), 'tnc_tax_' . $post->ID, [ 'format' => 'name', 'separator' => $separator, 'link' => false, 'inclusive' => false ]);


				if ( $tainacan_term->get_parent() )
					echo $args['before_term_hierarchy_path'] . $term_hierarchy_path  . $args['after_term_hierarchy_path'];
				else if ( $tainacan_term->get_parent() && !$args['hide_term_empty_hierarchy_path'] )
					echo $args['before_term_hierarchy_path'] . $args['term_empty_hierarchy_path_message']  . $args['after_term_hierarchy_path'];
			}

			if ( !$args['hide_term_name'] ) {
				$term_name = $tainacan_term->get_name();

				if ( !empty($term_name) )
					echo $args['before_term_name'] . $tainacan_term->get_name() . $args['after_term_name'];
				else if ( empty($term_name) && !$args['hide_term_empty_name'] )
					echo $args['before_term_name'] . $args['term_empty_name_message'] . $args['after_term_name'];
			}

			if ( !$args['hide_term_description'] ) {
				$term_description =	$tainacan_term->get_description();

				if ( !empty($term_description) ) {
					if ($args['trim_description_words'] > -1)
						$term_description = wp_trim_words( $term_description, $args['trim_description_words'], '[...]' );

					echo $args['before_term_description'] . $term_description . $args['after_term_description'];
				} else if ( empty($term_description) && !$args['hide_term_empty_description'] ) {
					echo $args['before_term_description'] . $args['term_empty_description_message'] . $args['after_term_description'];
				}
			}

			echo $args['before_term_links'];

			if ( !$args['hide_term_children_link'] ) {
				$total_children = get_term_children( $tainacan_term->get_id(), 'tnc_tax_' . $post->ID );
				$total_children = is_array($total_children) && count($total_children) ? count($total_children) : 0;

				if ( $total_children ) {
					echo $args['before_term_children_link'] . '<a href="' . add_query_arg( 'termsparent', $tainacan_term->get_id() ) . '">';
					
					if ( !$args['hide_term_children_count'] && $args['term_children_count_position'] === 'before' )
						echo '<span class="term-children-count">' . $total_children . '</span>&nbsp;';
					echo ($total_children == 1 || $total_children == '1') ? __('Child', 'tainacan') : __('Children', 'tainacan');
					if ( !$args['hide_term_children_count'] && $args['term_children_count_position'] !== 'before' )
						echo '&nbsp;<span class="term-children-count">(' . $total_children . ')</span>';
					
					echo '</a>' . $args['after_term_children_link'] . '&nbsp;&nbsp;';

				} else if ( !$total_children && !$args['hide_term_empty_children_link'] )
					echo $args['before_term_children_link'] . $args['term_empty_children_link_message'] . $args['after_term_children_link'] . '&nbsp;&nbsp;';
			}

			if ( !$args['hide_term_items_link'] && !$args['hide_term_children_link'] ) {

				if ( $term->count ) {
					echo $args['before_term_items_link'] . '<a href="' . $tainacan_term->get_url() . '">';
					
					if ( !$args['hide_term_items_count'] && $args['term_items_count_position'] === 'before' ) 
						echo '<span class="term-items-count">' . $term->count . '</span>&nbsp;';
					echo ($term->count == 1 || $term->count == '1') ? __('Item', 'tainacan') : __('Items', 'tainacan');
					if ( !$args['hide_term_items_count'] && $args['term_items_count_position'] !== 'before' ) 
						echo '&nbsp;<span class="term-items-count">(' . $term->count . ')</span>';
					
					echo '</a>' . $args['after_term_items_link'];
				
				} else if ( !$term->count && !$args['hide_term_empty_items_link'] )
					echo $args['before_term_items_link'] . $args['term_empty_items_link_message'] . $args['after_term_items_link'];
			}

			echo $args['after_term_links'];

			echo $args['after_term_information'];

			if ( !$args['hide_term_items_link'] && $args['hide_term_children_link'] )
				echo '</a>';
			
			echo $args['after_term'];

			$html = ob_get_contents();
			ob_end_clean();
			
			$content .= $html;

		}

		$content .= $args['after_terms_list'];

		$content .= $args['after_terms_list_container'];

	} else {

		$content = $args['before_terms_list_container'] . $content;

		$content .= '<p>' . __('No term was found.', 'tainacan') . '</p>';

		$content .= $args['after_terms_list_container'];
	}

	return apply_filters('tainacan_get_single_taxonomy_content', ['content' => $content, 'total_terms' => $total_terms] , $post);
}

function tainacan_get_taxonomies_orderby($args = []) {

	$args = array_merge(array(
		'hide_orderby_label' => false,
		'hide_order_label' => false,
		'hide_orderby' => false,
		'hide_order' => false,
	), $args);

	$current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();

	ob_start();
	?>
	<form id="tainacan-taxonomy-sorting-field">
		<div	
				class="wp-block-group is-wrap is-layout-flex"
				style="display: flex; flex-wrap: wrap">
			<?php if ( !$args['hide_order'] ): ?>
				<?php if ( !$args['hide_order_label'] ): ?>
					<label for="tainacan-taxonomy-order-select">
						<?php _e( 'Sort', 'tainacan' ); ?>
					</label>
				<?php endif; ?>
				<select 
						id="tainacan-taxonomy-order-select"
						name="order"
						onchange="location = this.value;">
					<option value="<?php echo add_query_arg( 'order', 'ASC' ); ?>" <?php echo $current_args['order'] == 'ASC' ? 'selected' : ''; ?>>
						<?php _e( 'Ascending', 'tainacan' ); ?>
					</option>
					<option value="<?php echo add_query_arg( 'order', 'DESC' ); ?>" <?php echo $current_args['order'] == 'DESC' ? 'selected' : ''; ?>>
						<?php _e( 'Descending', 'tainacan' ); ?>
					</option>
				</select>
			<?php endif; ?>
			<?php if ( !$args['hide_orderby'] ): ?>
				<?php if ( !$args['hide_orderby_label'] ): ?>
					<label
							for="tainacan-taxonomy-orderby-select">
						<?php _e( 'by', 'tainacan' ); ?>
					</label>
				<?php endif; ?>
				<select
						id="tainacan-taxonomy-orderby-select"
						name="orderby"
						onchange="location = this.value;">
					<option value="<?php echo add_query_arg( 'orderby', 'name' ); ?>" <?php echo $current_args['orderby'] == 'name' ? 'selected' : ''; ?>>
						<?php _e( 'Name', 'tainacan' ); ?>
					</option>
					<option value="<?php echo add_query_arg( 'orderby', 'count' ); ?>" <?php echo $current_args['orderby'] == 'count'? 'selected' : ''; ?>>
						<?php _e( 'Amount of items', 'tainacan' ); ?>
					</option>
				</select>
			<?php endif; ?>
		</div>
	</form>
	<?php

	$html = ob_get_contents();
	ob_end_clean();

	return apply_filters('tainacan_get_taxonomies_orderby', $html );
}

function tainacan_the_taxonomies_orderby($args = []) {
	echo tainacan_get_taxonomies_orderby($args);
}

function tainacan_get_taxonomies_search($args = []) {

	$args = array_merge(array(
		'hide_label' => false,
	), $args);

	$current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();

	ob_start();
	?>
	<form
			id="tainacan-taxonomy-search-field"
			role="search"
			method="get"
			action=""
			class="wp-block-search__button-outside wp-block-search__text-button wp-block-search">
		<?php if ( !$args['hide_label'] ): ?>
			<label
					for="tainacan-taxonomy-search-field--input"
					class="wp-block-search__label">
				<?php echo __( 'Search', 'tainacan'); ?>
			</label>
		<?php endif; ?>
		<div class="wp-block-search__inside-wrapper">
			<input
					type="search"
					id="tainacan-taxonomy-search-field--input"
					class="wp-block-search__input wp-block-search__input"
					name="search"
					value="<?php echo $current_args['search']; ?>"
					placeholder="<?php echo __( 'Search by a term name', 'tainacan'); ?>">
			<button 
					type="submit" 
					class="wp-block-search__button wp-element-button">
				<?php echo __( 'Search', 'tainacan'); ?>
			</button>
		</div>
		<?php foreach ($_GET as $key => $value) {
			if ($key !== 'search' && $key !== 'termspaged') {
				$key = htmlspecialchars($key);
				$value = htmlspecialchars($value);
				echo "<input type='hidden' name='$key' value='$value'/>";
			}
		} ?>
	</form>
	<?php

	$html = ob_get_contents();
	ob_end_clean();

	return apply_filters('tainacan_get_taxonomies_search', $html );
}

function tainacan_the_taxonomies_search($args = []) {
	echo tainacan_get_taxonomies_search($args);
}

function tainacan_get_taxonomies_pagination($total_terms, $args = []) {

	$args = array_merge(array(
		'before_pagination' => '<p class="tainacan-taxonomies-pagination-links">',
		'after_pagination' => '</p>',
		'paginate_links_extra_args' => []
	), $args);
	
	$current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();

	if ( $total_terms <= $current_args['perpage'] )
		return '';
		
	$paginate_links_args = array_merge(array(
		'format' => '?termspaged=%#%',
		'total' => ceil( $total_terms / $current_args['perpage'] ),
		'current' => max( 1, get_query_var('termspaged') ),
		'add_args' => array(
			'order' => $current_args['order'],
			'orderby' => $current_args['orderby'],
			'perpage' => $current_args['perpage'],
			'search' => $current_args['search'],
			'termsparent' => $current_args['termsparent'],
		)
	), $args['paginate_links_extra_args']);

	$html = $args['before_pagination'] . paginate_links($paginate_links_args) . $args['after_pagination'];

	return apply_filters('tainacan_get_taxonomies_pagination', $html );
}

function tainacan_the_taxonomies_pagination($total_terms, $args = []) {
	echo tainacan_get_taxonomies_pagination($total_terms, $args);
}