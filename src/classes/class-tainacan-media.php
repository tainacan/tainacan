<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Handles media functionality for Tainacan.
 *
 * Provides methods for managing images, attachments, and media-related features
 * including custom image sizes, attachment pages, and content indexing.
 *
 * @since 0.1.0
 */
class Media {
	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Current file name being processed.
	 *
	 * @since 0.1.0
	 *
	 * @var string|null
	 */
	private static $file_name = null;

	/**
	 * Base URL slug for attachment HTML pages.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $attachment_html_url_base = 'tainacan_attachment_html';

	/**
	 * Meta key for document content indexing.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $content_index_meta = 'document_content_index';

	/**
	 * Meta key for document content last index metadata.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $content_index_last = 'document_content_last_index';

	/**
	 * Initializes the media functionality.
	 *
	 * Sets up rewrite rules, query vars, and image sizes for Tainacan media handling.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'init', [$this, 'add_attachment_page_rewrite_rule'] );

		add_filter( 'query_vars', [$this, 'attachment_page_add_var'] );
		add_action( 'template_redirect', [$this, 'attachment_page'] );

		add_action( 'after_setup_theme', [$this, 'add_image_sizes'] );
		add_filter( 'image_size_names_choose', [$this, 'add_image_sizes_to_admin'] );
	}

	/**
	 * Registers custom image sizes for Tainacan.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_image_sizes() {
        add_image_size( 'tainacan-small', 40, 40, true );
        add_image_size( 'tainacan-medium', 275, 275, true );
        add_image_size( 'tainacan-medium-full', 205, 1500 );
        add_image_size( 'tainacan-large-full', 480, 860 );
    }

	/**
	 * Adds custom image sizes to the admin interface.
	 *
	 * @since 0.1.0
	 *
	 * @param array $sizes Existing image size options.
	 * @return array Modified image size options.
	 */
    public function add_image_sizes_to_admin( $sizes ) {
        return array_merge( $sizes, array(
            'tainacan-small'       => __( 'Tainacan small (40x40 - cropped)', 'tainacan' ),
            'tainacan-medium'      => __( 'Tainacan medium (275x275 - cropped)', 'tainacan' ),
            'tainacan-medium-full' => __( 'Tainacan medium full (205x1500 - not cropped)', 'tainacan' ),
            'tainacan-large-full'  => __( 'Tainacan large full (480x860 - not cropped)', 'tainacan' )
        ) );
    }

	/**
	 * Adds rewrite rule for attachment HTML pages.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_attachment_page_rewrite_rule() {
		add_rewrite_rule(
			'^' . $this->attachment_html_url_base . '/([0-9]+)/?',
			'index.php?tainacan_attachment_page=$matches[1]',
			'top'
		);
	}

	/**
	 * Gets the CSS styles for media attachment pages.
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS content for media attachment pages.
	 */
	private static function get_media_page_css() {
		return 'body:not([class]){ 
			margin: auto; 
			display: flex;
			flex-direction: column; 
			align-items: center; 
			justify-content: center; 
			width: 100%;
			height: 100%;
		}
		body:not([class]) > img {
			width: auto;
		}
		body:not([class]) > iframe {
			width: 100%;
			height: 100%;
			min-height: 80vh;
			border: none;
		}
		body:not([class]) > video {
			width: 100%;
			height: auto;
			min-height: 54px;
			max-height: 100%;
		}
		body:not([class]) > audio {
			width: 100%;
			height: auto;
			border-radius: 20px;
			background: black;
			min-height: 38px;
			max-height: 100%;
		}
		body:not([class]) > a,
		body:not([class]) > p {
			z-index: 99;
			padding: 1rem 4.33337vw;
			background: white;
			border-radius: 3px;
			word-wrap: break-word;
		}';
	}

	/**
	 * Adds inline CSS for media attachment pages. (Too small to be a separate file)
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_css() {
		// Register a minimal style handle and add inline CSS
		wp_register_style( 'tainacan-media-page', false, array(), TAINACAN_VERSION );
		wp_enqueue_style( 'tainacan-media-page' );
		wp_add_inline_style( 'tainacan-media-page', self::get_media_page_css() );
	}

	public function attachment_page_add_var($vars) {
		$vars[] = 'tainacan_attachment_page';
		return $vars;
	}

	private function flush_buffers(){
		if( ob_get_level() > 0 ) {
			ob_flush();
		}
		// flush();
	}

	private function get_file_name_from_url($url) {
		$response = wp_remote_head($url);
		if (is_wp_error($response)) {
			return basename($url);
		}
	
		$headers = wp_remote_retrieve_headers($response);
		$fileName = null;
	
		if (isset($headers['content-disposition'])) {
			if (preg_match('/filename="([^"]+)"/', $headers['content-disposition'], $matches)) {
				$fileName = $matches[1];
				return $fileName;
			}
		}
		return basename($url);
	}

	/**
	 * Insert an attachment from an URL address.
	 *
	 * @param  String $url
	 * @param  Int    $post_id (optional) the post this attachement should be attached to. empty for none
	 * @return Int|false    Attachment ID. False on failure
	 */
	public function insert_attachment_from_url($url, $post_id = null) {
		try {
			$filename = $this->save_remote_file($url);

			if( !file_exists($filename) ) {
				return false;
			}
			$this->flush_buffers();
			$file = file_get_contents($filename);

			if (false === $file) {
				return false;
			}

			return $this->insert_attachment_from_blob($file, $this->get_file_name_from_url($url), $post_id);
		} catch (\Exception $e) {
			error_log($e);
			return false;
		}

	}

	/**
	 * Insert an attachment from a local file.
	 *
	 * @param  String $filename The path to the file
	 * @param  Int    $post_id (optional) the post this attachement should be attached to. empty for none
	 * @return Int|false    Attachment ID. False on failure
	 */
	public function insert_attachment_from_file($filename, $post_id = null) {

		if( !file_exists($filename) || !is_file($filename) ) {
			return false;
		}
		$this->flush_buffers();
		return $this->insert_attachment_from_blob(file_get_contents($filename), basename($filename), $post_id);

	}

	/**
	 * Avoid memory overflow problems with large files (Exceeded maximum memory limit of PHP)
	 *
	 * @param $url
	 * @return string the file path
	 */
	public function save_remote_file($url) {
		// Include file.php
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$filename = \download_url($url, 900);
		if( is_wp_error($filename) ) {
			throw new \Exception( "[save_remote_file]:" . esc_html( implode( "\n", $filename->get_error_messages() ) ) );
		}
		return $filename;
	}


		/**
	 * Insert an attachment from an URL address.
	 *
	 * @param  blob $blob bitstream of the attachment
	 * @param  String $filename The filename that will be created
	 * @param  Int    $post_id (optional) the post this attachement should be attached to. empty for none
	 * @return Int|false    Attachment ID. False on failure
	 */
	public function insert_attachment_from_blob($blob, $filename, $post_id = null) {

		do_action('tainacan-pre-insert-attachment', $blob, $filename, $post_id);

		$upload = wp_upload_bits( $filename, null, $blob );
		if( !empty( $upload['error'] ) ) {
			return false;
		}

		if( @filesize($upload['file']) == 0 && is_resource($blob) ){
			$file_wordpress_stream = fopen( $upload['file'], 'r+');
			stream_copy_to_stream($blob, $file_wordpress_stream);

			if( file_exists(self::$file_name) ) unlink(self::$file_name);
		}

		$file_path = $upload['file'];
		$file_name = basename( $file_path );
		$file_type = wp_check_filetype( $file_name, null );
		$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
		$wp_upload_dir = wp_upload_dir();

		$guid = \str_replace($wp_upload_dir['basedir'], '', $file_path);
		$guid = $wp_upload_dir['baseurl'] .  $guid;

		$post_info = array(
			'guid'				=> $guid,
			'post_mime_type'	=> $file_type['type'],
			'post_title'		=> $attachment_title,
			'post_content'		=> '',
			'post_status'		=> 'inherit',
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $post_info, $file_path, $post_id );

		// Include image.php
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id,  $attach_data );

		do_action('tainacan-post-insert-attachment', $attach_id,  $attach_data, $post_id);

		return $attach_id;
	}

	/**
	 * Add support to get mime type content even when mime_content_type function is not available
	 * @param  string $filename The file name to check the mime type
	 * @return string mime type           @see \mime_content_type()
	 */
	function get_mime_content_type( $filename ){
		if (function_exists( 'mime_content_type' )) {
			return mime_content_type($filename);
		} else if( function_exists( 'finfo_open' ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$mime_type = finfo_file( $finfo, $filename );
			finfo_close( $finfo );
			return $mime_type;
		}
		$filetypes = \wp_check_filetype($filename);
		return isset($filetypes['type']) && $filetypes['type'] != false ? $filetypes['type'] : '';
	}

	/**
	 * Extract an image from the first page of a pdf file
	 *
	 * @param  string $filepath The pdf filepath in the server
	 * @return blob           bitstream of the image in jpg format
	 */
	public function get_pdf_cover($filepath) {
		$blob = apply_filters('tainacan-extract-pdf-cover', null, $filepath);
		if ($blob) {
			return $blob;
		}

		if (!class_exists('\Imagick')) {
			return null;
		}

		if ( $this->get_mime_content_type($filepath) != 'application/pdf') {
			return null;
		}

		if ( !is_readable( realpath($filepath) ) ) {
			return null;
		}

		try {
			register_shutdown_function(array($this, 'shutdown_function'));
			$this->THROW_EXCPTION_ON_FATAL_ERROR = true;
			$imagick = new \Imagick();
			$imagick->setResolution(72,72);
			$imagick->readImage($filepath . '[0]');
			$imagick->setImageFormat('jpg');
			$imagick->getImageBlob();
			$imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
			$this->THROW_EXCPTION_ON_FATAL_ERROR = false;
			return $imagick;
		} catch(\Exception $e) {
			return null;
		} catch (\Error $ex) {
			return null;
		}
	}

	private $THROW_EXCPTION_ON_FATAL_ERROR = false;
	public function shutdown_function() {
		if( $this->THROW_EXCPTION_ON_FATAL_ERROR )
			throw new \Exception("fatal error");
	}

	public function index_pdf_content($file, $item_id) {

		if ( ! (
			defined('TAINACAN_INDEX_PDF_CONTENT') 
				? ( true === TAINACAN_INDEX_PDF_CONTENT )
				: get_option( 'tainacan_option_index_pdf_content', false ) 
		) ) {
			return;
		}

		if ($file == null) {
			update_post_meta( $item_id, SELF::$content_index_meta, null );
			update_post_meta( $item_id, SELF::$content_index_last, null );
			return true;
		}

		if ( ! \file_exists($file) ) {
			return false;
		}

		if ( $this->get_mime_content_type($file) != 'application/pdf') {
			return null;
		}

		// Check if file has changed since last indexing
		$stored_file_info = get_post_meta($item_id, SELF::$content_index_last, true);
		
		// Get current file information with error handling
		$current_file_name = basename($file);
		$current_file_size = filesize($file);
		$current_mod_time = filemtime($file);
		
		// Validate file information
		if ($current_mod_time === false || $current_file_size === false) {
			// If we can't get file info, proceed with indexing to be safe
			error_log("Tainacan: Could not get file metadata for {$file} to make sure it has changed, proceeding with indexing anyways.");
		} else {
			// If file hasn't changed, skip re-indexing
			if (is_array($stored_file_info) && 
				isset($stored_file_info['file_name']) && 
				isset($stored_file_info['mod_time']) &&
				isset($stored_file_info['file_size']) &&
				$stored_file_info['file_name'] === $current_file_name && 
				$stored_file_info['mod_time'] === $current_mod_time &&
				$stored_file_info['file_size'] === $current_file_size) {
				return true;
			}
		}

		// Allow plugins to implement other approach to index pdf contents
		$alternate = apply_filters('tainacan-index-pdf', null, $file, $item_id);
		if ( ! \is_null($alternate) ) {
			return $alternate;
		}

		try {
			$parser = new \Smalot\PdfParser\Parser();
			$content = $parser->parseFile($file)->getText();

			$wp_charset = get_bloginfo('charset');
			$content_charset = mb_detect_encoding($content);
			$content = mb_convert_encoding($content, $wp_charset, $content_charset);
			update_post_meta( $item_id, SELF::$content_index_meta, $content );
			
			// Store file metadata for future change detection (only if we have valid data)
			if ($current_mod_time !== false && $current_file_size !== false) {
				$file_info = array(
					'file_name' => $current_file_name,
					'mod_time' => $current_mod_time,
					'file_size' => $current_file_size
				);
				update_post_meta( $item_id, SELF::$content_index_last, $file_info );
			}
			
		} catch(\Exception $e) {
			error_log('Caught exception: ' .  $e->getMessage() . "\n");
			return false;
		}
	}

	public function get_attachment_html_url($attachment_id) {
		return site_url( $this->attachment_html_url_base . '/' . (int) $attachment_id );
	}

	public function attachment_page() {
		$att_id = get_query_var('tainacan_attachment_page');
		
		if ( ! $att_id ) {
			return; // continue normal execution
		}
		
		$attachment = get_post($att_id);

		if ( $attachment instanceof \WP_Post && $attachment->post_type == 'attachment' ) {
			$parent = $attachment->post_parent;

			$item = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $parent );

			if ( $item instanceof \Tainacan\Entities\Item ) {

				if ( ! $item->can_read() ) {

					http_response_code(401);
					die;

				}

			} else {
				http_response_code(404);
				die;
			}

		} else {
			http_response_code(404);
			die;
		}

		$output = '';

		if ( wp_attachment_is_image($att_id) ) {

			$img = wp_get_attachment_image($attachment->ID, 'large');
			$output .= $img;

		} else {
			$this->add_css();
			wp_print_styles('tainacan-media-page');
			global $wp_embed;

			$url = wp_get_attachment_url($att_id);

			$embed = $wp_embed->autoembed($url);

			if ( esc_url($embed) == esc_url($url) ) {
				$output .= sprintf("<a href='%s' target='blank'>%s</a>", $url, $url);
			} else {
				$output .= $embed;
			}

		}

		echo wp_kses( $output, wp_kses_allowed_html('tainacan_content') );

		exit();

	}

	public function get_default_image_blurhash() {
		return apply_filters('tainacan-default-image-blurhash', "V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj");
	}

	public function get_image_blurhash($file_path, $width, $height) {
		try {
			if (
				!function_exists('imagecreatefromstring') ||
				!(version_compare(PHP_VERSION, '7.2.0') >= 0) ||
				!$image = @imagecreatefromstring(file_get_contents($file_path))
			) {
				return $this->get_default_image_blurhash();
			}
			if($image == false) {
				return $this->get_default_image_blurhash();
			}

			$max_width = 90;
			if( $width > $max_width ) {
				$image = imagescale($image, $max_width);
				$width = imagesx($image);
				$height = imagesy($image);
			}
			
			$pixels = [];
			for ($y = 0; $y < $height; ++$y) {
				$row = [];
				for ($x = 0; $x < $width; ++$x) {
					$index = imagecolorat($image, $x, $y);
					$colors = imagecolorsforindex($image, $index);
					$row[] = [$colors['red'], $colors['green'], $colors['blue']];
				}
				$pixels[] = $row;
			}
			$components_x = 5;
			$components_y = 4;
			$blurhash = \kornrunner\Blurhash\Blurhash::encode($pixels, $components_x, $components_y);
			return $blurhash;
		} catch (\Throwable $e) {
			return $this->get_default_image_blurhash();
		}
	}

}
