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
	 *     @type bool        $hide_empty                Wether to hide or not metadata the item has no value to
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

	return '<a name="' . __('Download the item document', 'tainacan') . '" download="'. $link . '" href="' . $link . '">' . __('Download', 'tainacan') . '</a>';
}


function tainacan_the_item_attachment_download_link($attachment_id) {

	if ( !$attachment_id || !wp_get_attachment_url($attachment_id) )
		return;

	$link = wp_get_attachment_url($attachment_id);

	return '<a name="' . __('Download the item attachment', 'tainacan') . '" download="'. $link . '" href="' . $link . '">' . __('Download', 'tainacan') . '</a>';
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
	return apply_filters('tainacan-get-collection-name', $name, $collection);
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
	echo tainacan_get_the_collection_name();
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
	return apply_filters('tainacan-get-collection-description', $description, $collection);
}

/**
 * When visiting a collection archive or single, prints the collection description
 *
 * @return void
 */
function tainacan_the_collection_description() {
	echo tainacan_get_the_collection_description();
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
 *     @type string      before_main_div          String to be added before the main gallery div
 *     @type string      after_main_div           String to be added after the main gallery div
 *     @type string      before_thumbs_div        String to be added before the thumbs gallery div
 *     @type string      after_thumbs_div         String to be added after the thumbs gallery div
 *     @type string      before_main_ul           String to be added before the main gallery ul
 *     @type string      after_main_ul            String to be added after the main gallery ul
 *     @type string      before_thumbs_ul         String to be added before the thumbs gallery ul
 *     @type string      after_thumbs_ul          String to be added after the thumbs gallery ul
 *     @type string      class_main_div           Class to be added to the main gallery div
 *     @type string      class_main_ul	          Class to be added to the main gallery ul
 *     @type string      class_main_li            Class to be added to the main gallery li
 *     @type string      class_thumbs_div         Class to be added to the thumbs gallery div
 *     @type string      class_thumbs_ul          Class to be added to the thumbs gallery ul
 *     @type string      class_thumbs_li          Class to be added to the thumbs gallery li
 *     @type array       swiper_main_options      Object with SwiperJS options for the main gallery
 *     @type array       swiper_thumbs_options    Object with SwiperJS options for the thumb gallery
 *     @type bool        show_share_button        Shows share button on lightbox
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
		'show_share_button' => false
	), $args);

	$args['has_media_main'] = $media_items_main && is_array($media_items_main) && count($media_items_main);
	$args['has_media_thumbs'] = $media_items_thumbs && is_array($media_items_thumbs) && count($media_items_thumbs);
	$args['media_main_id'] = $media_id . '-main';
	$args['media_thumbs_id'] = $media_id . '-thumbs';
	$args['media_id'] = $media_id;
	
	if ( $args['has_media_main'] || $args['has_media_thumbs'] ) :
		// Modal lightbox layer for rendering photoswipe
		add_action('wp_footer', 'tainacan_get_the_media_modal_layer');
	
		wp_enqueue_style( 'tainacan-media-component', $TAINACAN_BASE_URL . '/assets/css/tainacan-gutenberg-block-item-gallery.css', array(), TAINACAN_VERSION);
		?>

		<script>
			try {
				tainacan_plugin = (typeof tainacan_plugin != undefined) ? tainacan_plugin : {};
			} catch(err) {
				tainacan_plugin = {};
			}
			tainacan_plugin.tainacan_media_components = (typeof tainacan_plugin.tainacan_media_components != "undefined") ? tainacan_plugin.tainacan_media_components : {};
			tainacan_plugin.tainacan_media_components['<?php echo $args['media_id'] ?>'] = <?php echo json_encode($args) ?>;
		</script>	

		<div id="<?php echo $media_id ?>" class="tainacan-media-component" data-module='item-gallery'>

			<?php if ( $args['has_media_main'] ) : ?>
				
				<!-- Slider main container -->
				<?php echo $args['before_main_div'] ?>
				<div id="<?php echo $args['media_main_id'] ?>" class="tainacan-media-component__swiper-main swiper-container <?php echo $args['class_main_div'] ?>">
					
					<!-- Additional required wrapper -->
					<?php echo $args['before_main_ul'] ?>
					<ul class="swiper-wrapper <?php echo $args['class_main_ul'] ?>">
						<?php foreach($media_items_main as $media_item) { ?>
							<li class="swiper-slide <?php echo $args['class_main_li'] ?>">
								<?php echo $media_item ?>
							</li>
						<?php }; ?>
					</ul>
					<?php echo $args['before_main_ul'] ?>

					<?php if ( $args['swiper_main_options'] && isset($args['swiper_main_options']['pagination']) ) : ?>
						<!-- If we need pagination -->
						<div class="swiper-pagination swiper-pagination_<?php echo $args['media_main_id'] ?>"></div>
					<?php endif; ?>

					<?php if ( $args['swiper_main_options'] && isset($args['swiper_main_options']['navigation']) ) : ?>
						<!-- If we need navigation buttons -->
						<div class="swiper-button-prev swiper-navigation-prev_<?php echo $args['media_main_id'] ?>"></div>
						<div class="swiper-button-next swiper-navigation-next_<?php echo $args['media_main_id'] ?>"></div>
					<?php endif; ?>
				</div>
				<?php echo $args['after_main_div'] ?>
			<?php endif; ?>

			<?php if ( $args['has_media_thumbs'] ) : ?>

				<!-- Slider thumbs container -->
				<?php echo $args['before_thumbs_div'] ?>
				<div id="<?php echo $args['media_thumbs_id'] ?>" class="tainacan-media-component__swiper-thumbs swiper-container <?php echo $args['class_thumbs_div'] ?>">
					
					<!-- Additional required wrapper -->
					<?php echo $args['before_thumbs_ul'] ?>
					<ul class="swiper-wrapper <?php echo $args['class_thumbs_ul'] ?>">
						<?php foreach($media_items_thumbs as $media_item) { ?>
							<li class="swiper-slide <?php echo $args['class_thumbs_li'] ?>">
								<?php echo $media_item ?>
							</li>
						<?php }; ?>
					</ul>
					<?php echo $args['before_thumbs_ul'] ?>

					<?php if ( $args['swiper_thumbs_options'] && isset($args['swiper_thumbs_options']['pagination']) ) : ?>
						<!-- If we need pagination -->
						<div class="swiper-paginations swiper-pagination_<?php echo $args['media_thumbs_id'] ?>"></div>
					<?php endif; ?>

					<?php if ( $args['swiper_thumbs_options'] && isset($args['swiper_thumbs_options']['navigation']) ) : ?>
						<!-- If we need navigation buttons -->
						<div class="swiper-button-prev swiper-navigation-prev_<?php echo $args['media_thumbs_id'] ?>"></div>
						<div class="swiper-button-next swiper-navigation-next_<?php echo $args['media_thumbs_id'] ?>"></div>
					<?php endif; ?>

					<!-- These elements will create a gradient on the side of the carousel -->
					<div class="swiper-start-border"></div>
					<div class="swiper-end-border"></div>
				</div>
				<?php echo $args['after_thumbs_div'] ?>
			<?php endif; ?>

		</div>

	<?php endif; ?> <!-- End of if ($args['has_media_main'] || $args['has_media_thumbs'] ) -->
	
<?php
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
	<?php echo $args['before_slide_content'] ?>

	<div class="swiper-slide-content <?php echo $args['class_slide_content'] ?>">

		<?php if ( isset($args['media_content']) && !empty($args['media_content']) && $args['media_content'] !== false ) :?>
			<?php echo $args['media_content'] ?>
		<?php else: ?>
			<img src="<?php echo tainacan_get_the_mime_type_icon($args['media_type']) ?>" alt="<?php echo ( !empty($args['media_title']) ? $args['media_title'] : __('File', 'tainacan') ) ?>" >
		<?php endif; ?>
		
		<?php echo $args['before_slide_metadata'] ?>

		<?php if ( !empty($args['media_title']) || !empty($args['description']) || !empty($args['media_caption']) ) : ?>
			<div class="swiper-slide-metadata  <?php echo $args['class_slide_metadata'] ?>">
				<?php if ( !empty($args['media_caption']) ) :?>
					<span class="swiper-slide-metadata__caption">
						<?php echo $args['media_caption'] ?>
						<br>
					</span>
				<?php endif; ?>	
				<?php if ( !empty($args['media_title']) ) :?>
					<span class="swiper-slide-metadata__name">
						<?php echo $args['media_title'] ?>
					</span>
				<?php endif; ?>
				<br>
				<?php if ( !empty($args['media_description']) ) :?>
					<span class="swiper-slide-metadata__description">
						<?php echo $args['media_description'] ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( !empty($args['media_content_full']) ) : ?>
			<div class="media-full-content" style="display: none; position: absolute; visibility: hidden;"><?php echo $args['media_content_full'] ?></div>
		<?php endif; ?>

		<?php echo $args['after_slide_metadata'] ?>

	</div>

	<?php echo $args['after_slide_content'] ?>

<?php

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * Div with content necessay to render the photowipe modal
 *
 * @return string
 */
function tainacan_get_the_media_modal_layer() {
?> 
    <!-- Root element of PhotoSwipe lightbox. Must have class pswp. -->
    <div class="tainacan-photoswipe-layer pswp" tabindex="-1" role="dialog" aria-hidden="true">

        <!-- Background of PhotoSwipe. 
                It's a separate element, as animating opacity is faster than rgba(). -->
        <div class="pswp__bg"></div>

        <!-- Slides wrapper with overflow:hidden. -->
        <div class="pswp__scroll-wrap">

            <!-- Container that holds slides. PhotoSwipe keeps only 3 slides in DOM to save memory. -->
            <!-- don't modify these 3 pswp__item elements, data is added later on. -->
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>

            <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
            <div class="pswp__ui pswp__ui--hidden">

                <div class="pswp__top-bar">

                    <!--  Controls are self-explanatory. Order can be changed. -->
                    <div class="pswp__counter"></div>

                    <div class="pswp__name"></div>

                    <button class="pswp__button pswp__button--close" title="<?php __('Close modal (Esc)', 'tainacan') ?>"></button>
                    <button class="pswp__button pswp__button--share" title="<?php __('Share', 'tainacan') ?>"></button>
                    <button class="pswp__button pswp__button--fs" title="<?php __('Toggle fullscreen', 'tainacan') ?>"></button>
                    <button class="pswp__button pswp__button--zoom" title="<?php __('Zoom in/out', 'tainacan') ?>"></button>

                    <!-- Preloader demo https://codepen.io/dimsemenov/pen/yyBWoR -->
                    <!-- element will get class pswp__preloader--active when preloader is running -->
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                                <div class="pswp__preloader__donut"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div>
                </div>

                <button class="pswp__button pswp__button--arrow--left" title="<?php __('Next', 'tainacan') ?>"></button>

                <button class="pswp__button pswp__button--arrow--right" title="<?php __('Previous', 'tainacan') ?>"></button>

                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>

    </div>
<?php
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
	return apply_filters('tainacan-get-collection-url', $url, $collection);
}					


/**
 * When visiting a collection archive or single, prints the collection url link
 *
 * @return void
 */
function tainacan_the_collection_url() {
	echo tainacan_get_the_collection_url();
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
	return apply_filters('tainacan-get-term-name', $name, $term);
}

/**
 * When visiting a taxonomy archive, prints the term name
 *
 * @return void
 */
function tainacan_the_term_name() {
	echo tainacan_get_the_term_name();
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
	return apply_filters('tainacan-get-term-description', $description, $term);
}

/**
 * When visiting a taxonomy archive, prints the term description
 *
 * @return void
 */
function tainacan_the_term_description() {
	echo tainacan_get_the_term_description();
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
 * Returns true or false indicating wether a certain property or metadata is
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

	$link = '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . $text . '</a>';

	echo $before . $link . $after;
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
	
	return $images_path . $icon_file . $image_size . '.png';
}

/**
 * Displays a carousel of items, the same of the gutenberg block
 *
 * @param array $args {
 	*     Optional. Array of arguments.
 	*     @type string  $collection_id					The Collection ID
 	*     @type string  $search_URL					A query string to fetch items from, if load strategy is 'search'
 	*     @type array   $selected_items				An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'
 	*     @type string  $load_strategy					Either 'search' or 'selection', to determine how items will be fetch
 	*     @type integer $max_items_number				Maximum number of items to be fetch
 	*     @type integer $max_tems_per_screen			Maximum columns of items to be displayed on a row of the carousel
 	*     @type string  $arrows_position				How the arrows will be positioned regarding the carousel ('around', 'left', 'right')
 	*     @type bool    $large_arrows					Should large arrows be displayed?
 	*     @type bool    $auto_play						Should the Caroulsel start automatically to slide?
 	*     @type integer $auto_play_speed				The time in s to translate to the next slide automatically 
 	*     @type bool    $loop_slides					Should slides loop when reached the end of the Carousel?
 	*     @type bool    $hide_title					Should the title of the items be displayed?
 	*     @type bool    $crop_images_to_square			Should it use the `tainacan-medium-size` instead of the `tainacan-medium-large-size`?
 	*     @type bool    $show_collection_header		Should it display a small version of the collection header?
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