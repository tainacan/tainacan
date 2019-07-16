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
	
	protected function __construct() {
		add_filter('wp_handle_upload_prefilter', [$this, 'pre_upload']);
		add_filter('wp_handle_upload', [$this, 'post_upload']);
		
		add_action('template_redirect', [$this, 'template_redirect']);
		add_filter('image_get_intermediate_size', [$this, 'image_get_intermediate_size'], 10, 3);
		add_filter('wp_get_attachment_url', [$this, 'wp_get_attachment_url'], 10, 2);
		
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

		return $this->insert_attachment_from_blob(fopen($filename,'r'), basename($url), $post_id);
		
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
	
	
	function pre_upload($file){
		error_log('popopopopopo');
		add_filter('upload_dir', [$this, 'change_upload_dir']);
		return $file;
	}
	
	function post_upload($fileinfo){
		remove_filter('upload_dir', [$this, 'change_upload_dir']);
		return $fileinfo;
	}
	
	function change_upload_dir($path) {
		error_log (json_encode($path));
		$post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : false;
		
		if (false === $post_id) {
			return $path;
		}
		
		$theme_helper = \Tainacan\Theme_Helper::get_instance();
		
		$post = get_post($post_id);
		
		if ( !$theme_helper->is_post_an_item($post) ) {
			return $path;
		}
		
		$item = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $post_id );
		
		if ($item instanceof \Tainacan\Entities\Item) {
			
			$tainacan_basepath = '/tainacan-items/';
			$col_id_url = $item->get_collection_id();
			$col_id = $item->get_collection_id();
			$item_id_url = $item->get_id();
			$item_id = $item->get_id();
			
			if (true) {
				$col_id = '_x_' . $col_id;
			}
			
			$path['path'] = str_replace($path['subdir'], '', $path['path']); //remove default subdir (year/month)
			$path['url'] = str_replace($path['subdir'], $tainacan_basepath . $col_id_url . '/' . $item_id_url, $path['url']); 
			$path['path'] .= $tainacan_basepath . $col_id . '/' . $item_id;
			$path['subdir'] = $tainacan_basepath . $col_id . '/' . $item_id;
			
		}
		
		
		return $path;
		
	}
	
	function template_redirect() {
		
		if (is_404()) {
			
			$upload_dir = wp_get_upload_dir();
			$base_upload_url = preg_replace('/^https?:\/\//', '', $upload_dir['baseurl']);
			
			$requested_uri = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			
			if ( strpos($requested_uri, $base_upload_url) === false ) {
				// Not uploads 
				return;
			}
			
			$requested_uri = str_replace('/_x_', '/', $requested_uri);
			
			$file_path = \str_replace( '/', DIRECTORY_SEPARATOR, str_replace($base_upload_url, '', $requested_uri) );
			
			$file = $upload_dir['basedir'] . $file_path;
			
			$existing_file = false;
			
			$file_dirs = explode(DIRECTORY_SEPARATOR, $file);
			$file_dirs_size = sizeof($file_dirs);
			
			$item_id = $file_dirs[$file_dirs_size-2];
			$collection_id = $file_dirs[$file_dirs_size-3];
			
			// private item 
			$prefixed_file = str_replace( DIRECTORY_SEPARATOR . $item_id . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . '_x_' . $item_id . DIRECTORY_SEPARATOR, $file);
			
			if ( \file_exists( $prefixed_file ) ) {
				$existing_file = $prefixed_file;
			}
			// private collection 
			$prefixed_collection = str_replace( DIRECTORY_SEPARATOR . $collection_id . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . '_x_' . $collection_id . DIRECTORY_SEPARATOR, $file);
			if ( !$existing_file && \file_exists( $prefixed_collection ) ) {
				$existing_file = $prefixed_collection;
			}
			// private both 
			$prefixed_both = str_replace( DIRECTORY_SEPARATOR . $collection_id . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . '_x_' . $collection_id . DIRECTORY_SEPARATOR, $prefixed_file);
			if ( !$existing_file && \file_exists( $prefixed_collection ) ) {
				$existing_file = $prefixed_both;
			}
			
			if ($existing_file) {
				
				$item = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $item_id, (int) $collection_id );
				
				if ($item instanceof \Tainacan\Entities\Item && $item->can_read()) {
					//header('Content-Description: File Transfer');
					//header('Content-Type: application/octet-stream');
					header("Content-type: " . mime_content_type($existing_file));
					//header('Content-Disposition: attachment; filename="'.basename($file).'"');
					// header('Expires: 0');
					// header('Cache-Control: must-revalidate');
					// header('Pragma: public');
					// header('Content-Length: ' . filesize($file));
					\readfile($existing_file);
					
					die;
				}
				
				
			}
			
			
			
		}
		
	}
	
	function image_get_intermediate_size($data, $post_id, $size) {
		
		$data['path'] = str_replace(DIRECTORY_SEPARATOR . '_x_', DIRECTORY_SEPARATOR, $data['path']);
		$data['url'] = str_replace('/_x_', '/', $data['url']);
		
		return $data;
		
	}
	
	function wp_get_attachment_url($url, $post_id) {
		$url = str_replace('/_x_', '/', $url);
		return $url;
	}
	
}