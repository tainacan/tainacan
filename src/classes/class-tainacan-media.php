<?php
namespace Tainacan;

/**
 * Class withe helpful methods to handle media in Tainacan
 */
class Media {
	
	private static $instance = null;
	private static $file_handle = null;
	private static $file_name = null;

		public static function get_instance() {
				if(!isset(self::$instance)) {
						self::$instance = new self();
				}

				return self::$instance;
		}

	/**
	 * Insert an attachment from an URL address.
	 *
	 * @param  String $url 
	 * @param  Int    $post_id (optional) the post this attachement should be attached to. empty for none
	 * @return Int|false    Attachment ID. False on failure
	 */
	public function insert_attachment_from_url($url, $post_id = null) {
		$filename = $this->save_remote_file($url);
		
		if( !file_exists($filename) ) {
			return false;
		}
		
		$file = fopen($filename,'r');
		
		if (false === $file) {
			return false;
		}

		return $this->insert_attachment_from_blob($file, basename($url), $post_id);
		
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
		
		return $this->insert_attachment_from_blob(fopen($filename,'r'), basename($filename), $post_id);
		
	}

		/**
		 * Avoid memory overflow problems with large files (Exceeded maximum memory limit of PHP)
		 *
		 * @param $url
		 * @return string the file path
		 */
		public function save_remote_file($url) {

				set_time_limit(0);

				$filename = tempnam(sys_get_temp_dir(), basename($url));

				# Open the file for writing...
				self::$file_handle = fopen($filename, 'w+');
				self::$file_name = $filename;

				$callback = function ($ch, $str)  {
						$len = fwrite(self::$file_handle, $str);
						return $len;
				};

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FILE, self::$file_handle);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); # optional
				curl_setopt($ch, CURLOPT_TIMEOUT, -1); # optional: -1 = unlimited, 3600 = 1 hour
				curl_setopt($ch, CURLOPT_VERBOSE, false); # Set to true to see all the innards

				# Only if you need to bypass SSL certificate validation
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

				# Assign a callback function to the CURL Write-Function
				curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);

				# Exceute the download - note we DO NOT put the result into a variable!
				curl_exec($ch);

				# Close CURL
				curl_close($ch);

				# Close the file pointer
				fclose(self::$file_handle);

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
		} else {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$mime_type = finfo_file( $finfo, $filename );
			finfo_close( $finfo );
			return $mime_type;
		}
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
			//$imagick->setIteratorIndex(0);
			$imagick->setImageFormat('jpg');
			$this->THROW_EXCPTION_ON_FATAL_ERROR = false;
			return $imagick->getImageBlob();
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
		
		$content_index_meta = 'document_content_index';

		if ($file == null) {
			$meta_id = update_post_meta( $item_id, $content_index_meta, null );
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

			$meta_id = update_post_meta( $item_id, $content_index_meta, $content );
		} catch(Exception $e) {
			error_log('Caught exception: ' .  $e->getMessage() . "\n");
			return false;
		}
	}
		
}