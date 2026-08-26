<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Handles media embedding functionality for Tainacan.
 *
 * Provides enhanced embedding capabilities for various media types including
 * video, audio, and PDF files with responsive design support.
 *
 * @since 0.1.0
 */
class Embed {
	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Whether Tainacan's custom video/audio markup should be applied to the
	 * current autoembed() call.
	 *
	 * The wp_embed_handler_video/audio filters are registered once at plugin
	 * load, but they must only take effect when Tainacan itself is embedding
	 * media (Tainacan admin item pages, item single pages, the media page and
	 * the URL metadata type). This flag is toggled by Embed::embed() so the
	 * override never leaks into unrelated WordPress content.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private static $override_active = false;

	/**
	 * Item currently being embedded by Tainacan.
	 *
	 * @since 1.0.0
	 * @var object|null
	 */
	private static $override_item = null;

	/**
	 * Explicit thumbnail for the current autoembed() call, taking precedence
	 * over the item thumbnail (e.g. an image attachment paired with a video
	 * file by sharing the same file name).
	 *
	 * @since 1.0.0
	 * @var array|null
	 */
	private static $override_thumbnail = null;

	/**
	 * Available aspect ratios for responsive embeds.
	 *
	 * @since 0.1.0
	 *
	 * @var array Array of aspect ratio configurations.
	 */
	private static $aspect_ratios = array(
		// Common video resolutions.
		array("ratio" => '2.33', "className" => 'tainacan-embed-aspect-21-9'),
		array("ratio" => '2.00', "className" => 'tainacan-embed-aspect-18-9'),
		array("ratio" => '1.78', "className" => 'tainacan-embed-aspect-16-9'),
		array("ratio" => '1.33', "className" => 'tainacan-embed-aspect-4-3'), 
		// Vertical video and instagram square video support.
		array("ratio" => '1.00', "className" => 'tainacan-embed-aspect-1-1' ),
		array("ratio" => '0.75', "className" => 'tainacan-embed-aspect-3-4'),
		array("ratio" => '0.56', "className" => 'tainacan-embed-aspect-9-16'),
		array("ratio" => '0.50', "className" => 'tainacan-embed-aspect-1-2' )
	);
	
	/**
	 * Initializes the embed functionality.
	 *
	 * Sets up WordPress hooks for video, audio, and PDF embedding,
	 * and enqueues necessary styles for responsive embeds.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	protected function init() {
		
		/**
		 * Replace default WordPress embedders with Tainacan media markup.
		 */
		add_filter('wp_embed_handler_video', [$this, 'filter_video_embed'], 10, 4);
		add_filter('wp_embed_handler_audio', [$this, 'filter_audio_embed'], 10, 4);
		
		/**
		 * Add responsiveness to embeds
		 */
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_css' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'add_css' ) );

		/**
		 * ADD PDF Embed handler using PDF.js
		 * @var [type]
		 */
		wp_embed_register_handler( 'pdf', '#^https?://.+?\.(pdf)$#i', [$this, 'pdf_embed_handler'] );

		/**
		 * Hide image attachments that serve as miniatures for sibling videos
		 * from the item gallery, as they are displayed in the video place.
		 */
		add_filter( 'tainacan-get-the-attachments', [ $this, 'filter_video_companion_images' ], 10, 2 );

	}
	
	/**
	 * Filters video embed output to use HTML5 video tags.
	 *
	 * @since 0.1.0
	 *
	 * @param string $video    The current video embed HTML.
	 * @param array  $attr     Embed attributes.
	 * @param string $url      The video URL.
	 * @param array  $rawattr  Raw embed attributes.
	 * @return string Placeholder HTML for a Tainacan video embed.
	 */
	public function filter_video_embed($video, $attr, $url, $rawattr) {
		
		// Only apply Tainacan's video markup while Tainacan is embedding its own content.
		if ( ! self::$override_active ) {
			return $video;
		}

		if ( ! $this->is_video_lazyload_enabled() ) {
			return $this->get_video_embed( $attr, $url );
		}

		$thumbnail = $this->get_video_thumbnail();
		$video_dimensions = '';
		if ( ! empty( $attr['width'] ) ) {
			$video_width = absint( $attr['width'] );
			if ( $video_width ) {
				$video_dimensions .= sprintf( ' data-video-width="%d"', $video_width );
			}
		}
		if ( ! empty( $attr['height'] ) ) {
			$video_height = absint( $attr['height'] );
			if ( $video_height ) {
				$video_dimensions .= sprintf( ' data-video-height="%d"', $video_height );
			}
		}

		$image_dimensions = '';
		if ( ! empty( $thumbnail['width'] ) ) {
			$image_dimensions .= sprintf( ' width="%d"', absint( $thumbnail['width'] ) );
		}
		if ( ! empty( $thumbnail['height'] ) ) {
			$image_dimensions .= sprintf( ' height="%d"', absint( $thumbnail['height'] ) );
		}

		return sprintf(
			'<a class="tainacan-video-lazyload" href="%1$s" data-video-src="%1$s"%2$s aria-label="%3$s"><img src="%4$s"%5$s alt=""><span class="tainacan-video-lazyload__play" aria-hidden="true"></span></a>',
			esc_url( $url ),
			$video_dimensions,
			esc_attr__( 'Play video', 'tainacan' ),
			esc_url( $thumbnail['url'] ),
			$image_dimensions
		);
		
	}
	
	/**
	 * Filters audio embed output to use HTML5 audio tags.
	 *
	 * @since 0.1.0
	 *
	 * @param string $audio    The current audio embed HTML.
	 * @param array  $attr     Embed attributes.
	 * @param string $url      The audio URL.
	 * @param array  $rawattr  Raw embed attributes.
	 * @return string Modified audio embed HTML.
	 */
	public function filter_audio_embed($audio, $attr, $url, $rawattr) {
		
		// Only apply Tainacan's audio markup while Tainacan is embedding its own content.
		if ( ! self::$override_active ) {
			return $audio;
		}
		
		if ( ! empty( $attr['width'] ) ) {
			$dimensions = sprintf( 'width="%d" ', (int) $attr['width'] );
		}
		
		$audio = sprintf('<audio controls="" src="%s" %s></audio>', esc_url( $url ), $dimensions);
		
		return $audio;
		
	}
	
	/**
	 * Checks whether video lazyload is enabled in Tainacan settings.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether video placeholders should be rendered.
	 */
	private function is_video_lazyload_enabled() {
		return rest_sanitize_boolean( get_option( 'tainacan_option_enable_video_lazyload', true ) );
	}

	/**
	 * Renders the scoped video output when lazyload is disabled.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $attr Video attributes.
	 * @param string $url  Video URL.
	 * @return string Video HTML.
	 */
	private function get_video_embed( $attr, $url ) {
		$dimensions = '';
		if ( ! empty( $attr['width'] ) && ! empty( $attr['height'] ) ) {
			$dimensions = sprintf( 'width="%d" ', absint( $attr['width'] ) );
		}

		return sprintf(
			'<video class="tainacan-video-embed" controls="" preload="none" playsinline="" %s src="%s"></video>',
			$dimensions,
			esc_url( $url )
		);
	}

	/**
	 * Gets the thumbnail to display before a Tainacan video is activated.
	 *
	 * @since 1.0.0
	 *
	 * @return array Thumbnail URL and dimensions.
	 */
	private function get_video_thumbnail() {
		$override = self::$override_thumbnail;
		if ( is_array( $override ) && ! empty( $override['url'] ) ) {
			return array(
				'url'    => $override['url'],
				'width'  => isset( $override['width'] ) ? (int) $override['width'] : 0,
				'height' => isset( $override['height'] ) ? (int) $override['height'] : 0,
			);
		}

		$thumbnail_sizes = array( 'tainacan-medium', 'medium_large', 'medium', 'large', 'full' );
		$item = self::$override_item;
		if ( is_object( $item ) && is_callable( array( $item, 'get_thumbnail' ) ) ) {
			$thumbnails = $item->get_thumbnail();
			if ( is_array( $thumbnails ) ) {
				foreach ( $thumbnail_sizes as $size ) {
					if ( ! empty( $thumbnails[ $size ] ) && is_array( $thumbnails[ $size ] ) && ! empty( $thumbnails[ $size ][0] ) ) {
						return array(
							'url'    => $thumbnails[ $size ][0],
							'width'  => isset( $thumbnails[ $size ][1] ) ? $thumbnails[ $size ][1] : 0,
							'height' => isset( $thumbnails[ $size ][2] ) ? $thumbnails[ $size ][2] : 0,
						);
					}
				}
			}
		}

		$placeholder = function_exists( 'tainacan_get_the_mime_type_icon' )
			? tainacan_get_the_mime_type_icon( 'video', 'medium' )
			: '';
		if ( empty( $placeholder ) ) {
			global $TAINACAN_BASE_URL;
			$placeholder = trailingslashit( $TAINACAN_BASE_URL ) . 'assets/images/placeholder_video_medium.png';
		}

		return array(
			'url'    => $placeholder,
			'width'  => 0,
			'height' => 0,
		);
	}

	/**
	 * Finds an image attachment that pairs with a video attachment by sharing
	 * the same file name (minus extension).
	 *
	 * Content managers can upload e.g. interview.mp4 together with interview.jpg
	 * so the video lazyload placeholder shows a real miniature instead of the
	 * generic video icon.
	 *
	 * @since 1.0.0
	 *
	 * @param int $attachment_id Video attachment ID.
	 * @return int|null Paired image attachment ID, or null when none is found.
	 */
	public function get_video_companion_attachment_id( $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		$attachment = $attachment_id ? get_post( $attachment_id ) : null;

		if (
			! $attachment instanceof \WP_Post
			|| ! wp_attachment_is( 'video', $attachment )
			|| empty( $attachment->post_parent )
		) {
			return null;
		}

		$file = get_attached_file( $attachment_id );
		$file_name = $file ? pathinfo( $file, PATHINFO_FILENAME ) : '';
		if ( '' === $file_name ) {
			return null;
		}

		$siblings = get_posts( array(
			'post_type'      => 'attachment',
			'post_parent'    => (int) $attachment->post_parent,
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'exclude'        => array( $attachment_id ),
		) );

		foreach ( $siblings as $sibling ) {
			$sibling_file = get_attached_file( $sibling->ID );
			$sibling_name = $sibling_file ? pathinfo( $sibling_file, PATHINFO_FILENAME ) : '';

			if ( '' !== $sibling_name && 0 === strcasecmp( $file_name, $sibling_name ) ) {
				return (int) $sibling->ID;
			}
		}

		return null;
	}

	/**
	 * Resolves the thumbnail of a video's paired image attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param int $attachment_id Video attachment ID.
	 * @return array|null Thumbnail URL and dimensions, or null when no image is paired.
	 */
	public function get_video_companion_thumbnail( $attachment_id ) {
		$sibling_id = $this->get_video_companion_attachment_id( $attachment_id );
		if ( ! $sibling_id ) {
			return null;
		}

		foreach ( array( 'tainacan-medium', 'medium_large', 'medium', 'large', 'full' ) as $size ) {
			$src = wp_get_attachment_image_src( $sibling_id, $size );
			if ( is_array( $src ) ) {
				return array(
					'url'    => $src[0],
					'width'  => (int) $src[1],
					'height' => (int) $src[2],
				);
			}
		}

		// No generated image sizes: fall back to the original file URL.
		$url = wp_get_attachment_url( $sibling_id );
		if ( $url ) {
			return array(
				'url'    => $url,
				'width'  => 0,
				'height' => 0,
			);
		}

		return null;
	}

	/**
	 * Removes image attachments that serve as miniatures for sibling videos
	 * from item attachment lists.
	 *
	 * When an image pairs with a non-document video of the same item, it is
	 * displayed inside the video placeholder and showing it again in the
	 * gallery would duplicate the content. The document video is excluded from
	 * the pairing because it always uses the item's own thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param array                          $attachments List of attachment posts.
	 * @param \Tainacan\Entities\Item|null   $item        Owning item.
	 * @return array Filtered attachment list.
	 */
	public function filter_video_companion_images( $attachments, $item = null ) {
		if ( ! is_array( $attachments ) || empty( $attachments ) || ! $this->is_video_lazyload_enabled() ) {
			return $attachments;
		}

		$item_id = 0;
		if ( is_object( $item ) && method_exists( $item, 'get_id' ) && (int) $item->get_id() ) {
			$item_id = (int) $item->get_id();
		} elseif ( ! empty( $attachments[0] ) && $attachments[0] instanceof \WP_Post && $attachments[0]->post_parent ) {
			$item_id = (int) $attachments[0]->post_parent;
		}
		if ( ! $item_id ) {
			return $attachments;
		}

		$excluded_ids = array();
		if (
			is_object( $item )
			&& method_exists( $item, 'get_document_type' )
			&& method_exists( $item, 'get_document' )
			&& $item->get_document_type() === 'attachment'
			&& (int) $item->get_document()
		) {
			$excluded_ids[] = (int) $item->get_document();
		}

		$videos = get_posts( array(
			'post_type'      => 'attachment',
			'post_parent'    => $item_id,
			'post_mime_type' => 'video',
			'posts_per_page' => -1,
			'exclude'        => $excluded_ids,
			'fields'         => 'ids',
		) );
		if ( empty( $videos ) ) {
			return $attachments;
		}

		$video_names = array();
		foreach ( $videos as $video_id ) {
			$file = get_attached_file( $video_id );
			$name = $file ? pathinfo( $file, PATHINFO_FILENAME ) : '';
			if ( '' !== $name ) {
				$video_names[ mb_strtolower( $name ) ] = true;
			}
		}
		if ( empty( $video_names ) ) {
			return $attachments;
		}

		$result = array();
		foreach ( $attachments as $attachment ) {
			if ( $attachment instanceof \WP_Post && wp_attachment_is( 'image', $attachment ) ) {
				$file = get_attached_file( $attachment->ID );
				$name = $file ? pathinfo( $file, PATHINFO_FILENAME ) : '';
				if ( '' !== $name && isset( $video_names[ mb_strtolower( $name ) ] ) ) {
					continue;
				}
			}
			$result[] = $attachment;
		}

		return $result;
	}

	/**
	 * Runs WordPress autoembed on a URL while applying Tainacan's custom
	 * video/audio markup.
	 *
	 * WordPress processes media URLs in content through the registered embed
	 * handlers. Tainacan's own markup must only be produced for media that
	 * Tainacan itself embeds (item documents, attachments, URL metadata and
	 * the media page), never for unrelated WordPress content. This wrapper
	 * scopes the override to exactly those calls.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $url       The URL to embed.
	 * @param object|null $item      The Tainacan item owning the embed.
	 * @param array|null  $thumbnail Explicit thumbnail (url/width/height) taking
	 *                                precedence over the item thumbnail.
	 * @return string The embed HTML, or the original URL when autoembed fails.
	 */
	public function embed( $url, $item = null, $thumbnail = null ) {
		global $wp_embed;

		$previous_override_active = self::$override_active;
		$previous_override_item = self::$override_item;
		$previous_override_thumbnail = self::$override_thumbnail;
		self::$override_active = true;
		self::$override_item = $item;
		self::$override_thumbnail = is_array( $thumbnail ) && ! empty( $thumbnail['url'] ) ? $thumbnail : null;

		try {
			return $wp_embed->autoembed( $url );
		} finally {
			self::$override_active = $previous_override_active;
			self::$override_item = $previous_override_item;
			self::$override_thumbnail = $previous_override_thumbnail;
		}
	}
	/**
	 * Handles PDF file embedding using iframe.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $rawattr   Raw embed attributes.
	 * @return string PDF embed HTML.
	 */
	public function pdf_embed_handler($matches, $attr, $url, $rawattr) {
		global $TAINACAN_BASE_URL;
		
		$defaults = array(
			'width' => '100%',
			'height' => '640px'
		);
		
		$args = array_merge($attr, $defaults);
		
		$dimensions = '';
		if ( ! empty( $args['width'] ) && ! empty( $args['height'] ) ) {
			$dimensions .= sprintf( "width='%s' ", $args['width'] );
			$dimensions .= sprintf( "height='%s' ", $args['height'] );
		}

		$pdf = sprintf('<iframe id="iframePDF" name="iframePDF" src="%s" %s allowfullscreen webkitallowfullscreen></iframe>', esc_url( $url ), $dimensions );
		return $pdf;
	}
	
	/**
	 * Retrieves the thumbnail URL, if provided, for a given URL.
	 * 
	 * @since 0.1.0
	 *
	 * @param string $url The URL for the content.
	 * @return string|null The thumbnail URL or null on failure.
	 */
	public function oembed_get_thumbnail($url) {
		
		add_filter( 'oembed_dataparse', [$this, 'oembed_get_thumbnail_filter'], 10, 3);
		$return = wp_oembed_get($url);
		remove_filter( 'oembed_dataparse', [$this, 'oembed_get_thumbnail_filter']);
		return $return;
		
	}
	/**
	 * Filters oEmbed data to extract thumbnail URL.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed  $return The oEmbed return data.
	 * @param object $data   The oEmbed data object.
	 * @param string $url    The original URL.
	 * @return string|null The thumbnail URL or null.
	 */
	public function oembed_get_thumbnail_filter($return, $data, $url) {
		
		if ( isset($data->thumbnail_url) ) {
			return $data->thumbnail_url;
		}
		
		return null;
		
	}

	/**
	 * Adds inline CSS for responsive embeds.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	/**
	 * Gets the CSS styles for responsive embeds. (Too small to be a separate file)
	 *
	 * This CSS copies most of Gutenberg's logic for responsive blocks,
	 * but uses different classes to avoid future conflicts.
	 * Check their original css: /packages/block-library/src/embed/style.scss
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS content for responsive embeds.
	 */
	private static function get_embeds_css() {
		return '/* TAINACAN EMBEDS 
		* This file copies most of Gutenberg\'s logic for responsive blocks,
		* but uses different classes to avoid future conflicts.
		* Check their original css: /packages/block-library/src/embed/style.scss
		*/

		/* 
		* The embed container is in a `figure` element, and many themes zero this out.
		* This rule explicitly sets it, to ensure at least some bottom-margin in the flow.
		*/
		:not(.wp-block-embed__wrapper)>.tainacan-content-embed {
			margin-bottom: 1em;
			margin-left: 0;
			margin-right: 0;
			clear: both;
		}
		/* Don\'t allow iframe to overflow it\'s container. */
		:not(.wp-block-embed__wrapper)>.tainacan-content-embed iframe {
			max-width: 100%;
		}
		:not(.wp-block-embed__wrapper)>.tainacan-content-embed .tainacan-content-embed__wrapper {
			position: relative;
		}
		/* Add responsiveness to embeds with aspect ratios. */
		:not(.wp-block-embed__wrapper)>.tainacan-has-aspect-ratio .tainacan-content-embed__wrapper::before {
			content: "";
			display: block;
			padding-top: 50%; /* Default to 2:1 aspect ratio. */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-has-aspect-ratio iframe {
			position: absolute;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			height: 100%;
			width: 100%;
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-21-9 .tainacan-content-embed__wrapper::before {
			padding-top: 42.85%; /* 9 / 21 * 100 */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-18-9 .tainacan-content-embed__wrapper::before {
			padding-top: 50%; /* 9 / 18 * 100 */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-16-9 .tainacan-content-embed__wrapper::before {
			padding-top: 56.25%; /* 9 / 16 * 100 */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-4-3 .tainacan-content-embed__wrapper::before {
			padding-top: 75%; /* 3 / 4 * 100 */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-1-1 .tainacan-content-embed__wrapper::before {
			padding-top: 100%; /* 1 / 1 * 100 */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-9-16 .tainacan-content-embed__wrapper::before {
			padding-top: 177.77%; /* 16 / 9 * 100 */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-3-4 .tainacan-content-embed__wrapper::before {
			padding-top: 133.33%; /* 4 / 3 * 100 */
		}
		:not(.wp-block-embed__wrapper)>.tainacan-embed-aspect-1-2 .tainacan-content-embed__wrapper::before {
			padding-top: 200%; /* 2 / 1 * 100 */
		}
		.tainacan-video-embed {
			display: block;
			width: 100%;
			height: auto;
			max-width: 100%;
			aspect-ratio: 16 / 9;
			object-fit: contain;
			background: #000;
			pointer-events: auto;
		}
		.tainacan-video-lazyload {
			position: relative;
			display: block;
			width: 100%;
			max-width: 100%;
			background: #000;
			cursor: pointer;
			text-decoration: none;
		}
		.tainacan-video-lazyload img {
			display: block;
			width: 100%;
			height: auto;
			max-width: 100%;
			object-fit: contain;
			aspect-ratio: 16 / 9;
		}
		.tainacan-video-lazyload__play {
			position: absolute;
			top: 50%;
			left: 50%;
			width: 3.5rem;
			height: 3.5rem;
			border-radius: 50%;
			background: rgba(0, 0, 0, 0.7);
			transform: translate(-50%, -50%);
		}
		.tainacan-video-lazyload__play::before {
			content: "";
			position: absolute;
			top: 50%;
			left: 50%;
			border-top: 0.65rem solid transparent;
			border-bottom: 0.65rem solid transparent;
			border-left: 1rem solid #fff;
			transform: translate(-35%, -50%);
		}
		.tainacan-video-lazyload:hover .tainacan-video-lazyload__play,
		.tainacan-video-lazyload:focus .tainacan-video-lazyload__play {
			background: rgba(0, 0, 0, 0.9);
		}
		.tainacan-video-lazyload:focus {
			outline: 2px solid currentColor;
			outline-offset: 3px;
		}
		.tainacan-video-lazyload__video {
			display: block;
			width: 100%;
			height: auto;
			max-width: 100%;
		}
		';
	}

	/**
	 * Adds inline CSS for responsive embeds.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_css() {
		// Register a minimal style handle and add inline CSS
		wp_register_style( 'tainacan-embeds-inline', false, array(), TAINACAN_VERSION  );
		wp_enqueue_style( 'tainacan-embeds-inline' );
		wp_add_inline_style( 'tainacan-embeds-inline', self::get_embeds_css() );
	}

	/**
	 * Adds responsive wrapper classes based on aspect ratio.
	 *
	 * This code is heavily inspired by Gutenberg plugin's "getClassNames" function.
	 * Check their source code for more details: /packages/block-library/src/embed/util.js
	 *
	 * @since 0.1.0
	 *
	 * @param string $html The preview HTML that possibly contains an iframe with width and height set.
	 * @return string HTML with responsive wrapper classes added.
	 */
	public function add_responsive_wrapper( $html ) {
	
		$height = false;
		$width = false;
		
		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_clear_errors();

		// If we have a fixed aspect iframe, and it's a responsive embed content.
		if ($dom) {
			$externalContentElement = $dom->getElementsByTagName('iframe');
			
			if (!$externalContentElement)
				$externalContentElement = $dom->getElementsByTagName('embed');
				
			if (!$externalContentElement)
				$externalContentElement = $dom->getElementsByTagName('object');
			
			if ($externalContentElement) {
				foreach($externalContentElement as $element) {
					foreach($element->attributes as $attribute) {
						if ($attribute->nodeName == 'width')
							$width = $attribute->nodeValue;
						if ($attribute->nodeName == 'height')
							$height = $attribute->nodeValue;
						
						if ($attribute->nodeName == 'class' && $attribute->nodeValue == 'wp-embedded-content') {
							$height = false;
							$width = false;
							break;
						} 
					}
				}
			}

			if ( $height && $width ) {

				// Removes 'px' from the end if it was passed
				$height = preg_split('/px$/', $height)[0];
				$width = preg_split('/px$/', $width)[0];

				// If even then we are still not using a numeric value, it is probably the case of a 100%
				$height = is_numeric($height) ? $height : 567;
				$width = is_numeric($width) ? $width : 1024;

				$aspect_ratio = number_format(( $width / $height ), 2, '.', "");
	
				// Given the actual aspect ratio, find the widest ratio to support it.
				for ($ratioIndex = 0; $ratioIndex < count(self::$aspect_ratios); $ratioIndex++) {

					$potentialRatio = self::$aspect_ratios[ $ratioIndex ];
					if ( $aspect_ratio >= $potentialRatio['ratio'] ) {
						$class = $potentialRatio['className'] . ' tainacan-content-embed tainacan-has-aspect-ratio';
						return '<figure class="' . $class . '"><div class="tainacan-content-embed__wrapper">' . $html . '</div></figure>';
					}
				}
			}
		}
	
		return $html;
	}
	 
}