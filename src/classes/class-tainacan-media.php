<?php
namespace Tainacan;

/**
 * Class withe helpful methods to handle media in Tainacan
 */
class Media {
	
	private static $instance = null;

    public static function get_instance() {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
	
	protected function __construct() {
		
		
	}

	/**
	 * Insert an attachment from an URL address.
	 *
	 * @param  String $url 
	 * @param  Int    $post_id (optional) the post this attachement should be attached to. empty for none
	 * @return Int|false    Attachment ID. False on failure
	 */
	public function insert_attachment_from_url($url, $post_id = null) {
		if( !class_exists( '\WP_Http' ) )
			include_once( ABSPATH . WPINC . '/class-http.php' );

		$http = new \WP_Http();
		
		$response = $http->request( $url );
		
		if( !is_array($response) || !isset($response['response']) || $response['response']['code'] != 200 ) {
			return false;
		}
		
		return $this->insert_attachment_from_blob($response['body'], basename($url), $post_id);
		
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

		$upload = wp_upload_bits( $filename, null, $blob );
		if( !empty( $upload['error'] ) ) {
			return false;
		}

		$file_path = $upload['file'];
		$file_name = basename( $file_path );
		$file_type = wp_check_filetype( $file_name, null );
		$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
		$wp_upload_dir = wp_upload_dir();

		$post_info = array(
			'guid'				=> $wp_upload_dir['url'] . '/' . $file_name, 
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

		return $attach_id;
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
		
		if ( mime_content_type($filepath) != 'application/pdf') {
			return null;
		}
		
		$imagick = new \Imagick();
		$imagick->setResolution(72,72);
		$imagick->readImage($filepath . '[0]');  
        //$imagick->setIteratorIndex(0);
        $imagick->setImageFormat('jpg');
        return $imagick->getImageBlob();
	}
	
}