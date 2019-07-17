<?php
namespace Tainacan;

use Tainacan\Repositories;
use Tainacan\Entities;

/**
 * Class withe helpful methods to handle media in Tainacan
 */
class Private_Files {
	
	private static $instance = null;

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
		
		add_action('tainacan-insert', [$this, 'update_item_and_collection'], 10, 3);
		
		add_action('tainacan-bulk-edit-set-status', [$this, 'bulk_edit'], 10, 4);
		
		
	}

	/**
	 * Adds a filter to the upload_dir hook when uploading a new file 
	 * 
	 */
	function pre_upload($file){
		add_filter('upload_dir', [$this, 'change_upload_dir']);
		return $file;
	}
	
	/**
	 * Removes a filter to the upload_dir hook after uploading a new file 
	 * 
	 */
	function post_upload($fileinfo){
		remove_filter('upload_dir', [$this, 'change_upload_dir']);
		return $fileinfo;
	}
	
	/**
	 * Gets the base directory inside the uploads folder where 
	 * attachments and documents for items will be uploaded 
	 * 
	 * @return string The folder name
	 */
	function get_items_uploads_folder() {
		if (defined('TAINACAN_ITEMS_UPLOADS_DIR')) {
			return TAINACAN_ITEMS_UPLOADS_DIR;
		}
		return 'tainacan-items';
	}
	
	/**
	 * Gets the directory prefix to be added to folders holding
	 * attachments and documents for private items or collections 
	 * 
	 * @return string The folder prefix
	 */
	function get_private_folder_prefix() {
		if (defined('TAINACAN_PRIVATE_FOLDER_PREFIX')) {
			return TAINACAN_PRIVATE_FOLDER_PREFIX;
		}
		return '_x_';
	}
	
	/**
	 * Change the upload directory for items attachments and documents 
	 *
	 * It replaces the default WordPress strucutre, which is YYYY/MM/file 
	 * with a path containing the collection id and the item id inside the @see get_items_uploads_folder(): 
	 * ex: * tainacan-items/$collection_id/$item_id
	 *
	 * It also add a prefix in the folder name of private items or collections: 
	 * tainacan-items/$collection_id/_x_$item_id ($item_id is a private item)
	 * 
	 */
	function change_upload_dir($path) {
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
			
			$tainacan_basepath = $this->get_items_uploads_folder();
			$col_id_url = $item->get_collection_id();
			$col_id = $item->get_collection_id();
			$item_id_url = $item->get_id();
			$item_id = $item->get_id();
			
			$col_status = get_post_status_object($item->get_collection()->get_status());
			$item_status = get_post_status_object($item->get_status());
			
			if ( ! $col_status->public ) {
				$col_id = $this->get_private_folder_prefix() . $col_id;
			}
			if ( ! $item_status->public ) {
				$item_id = $this->get_private_folder_prefix() . $item_id;
			}
			
			$path['path'] = str_replace($path['subdir'], '', $path['path']); //remove default subdir (year/month)
			$path['url'] = str_replace($path['subdir'], '/' . $tainacan_basepath . '/' . $col_id_url . '/' . $item_id_url, $path['url']); 
			$path['path'] .= DIRECTORY_SEPARATOR . $tainacan_basepath . DIRECTORY_SEPARATOR . $col_id . '/' . $item_id;
			$path['subdir'] = DIRECTORY_SEPARATOR . $tainacan_basepath . DIRECTORY_SEPARATOR . $col_id . '/' . $item_id;
			
		}
		
		return $path;
		
	}
	
	/**
	 * Handles 404 returns looking for attachments inside the tainacan items uploads folder 
	 *
	 * When looking for a file that does not exists, it checks for relative prefixed folders. 
	 * If it finds the file, it then checks to see if current user have permission to see this file, based on
	 * the permission he/she have to read the related item.
	 * 
	 */
	function template_redirect() {
		
		if (is_404()) {
			
			$upload_dir = wp_get_upload_dir();
			$base_upload_url = preg_replace('/^https?:\/\//', '', $upload_dir['baseurl']);
			
			$requested_uri = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			
			if ( strpos($requested_uri, $base_upload_url) === false ) {
				// Not uploads 
				return;
			}
			
			$requested_uri = str_replace('/' . $this->get_private_folder_prefix(), '/', $requested_uri);
			
			$file_path = \str_replace( '/', DIRECTORY_SEPARATOR, str_replace($base_upload_url, '', $requested_uri) );
			
			$file = $upload_dir['basedir'] . $file_path;
			
			$existing_file = false;
			
			$file_dirs = explode(DIRECTORY_SEPARATOR, $file);
			$file_dirs_size = sizeof($file_dirs);
			
			$item_id = $file_dirs[$file_dirs_size-2];
			$collection_id = $file_dirs[$file_dirs_size-3];
			
			// private item 
			$prefixed_file = str_replace( DIRECTORY_SEPARATOR . $item_id . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . $this->get_private_folder_prefix() . $item_id . DIRECTORY_SEPARATOR, $file);
			
			if ( \file_exists( $prefixed_file ) ) {
				$existing_file = $prefixed_file;
			}
			// private collection 
			$prefixed_collection = str_replace( DIRECTORY_SEPARATOR . $collection_id . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . $this->get_private_folder_prefix() . $collection_id . DIRECTORY_SEPARATOR, $file);
			if ( !$existing_file && \file_exists( $prefixed_collection ) ) {
				$existing_file = $prefixed_collection;
			}
			// private both 
			$prefixed_both = str_replace( DIRECTORY_SEPARATOR . $collection_id . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . $this->get_private_folder_prefix() . $collection_id . DIRECTORY_SEPARATOR, $prefixed_file);
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
	
	/**
	 * Filters the image_get_intermediate_size hook to strip out the 
	 * private uploads folder prefix from the attachments URLs
	 */
	function image_get_intermediate_size($data, $post_id, $size) {
		
		$data['path'] = str_replace(DIRECTORY_SEPARATOR . $this->get_private_folder_prefix(), DIRECTORY_SEPARATOR, $data['path']);
		$data['url'] = str_replace('/' . $this->get_private_folder_prefix(), '/', $data['url']);
		
		return $data;
		
	}
	
	/**
	 * Filters the wp_get_attachment_url hook to strip out the 
	 * private uploads folder prefix from the attachments URLs
	 */
	function wp_get_attachment_url($url, $post_id) {
		$url = str_replace('/' . $this->get_private_folder_prefix(), '/', $url);
		return $url;
	}
	
	/**
	 * When an item or collection is saved, it checks if the satus was changed and 
	 * if the items upload directory mus be renamed to add or remove the 
	 * private folder prefix 
	 */
	function update_item_and_collection($obj, $diffs, $is_update) {
		
		// updating collection or item
		if ( $is_update ) {
			
			$folder = DIRECTORY_SEPARATOR;
			$check_folder = DIRECTORY_SEPARATOR;
			$check = false;
			
			if ( $obj instanceof \Tainacan\Entities\Collection ) {
				
				$status_obj = get_post_status_object($obj->get_status());
				
				$folder .= $status_obj->public ? $obj->get_id() : $this->get_private_folder_prefix() . $obj->get_id();
				$check_folder .= ! $status_obj->public ? $obj->get_id() : $this->get_private_folder_prefix() . $obj->get_id();
				
				$check = true;
				
			}
			
			if ( $obj instanceof \Tainacan\Entities\Item ) {
				
				$collection = $obj->get_collection();
				$col_status_object = get_post_status_object($collection->get_status());
				
				$folder 	  .= $col_status_object->public ? $collection->get_id() : $this->get_private_folder_prefix() . $collection->get_id() . DIRECTORY_SEPARATOR;
				$check_folder .= $col_status_object->public ? $collection->get_id() : $this->get_private_folder_prefix() . $collection->get_id() . DIRECTORY_SEPARATOR;
				
				$folder 	  .= DIRECTORY_SEPARATOR;
				$check_folder .= DIRECTORY_SEPARATOR;
				
				$status_obj = get_post_status_object($obj->get_status());
				
				$folder .= $status_obj->public ? $obj->get_id() : $this->get_private_folder_prefix() . $obj->get_id();
				$check_folder .= ! $status_obj->public ? $obj->get_id() : $this->get_private_folder_prefix() . $obj->get_id();
				
				$check = true;
				
			}
			
			if ($check) {
				
				$upload_dir = wp_get_upload_dir();
				$base_dir = $upload_dir['basedir'];
				$full_path = $base_dir . DIRECTORY_SEPARATOR . $this->get_items_uploads_folder() . $folder;
				$full_path_check = $base_dir . DIRECTORY_SEPARATOR . $this->get_items_uploads_folder() . $check_folder;
				
				if (\file_exists($full_path_check)) {
					rename($full_path_check, $full_path);
					do_action('tainacan-upload-folder-renamed', $full_path_check, $full_path);
				}
				
			}
			
		}
		
	}
	
}