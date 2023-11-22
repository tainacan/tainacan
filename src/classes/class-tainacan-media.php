<?php
namespace Tainacan;

/**
 * Class withe helpful methods to handle media in Tainacan
 */
class Media {

	private static $instance = null;
	private static $file_name = null;
	private $attachment_html_url_base = 'tainacan_attachment_html';

	public static $content_index_meta = 'document_content_index';

	public static function get_instance() {
			if(!isset(self::$instance)) {
					self::$instance = new self();
			}

			return self::$instance;
	}

	protected function __construct() {
		add_action( 'init', [$this, 'add_attachment_page_rewrite_rule'] );

		add_filter( 'query_vars', [$this, 'attachment_page_add_var'] );
		add_action( 'template_redirect', [$this, 'attachment_page'] );
	}

	public function add_attachment_page_rewrite_rule() {
		add_rewrite_rule(
			'^' . $this->attachment_html_url_base . '/([0-9]+)/?',
			'index.php?tainacan_attachment_page=$matches[1]',
			'top'
		);
	}

	public function add_css() {
		global $TAINACAN_BASE_URL;
		wp_enqueue_style( 'tainacan-media-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-media-page.css', [], TAINACAN_VERSION );
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

			return $this->insert_attachment_from_blob($file, basename($url), $post_id);
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

		if( !file_exists($filename) ) {
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
			throw new \Exception( "[save_remote_file]:" . implode("\n", $filename->get_error_messages()));
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

		if ( ! defined('TAINACAN_INDEX_PDF_CONTENT') || true !== TAINACAN_INDEX_PDF_CONTENT ) {
			return;
		}

		if ($file == null) {
			update_post_meta( $item_id, SELF::$content_index_meta, null );
			return true;
		}

		if ( ! \file_exists($file) ) {
			return false;
		}

		if ( $this->get_mime_content_type($file) != 'application/pdf') {
			return null;
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

		echo $output;

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
		} catch (\Exception $e) {
			return $this->get_default_image_blurhash();
		}
	}

}
