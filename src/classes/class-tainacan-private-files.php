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
		
	}
	
	function pre_upload($file){
		add_filter('upload_dir', [$this, 'change_upload_dir']);
		return $file;
	}
	
	function post_upload($fileinfo){
		remove_filter('upload_dir', [$this, 'change_upload_dir']);
		return $fileinfo;
	}
	
	function get_items_uploads_folder() {
		if (defined('TAINACAN_ITEMS_UPLOADS_DIR')) {
			return TAINACAN_ITEMS_UPLOADS_DIR;
		}
		return 'tainacan-items';
	}
	
	function get_private_folder_prefix() {
		if (defined('TAINACAN_PRIVATE_FOLDER_PREFIX')) {
			return TAINACAN_PRIVATE_FOLDER_PREFIX;
		}
		return '_x_';
	}
	
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
	
	
	function image_get_intermediate_size($data, $post_id, $size) {
		
		$data['path'] = str_replace(DIRECTORY_SEPARATOR . $this->get_private_folder_prefix(), DIRECTORY_SEPARATOR, $data['path']);
		$data['url'] = str_replace('/' . $this->get_private_folder_prefix(), '/', $data['url']);
		
		return $data;
		
	}
	
	function wp_get_attachment_url($url, $post_id) {
		$url = str_replace('/' . $this->get_private_folder_prefix(), '/', $url);
		return $url;
	}
	
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