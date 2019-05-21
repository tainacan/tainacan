<?php 

namespace Tainacan;

use WP_CLI;

class Cli_Garbage_Collector {
	
	
	/**
	 * Clean your Tainacan installation removing unused files and database entries
	 *
	 * ## OPTIONS 
	 *
	 * [--dry-run]
	 * : Look for garbage but do not delete anything, just output a report 
	 *
	 * [--deep]
	 * : More aressive approach finding garbage. In some cases it could delete something related to other parts of the website. Currently, deep mode deletes all attachments with broken parent IDs, regardless whether they were uploaded via tainacan or not
	 *
	 * [--skip-attachments]
	 * : Do not try to find orphan and unused attachments 
	 *
	 * [--skip-items]
	 * : Do not try to find orphan and unused items
	 *
	 * [--skip-taxonomies]
	 * : Do not try to find orphan and unused taxonomies
	 *
	 * [--skip-metadata]
	 * : Do not try to find orphan and unused metadata
	 *  
	 */
	public function __invoke($args, $assoc_args) {
		
		$dry_run = isset($assoc_args['dry-run']);
		$deep = isset($assoc_args['deep']);
		
		// delete attachments
		if (!isset($assoc_args['skip-attachments'])) {
			$this->delete_attachments($dry_run, $deep);
		}
		
		// delete items
		if (!isset($assoc_args['skip-items'])) {
			$this->delete_items($dry_run, $deep);
		}

		// delete terms 
		if (!isset($assoc_args['skip-taxonomies'])) {
			$this->delete_terms_taxonomies($dry_run, $deep);
		}
		
		// delete trashed metadata 
		// delete trashed metadata values 
		if (!isset($assoc_args['skip-metadata'])) {
			$this->delete_metadata($dry_run, $deep);
		}
		
		// delete bulk post meta 

		
	}

	
	private function get_orphan_items_query($select = 'ID') {
		global $wpdb;
		
		$collections = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-collection'");
		
		$collections_post_types = array_map(function($el) { return 'tnc_col_' . $el . '_item'; }, $collections);
		
		$existing_post_types = $wpdb->get_col("SELECT DISTINCT(post_type) FROM $wpdb->posts WHERE post_type LIKE 'tnc_col_%'");
		
		$post_types = array_diff($existing_post_types, $collections_post_types);
		
		if (empty($post_types)) {
			$post_types = ['return-nothing'];
		}
		
		$in_str_arr = array_fill( 0, count( $post_types ), '%s' );
		$in_str = join( ',', $in_str_arr );
		
		
		return $wpdb->prepare("SELECT $select FROM $wpdb->posts WHERE post_type IN ($in_str)", $post_types);
		
	}
	
	private function delete_items($dry_run = false, $deep = false) {
		global $wpdb;
		
		$items_found = $wpdb->get_var( $this->get_orphan_items_query('COUNT(ID)') );
		$items_deleted = 0;
		
		WP_CLI::line( "Found $items_found items" );
		
		if (!$dry_run) {
			
			$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting items', $items_found );
			
			$items_ids = $wpdb->get_col( $this->get_orphan_items_query() );
			
			foreach ($items_ids as $item_id) {
				$deleted = wp_delete_post($item_id, true);
				if ($deleted !== false && !is_null($deleted)) {
					$items_deleted ++;
				}
				$progress->tick();
			}
			
			$progress->finish();
			WP_CLI::success( "$items_deleted deleted" );
			
		}
		
	}
	
	private function get_orphan_attachments_count() {
		global $wpdb;
		return $wpdb->get_var( $this->get_orphan_attachments_query('COUNT(ID)') );
		
	}
	
	private function delete_attachments($dry_run = false, $deep = false) {
		global $wpdb;
		
		$orphan_items_query = $this->get_orphan_items_query();
		
		$orphan_documents = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' 
			AND ID IN (
				SELECT meta_value FROM $wpdb->postmeta WHERE post_id IN ($orphan_items_query) 
					AND meta_key = 'document'
		
			)");
		
		$orphan_att = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' 
			AND post_parent IN ( $orphan_items_query )");
		
		$orphan_att_deep = [];
		
		if ($deep) {
			$orphan_att_deep = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' 
				AND post_parent > 0 AND post_parent NOT IN ( SELECT ID FROM $wpdb->posts )");
		}
		
		$attachments = array_merge($orphan_documents, $orphan_att, $orphan_att_deep);
		//$attachments = array_unique($attachments);
		
		$number_of_files = 0;
		$total_KB = 0;
		$verbose_output = [];
		$uploadpath = wp_get_upload_dir();
		
		
		foreach ($attachments as $att) {
			
			$number_of_files ++;
			
			$file = get_attached_file($att);
			$meta = wp_get_attachment_metadata($att);
			$size = file_exists($file) ? filesize($file): 0;
			
			$total_KB += $size;
			$verbose_output[] = [
				'file' => $file,
				'size' => $this->filesize_formatted($size)
			];
			
			if (isset($meta['sizes'])) {
				foreach ( $meta['sizes'] as $size => $sizeinfo ) {
					$intermediate_file = str_replace( basename( $file ), $sizeinfo['file'], $file );
					if ( ! empty( $intermediate_file ) ) {
						$intermediate_file = path_join( $uploadpath['basedir'], $intermediate_file );

						$size = file_exists($intermediate_file) ? filesize($intermediate_file): 0;
						$total_KB += $size;
						$verbose_output[] = [
							'file' => $intermediate_file,
							'size' => $this->filesize_formatted($size)
						];
						
					}
				}
			}
			
		}
		// verbose
		//$respose = WP_CLI\Utils\format_items( 'table', $verbose_output, array( 'file', 'size' ) );
		$total_bytes = $this->filesize_formatted($total_KB);
		WP_CLI::line( "Found $number_of_files attachments. Total of $total_bytes bytes" );
		
		if (!$dry_run) {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting files', count($attachments) );
			
			foreach ($attachments as $att) {
				wp_delete_attachment($att, true);
				$progress->tick();
			}
			$progress->finish();
		}
		
		
	}
	

	private function filesize_formatted($size) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$power = $size > 0 ? floor(log($size, 1024)) : 0;
		return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
	}
	
	private function delete_terms_taxonomies($dry_run = false, $deep = false) {
		global $wpdb;
		
		$existing_taxonomies = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-taxonomy'" );
		$existing_taxonomies = array_map(function($el) { return 'tnc_tax_' . $el; }, $existing_taxonomies);
		
		$current_taxonomies = $wpdb->get_col( "SELECT DISTINCT(taxonomy) FROM $wpdb->term_taxonomy WHERE taxonomy LIKE 'tnc_tax_%'" );
		
		$orphan_taxonomies = array_diff($current_taxonomies, $existing_taxonomies);
		$orphan_taxonomies_count = count($orphan_taxonomies);
		WP_CLI::line( "Found $orphan_taxonomies_count orphan taxonomies" );
		
		if ($orphan_taxonomies_count < 1) {
			return;
		}
		
		$in_str_arr = array_fill( 0, $orphan_taxonomies_count, '%s' );
		$in_str = join( ',', $in_str_arr );
		
		$orphan_terms = $wpdb->get_results( $wpdb->prepare("SELECT term_id, term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy IN ($in_str)", $orphan_taxonomies) );
		$orphan_terms_count = count($orphan_terms);
		
		WP_CLI::line( "Found $orphan_terms_count orphan terms" );
		
		if (!$dry_run) {
			
			$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting taxonomies and terms', 4 );
			
			$term_ids = array_map( function($el) { return $el->term_id; }, $orphan_terms );
			$term_taxonomy_ids = array_map( function($el) { return $el->term_taxonomy_id; }, $orphan_terms );
			
			$in_str_arr = array_fill( 0, count($term_ids), '%s' );
			$in_str = join( ',', $in_str_arr );
			
			$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->termmeta WHERE term_id IN ($in_str)", $term_ids) );
			$progress->tick();
			$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id IN ($in_str)", $term_taxonomy_ids) );
			$progress->tick();
			$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->term_taxonomy WHERE term_taxonomy_id IN ($in_str)", $term_taxonomy_ids) );
			$progress->tick();
			$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->terms WHERE term_id IN ($in_str)", $term_ids) );
			$progress->tick();
			$progress->finish();
			
			WP_CLI::success( "Terms deleted!" );
			
		}
		
		
	}

	private function delete_metadata($dry_run = false, $deep = false) {
		global $wpdb;
		
		$deleted_metadata = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-metadatum' AND post_status = 'trash'" );
		
		$orphan_metadata = $wpdb->get_col( "SELECT p.ID FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON p.ID = pm.post_id 
			WHERE p.post_type = 'tainacan-metadatum' AND 
			pm.meta_key = 'collection_id' AND 
			pm.meta_value NOT IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-collection')
			" );
		
		var_dump(count($orphan_metadata));
		
		$meta_to_delete = array_merge($deleted_metadata, $orphan_metadata);
		
		$meta_to_delete_count = count($meta_to_delete);
		
		$orphan_values = $wpdb->get_col( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)" );
		$orphan_values_count = count($orphan_values);
		
		
		
		if ($meta_to_delete_count < 1 && $orphan_values_count < 1) {
			WP_CLI::line( "No deleted or orphan Metadata found" );
			return;
		}
		
		$metas = [];
		
		if ($meta_to_delete_count > 0) {
			$in_str_arr = array_fill( 0, $meta_to_delete_count, '%d' );
			$in_str = join( ',', $in_str_arr );
			$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE meta_key IN ($in_str)", $meta_to_delete) );
		}
		
		$metas_count = count($metas);
		
		
		WP_CLI::line( "Found $meta_to_delete_count deleted or orphan Metadata with $metas_count values associated" );
		WP_CLI::line( "Found $orphan_values_count orphan metadata values" );
		
		
		
		if (!$dry_run) {
			
			$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting metadata', $meta_to_delete_count + 2 );
			$progress->tick();
			
			$metas = array_merge($metas, $orphan_values);
			$metas_count = count($metas);
			
			if ($metas_count > 0) {
				$in_str_arr = array_fill( 0, $metas_count, '%d' );
				$in_str = join( ',', $in_str_arr );
				$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_id IN ($in_str)", $metas) );
			}
			
			$progress->tick();
			
			// $in_str_arr = array_fill( 0, $meta_to_delete_count, '%d' );
			// $in_str = join( ',', $in_str_arr );
			// $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts WHERE ID IN ($in_str)", $meta_to_delete) );
			
			foreach ($meta_to_delete as $meta_id) {
				$deleted = wp_delete_post($meta_id, true);
				$progress->tick();
			}
			
			$progress->finish();
			
			WP_CLI::success( "Metadata deleted!" );
		}
		
	}
	
	
}


 ?>