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
	 * ## OPTIONS 
	 *
	 * [--dry-run]
	 * : Look for attachments but don't move them, just output a report 
	 *  
	 */
	public function __invoke($args, $assoc_args) {
		
		$dry_run = isset($assoc_args['dry-run']);
		
		global $wpdb;
		
		$dry_run = isset($assoc_args['dry-run']);
		
		$items_repo = Repositories\Items::get_instance();
		$collections_repo = Repositories\Collections::get_instance();
		
		$attachments = new \WP_Query([
			'post_type' => 'attachment',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'id=>parent'
		]);
		
		$PF = \Tainacan\Private_Files::get_instance();
		
		$upload_base = wp_get_upload_dir();
		$upload_base = $upload_base['basedir'];
		
		$base_upload_path = $upload_base . DIRECTORY_SEPARATOR . $PF->get_items_uploads_folder();
		
		if (!file_exists($base_upload_path)) {
			if ( !wp_mkdir_p($base_upload_path) ) {
				\WP_CLI::error( "Unable to create uploads directory: " . $base_upload_path );
			}
		}
		
		$progress = \WP_CLI\Utils\make_progress_bar( 'Moving attachments', $attachments->found_posts );
		$results = [];
		
		foreach ($attachments->posts as $att) {
			
			$progress->tick();
			
			if ( $item = $this->is_item_attachment($att) ) {
				
				$meta = wp_get_attachment_metadata($att->ID);
				
				$current_url = get_post_meta($att->ID, '_wp_attached_file', true);
				$filename = basename($current_url);
				$collection = $item->get_collection();
				if ( ! $collection instanceof \Tainacan\Entities\Collection ) {
					continue;
				}
				$col_id = $collection->get_id();
				$item_id = $item->get_id();
				
				$new_url = $PF->get_items_uploads_folder() . '/' . $col_id . '/' . $item_id . '/' . $filename;
				
				if ($current_url == $new_url) {
					continue;
				}
				$current_path = get_attached_file($att->ID);
				$current_base_path = dirname($current_path);
				
				$col_status = get_post_status_object($collection->get_status());
				$item_status = get_post_status_object($item->get_status());
				
				if ( ! $col_status->public ) {
					$col_id = $PF->get_private_folder_prefix() . $col_id;
				}
				if ( ! $item_status->public ) {
					$item_id = $PF->get_private_folder_prefix() . $item_id;
				}
				$new_path_base = $base_upload_path . DIRECTORY_SEPARATOR . $col_id . '/' . $item_id;
				
				$new_path = $new_path_base . DIRECTORY_SEPARATOR . $filename;
				
				if (!$dry_run) {
					
					if ( ! wp_mkdir_p( $new_path_base ) ) {
						\WP_CLI::error( "Unable to create destination directory: " . $new_path_base );
					}
					
					if ( isset($meta['file']) ) {
						$meta['file'] = \str_replace($current_url, $new_url, $meta['file']);
					}
					
					if ( isset($meta['sizes']) && is_array($meta['sizes']) ) {
						foreach ($meta['sizes'] as $size) {
							rename($current_base_path . DIRECTORY_SEPARATOR . $size['file'], $new_path_base . DIRECTORY_SEPARATOR . $size['file']);
						}
					}
					
					rename($current_path, $new_path);
					$wpdb->query("UPDATE $wpdb->posts SET post_parent = {$item->get_id()}, guid = REPLACE(guid, '$current_url', '$new_url') WHERE ID = {$att->ID}");
					wp_update_attachment_metadata($att->ID, $meta);
					update_post_meta($att->ID, '_wp_attached_file', $new_url);
					
				}
				
				$results[] = [
					'Attachment' => $current_url, 
					'Moved to' => $new_url
				];
				
			}
			
		}
		
		$progress->finish();
		
		\WP_CLI\Utils\format_items( 'table', $results, ['Attachment', 'Moved to'] );
		
		$message = $dry_run ? ' to be moved' : ' moved';
		
		\WP_CLI::success( count($results) . $message );
		
	}
	
	private function is_item_attachment($att) {
		$ThemeHelper = \Tainacan\Theme_Helper::get_instance();
		if ($att->post_parent > 0) {
			
			$post = get_post($att->post_parent);
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