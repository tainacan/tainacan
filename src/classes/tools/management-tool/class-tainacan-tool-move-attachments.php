<?php

namespace Tainacan\Tools\Management_Tool;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Tools\Output_Collector;

/**
 * Move item documents and attachments to the collection_id/item_id directory structure (Tainacan 0.11+).
 *
 * @since 1.0.0
 */
class Tool_Move_Attachments implements \Tainacan\Tools\Management_Tool {

	private $collections = [];

	/** @var int[]|null Null until first load. */
	private $documents = null;

	/** @var Output_Collector|null */
	private $output;

	public function get_id() {
		return 'move_attachments_to_items_folder';
	}

	public function get_name() {
		return __( 'Move attachments to items folder', 'tainacan' );
	}

	public function get_description() {
		return __( 'Migrate attachments to the collection/item directory structure. Use for installations created before Tainacan 0.11.', 'tainacan' );
	}

	public function get_params() {
		return [
			[
				'name'        => 'dry_run',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Dry run', 'tainacan' ),
				'description' => __( 'Report only, do not move files', 'tainacan' ),
			],
		];
	}

	public function is_destructive() {
		return false;
	}

	public function is_exposed_to_rest() {
		return false;
	}

	public function run( array $args, Output_Collector $output ) {
		$this->output = $output;
		$dry_run = ! empty( $args['dry_run'] );

		global $wpdb;

		$PF = \Tainacan\Private_Files::get_instance();
		$upload_base = wp_get_upload_dir();
		$upload_base = $upload_base['basedir'];
		$base_upload_path = $upload_base . DIRECTORY_SEPARATOR . $PF->get_items_uploads_folder();

		if ( ! file_exists( $base_upload_path ) ) {
			if ( ! wp_mkdir_p( $base_upload_path ) ) {
				$this->output->error( 'Unable to create uploads directory: ' . $base_upload_path, true );
				return;
			}
		}

		$attachments = new \WP_Query( [
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		] );

		$total = $attachments->found_posts;
		$this->output->start_progress( $total, __( 'Moving attachments', 'tainacan' ) );

		$moved_count = 0;
		foreach ( $attachments->posts as $att ) {
			$this->output->tick_progress( 1 );

			$item = $this->is_item_attachment( $att );
			if ( ! $item ) {
				continue;
			}

			$meta = wp_get_attachment_metadata( $att->ID );
			$current_url = get_post_meta( $att->ID, '_wp_attached_file', true );
			$filename = basename( $current_url );
			$collection = $item->get_collection();
			if ( ! $collection instanceof \Tainacan\Entities\Collection ) {
				continue;
			}
			$col_id = $collection->get_id();
			$item_id = $item->get_id();
			$new_url = $PF->get_items_uploads_folder() . '/' . $col_id . '/' . $item_id . '/' . $filename;

			if ( $current_url === $new_url ) {
				continue;
			}

			$current_path = get_attached_file( $att->ID );
			$current_base_path = dirname( $current_path );
			$col_status = get_post_status_object( $collection->get_status() );
			$item_status = get_post_status_object( $item->get_status() );
			if ( ! $col_status->public ) {
				$col_id = $PF->get_private_folder_prefix() . $col_id;
			}
			if ( ! $item_status->public ) {
				$item_id = $PF->get_private_folder_prefix() . $item_id;
			}
			$new_path_base = $base_upload_path . DIRECTORY_SEPARATOR . $col_id . '/' . $item_id;
			$new_path = $new_path_base . DIRECTORY_SEPARATOR . $filename;

			if ( ! $dry_run ) {
				if ( ! wp_mkdir_p( $new_path_base ) ) {
					$this->output->error( 'Unable to create destination directory: ' . $new_path_base, true );
					return;
				}
				if ( isset( $meta['file'] ) ) {
					$meta['file'] = str_replace( $current_url, $new_url, $meta['file'] );
				}
				if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
					foreach ( $meta['sizes'] as $size ) {
						$src = $current_base_path . DIRECTORY_SEPARATOR . $size['file'];
						$dst = $new_path_base . DIRECTORY_SEPARATOR . $size['file'];
						if ( file_exists( $src ) ) {
							rename( $src, $dst );
						}
					}
				}
				rename( $current_path, $new_path );
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d, guid = REPLACE(guid, %s, %s) WHERE ID = %d", $item->get_id(), $current_url, $new_url, $att->ID ) );
				wp_update_attachment_metadata( $att->ID, $meta );
				update_post_meta( $att->ID, '_wp_attached_file', $new_url );
			}

			$moved_count++;
		}

		$this->output->finish_progress();

		$message = $dry_run ?
			/* translators: %d: number of attachments */
			__( '%d attachments to be moved', 'tainacan' ) :
			/* translators: %d: number of attachments */
			__( '%d attachments moved', 'tainacan' );
		$this->output->success( sprintf( $message, $moved_count ) );
	}

	private function is_item_attachment( $att ) {
		$ThemeHelper = \Tainacan\Theme_Helper::get_instance();
		if ( $att->post_parent > 0 ) {
			$post = get_post( $att->post_parent );
			if ( $post instanceof \WP_Post && $ThemeHelper->is_post_an_item( $post ) ) {
				return new \Tainacan\Entities\Item( $post );
			}
		} else {
			if ( $this->is_document( $att->ID ) ) {
				global $wpdb;
				$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'document' AND meta_value = %d LIMIT 1", $att->ID ) );
				$post = get_post( $post_id );
				if ( $post instanceof \WP_Post && $ThemeHelper->is_post_an_item( $post ) ) {
					return new \Tainacan\Entities\Item( $post );
				}
			}
		}
		return false;
	}

	private function is_document( $attachment_id ) {
		if ( $this->documents === null ) {
			global $wpdb;
			$col = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'document'" );
			$this->documents = is_array( $col ) ? array_map( 'intval', $col ) : [];
		}
		return in_array( (int) $attachment_id, $this->documents, true );
	}
}
