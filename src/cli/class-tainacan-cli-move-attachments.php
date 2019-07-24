<?php 

namespace Tainacan;

use WP_CLI;
use Tainacan\Repositories;

class Cli_Move_Attachments {
	
	private $collections = [];
	private $documents = false;
	
	/**
	 * Moves the items documents and attachments to a $collection_id/$item_id directory structure 
	 *
	 * This is only to be used to update the structure of intallations made before version 0.11 of Tainacan, when 
	 * this structure was implemented.
	 *
	 * See (URL to docs) for more information
	 *
	 *  
	 */
	public function __invoke($args, $assoc_args) {
		
		global $wpdb;
		
		$dry_run = isset($assoc_args['dry-run']);
		$deep = isset($assoc_args['deep']);
		
		$items_repo = Repositories\Items::get_instance();
		$collections_repo = Repositories\Collections::get_instance();
		
		$attachments = new \WP_Query([
			'post_type' => 'attachment',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'id=>parent'
		]);
		
		$PF = \Tainacan\Private_Files::get_instance();

		foreach ($attachments->posts as $att) {
			
			if ( $item = $this->is_item_attachment($att) ) {
				
				$meta = wp_get_attachment_metadata($att->ID);
				
				// $meta['file'] = str_replace('2222/07', '2019/07', $meta['file']);
				// 
				// wp_update_attachment_metadata($att->ID, $meta);
				
				$current_url = get_post_meta($att->ID, '_wp_attached_file', true);
				$filename = basename($current_url);
				
				$collection = $item->get_collection();
				$col_id = $collection->get_id();
				$item_id = $item->get_id();
				
				$new_url = $PF->get_items_uploads_folder() . '/' . $col_id . '/' . $item_id . '/' . $filename;
				
				$current_path = get_attached_file($att->ID);
				
				$upload_base = wp_get_upload_dir();
				$upload_base = $upload_base['basedir'];
				
				$col_status = get_post_status_object($collection->get_status());
				$item_status = get_post_status_object($item->get_status());
				
				if ( ! $col_status->public ) {
					$col_id = $PF->get_private_folder_prefix() . $col_id;
				}
				if ( ! $item_status->public ) {
					$item_id = $PF->get_private_folder_prefix() . $item_id;
				}
				
				$new_path_base = $upload_base . DIRECTORY_SEPARATOR . $PF->get_items_uploads_folder() . DIRECTORY_SEPARATOR . $col_id . '/' . $item_id;
				
				$new_path = $new_path_base . DIRECTORY_SEPARATOR . $filename;
				
				var_dump($current_url, $new_url, $current_path, $new_path);
				
				die;
				
			}
			
		}
		
	}
	
	private function is_item_attachment($att) {
		$ThemeHelper = \Tainacan\Theme_Helper::get_instance();
		if ($att->parent > 0) {
			
			$post = get_post($att->parent);
			if ($post instanceof \WP_Post) {
				if ($ThemeHelper->is_post_an_item($post)) {
					return new \Tainacan\Entities\Item($post);
				}
			}
		} else {
			if  ( $this->is_document($att->ID) ) {
				global $wpdb;
				$post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'document' AND meta_value = %d LIMIT 1", $att->ID) );
				$post = get_post($post_id);
				if ($post instanceof \WP_Post) {
					if ($ThemeHelper->is_post_an_item($post)) {
						return new \Tainacan\Entities\Item($post);
					}
				}
			}
		}
		
		return false;
		
	}
	
	private function is_document($attachment_id) {
		
		// init
		if ( ! $this->documents ) {
			global $wpdb;
			$this->documents = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'document'");
		}
		
		return in_array($attachment_id, $this->documents);
		
	}
	
	private function get_collection($id) {
		
		$collections_repo = Repositories\Collections::get_instance();
		
		if (!isset($this->collections[$id])) {
			$this->collections[$id] = $collections_repo->fetch( (int) $id );
		}
		return $this->collections[$id];
	}
	
}


 ?>