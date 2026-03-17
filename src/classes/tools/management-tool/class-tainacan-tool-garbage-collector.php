<?php

namespace Tainacan\Tools\Management_Tool;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Tools\Output_Collector;

/**
 * Clean Tainacan installation: remove unused files, orphan items, terms, metadata, and transients.
 *
 * @since 1.0.0
 */
class Tool_Garbage_Collector implements \Tainacan\Tools\Management_Tool {

	/** @var Output_Collector|null */
	private $output;

	public function get_id() {
		return 'garbage_collector';
	}

	public function get_name() {
		return __( 'Garbage collector', 'tainacan' );
	}

	public function get_description() {
		return __( 'Remove unused files, orphan items, taxonomies, metadata and transients. Use --run to delete; without it only reports.', 'tainacan' );
	}

	public function get_params() {
		return [
			[
				'name'        => 'run',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Run (delete)', 'tainacan' ),
				'description' => __( 'Actually delete garbage (default is report only)', 'tainacan' ),
			],
			[
				'name'        => 'deep',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Deep cleanup', 'tainacan' ),
				'description' => __( 'More aggressive; may affect non-Tainacan attachments with broken parent IDs', 'tainacan' ),
			],
			[
				'name'        => 'skip_attachments',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Skip attachments', 'tainacan' ),
				'description' => __( 'Skip orphan/unused attachments', 'tainacan' ),
			],
			[
				'name'        => 'skip_items',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Skip items', 'tainacan' ),
				'description' => __( 'Skip orphan items', 'tainacan' ),
			],
			[
				'name'        => 'skip_taxonomies',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Skip taxonomies', 'tainacan' ),
				'description' => __( 'Skip orphan taxonomies/terms', 'tainacan' ),
			],
			[
				'name'        => 'skip_metadata',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Skip metadata', 'tainacan' ),
				'description' => __( 'Skip orphan/trashed metadata', 'tainacan' ),
			],
			[
				'name'        => 'skip_transients',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Skip transients', 'tainacan' ),
				'description' => __( 'Skip Tainacan transients', 'tainacan' ),
			],
		];
	}

	public function is_destructive() {
		return true;
	}

	public function is_exposed_to_rest() {
		return true;
	}

	public function run( array $args, Output_Collector $output ) {
		$this->output = $output;
		$dry_run = empty( $args['run'] );
		$deep = ! empty( $args['deep'] );

		if ( ! $dry_run ) {
			$this->output->warning( __( 'It is strongly recommended you do a backup before running this command, as there is no way to undo it.', 'tainacan' ) );
		}

		if ( empty( $args['skip_attachments'] ) ) {
			$this->delete_attachments( $dry_run, $deep );
		}
		if ( empty( $args['skip_items'] ) ) {
			$this->delete_items( $dry_run, $deep );
		}
		if ( empty( $args['skip_taxonomies'] ) ) {
			$this->delete_terms_taxonomies( $dry_run, $deep );
		}
		if ( empty( $args['skip_metadata'] ) ) {
			$this->delete_metadata( $dry_run, $deep );
		}
		if ( empty( $args['skip_transients'] ) ) {
			$this->delete_transients( $dry_run );
		}

		if ( $dry_run ) {
			$this->output->warning( __( 'Nothing was done. If you want to delete all the found garbage, run with run=true.', 'tainacan' ) );
		}
	}

	private function get_orphan_items_query( $select = 'ID' ) {
		global $wpdb;
		$collections = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-collection'" );
		$collections_post_types = array_map( function( $el ) { return 'tnc_col_' . $el . '_item'; }, $collections );
		$existing_post_types = $wpdb->get_col( "SELECT DISTINCT(post_type) FROM $wpdb->posts WHERE post_type LIKE 'tnc_col_%'" );
		$post_types = array_diff( $existing_post_types, $collections_post_types );
		if ( empty( $post_types ) ) {
			$post_types = [ 'return-nothing' ];
		}
		$in_str = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		return $wpdb->prepare( "SELECT $select FROM $wpdb->posts WHERE post_type IN ($in_str)", $post_types );
	}

	private function delete_items( $dry_run, $deep ) {
		global $wpdb;
		$items_found = (int) $wpdb->get_var( $this->get_orphan_items_query( 'COUNT(ID)' ) );
		$this->output->log( sprintf( __( 'Found %d items', 'tainacan' ), $items_found ), 'info' );

		if ( $dry_run ) {
			return;
		}

		$items_ids = $wpdb->get_col( $this->get_orphan_items_query() );
		$total = count( $items_ids );
		$this->output->start_progress( $total, __( 'Deleting items', 'tainacan' ) );
		$items_deleted = 0;
		foreach ( $items_ids as $item_id ) {
			$deleted = wp_delete_post( $item_id, true );
			if ( $deleted !== false && $deleted !== null ) {
				$items_deleted++;
			}
			$this->output->tick_progress( 1 );
		}
		$this->output->finish_progress();
		$this->output->success( (string) $items_deleted . ' ' . __( 'deleted', 'tainacan' ) );
	}

	private function delete_attachments( $dry_run, $deep ) {
		global $wpdb;
		$orphan_items_query = $this->get_orphan_items_query();

		$orphan_documents = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND ID IN (SELECT meta_value FROM $wpdb->postmeta WHERE post_id IN ($orphan_items_query) AND meta_key = 'document')" );
		$orphan_att = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent IN ($orphan_items_query)" );
		$orphan_att_deep = [];
		if ( $deep ) {
			$orphan_att_deep = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent > 0 AND post_parent NOT IN (SELECT ID FROM $wpdb->posts)" );
		}
		$attachments = array_merge( $orphan_documents ? $orphan_documents : [], $orphan_att ? $orphan_att : [], $orphan_att_deep );

		$number_of_files = 0;
		$total_bytes = 0;
		$uploadpath = wp_get_upload_dir();
		foreach ( $attachments as $att ) {
			$number_of_files++;
			$file = get_attached_file( $att );
			$meta = wp_get_attachment_metadata( $att );
			$size = ( is_string( $file ) && file_exists( $file ) ) ? filesize( $file ) : 0;
			$total_bytes += $size;
			if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $sizeinfo ) {
					$intermediate_file = str_replace( basename( $file ), $sizeinfo['file'], $file );
					if ( ! empty( $intermediate_file ) ) {
						$intermediate_file = path_join( $uploadpath['basedir'], $intermediate_file );
						$total_bytes += ( file_exists( $intermediate_file ) ) ? filesize( $intermediate_file ) : 0;
					}
				}
			}
		}

		$this->output->log( sprintf( __( 'Found %1$d attachments. Total of %2$s', 'tainacan' ), $number_of_files, $this->filesize_formatted( $total_bytes ) ), 'info' );

		if ( ! $dry_run && count( $attachments ) > 0 ) {
			$this->output->start_progress( count( $attachments ), __( 'Deleting files', 'tainacan' ) );
			foreach ( $attachments as $att ) {
				wp_delete_attachment( $att, true );
				$this->output->tick_progress( 1 );
			}
			$this->output->finish_progress();
		}
	}

	private function filesize_formatted( $size ) {
		$units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
		$power = $size > 0 ? floor( log( $size, 1024 ) ) : 0;
		return number_format( $size / pow( 1024, $power ), 2, '.', ',' ) . ' ' . $units[ $power ];
	}

	private function delete_terms_taxonomies( $dry_run, $deep ) {
		global $wpdb;
		$existing_taxonomies = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-taxonomy'" );
		$existing_taxonomies = array_map( function( $el ) { return 'tnc_tax_' . $el; }, $existing_taxonomies );
		$current_taxonomies = $wpdb->get_col( "SELECT DISTINCT(taxonomy) FROM $wpdb->term_taxonomy WHERE taxonomy LIKE 'tnc_tax_%'" );
		$orphan_taxonomies = array_diff( $current_taxonomies ? $current_taxonomies : [], $existing_taxonomies );
		$orphan_taxonomies_count = count( $orphan_taxonomies );
		$this->output->log( sprintf( __( 'Found %d orphan taxonomies', 'tainacan' ), $orphan_taxonomies_count ), 'info' );

		if ( $orphan_taxonomies_count < 1 ) {
			return;
		}

		$in_str = implode( ',', array_fill( 0, $orphan_taxonomies_count, '%s' ) );
		$orphan_terms = $wpdb->get_results( $wpdb->prepare( "SELECT term_id, term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy IN ($in_str)", $orphan_taxonomies ) );
		$orphan_terms_count = count( $orphan_terms );
		$this->output->log( sprintf( __( 'Found %d orphan terms', 'tainacan' ), $orphan_terms_count ), 'info' );

		if ( ! $dry_run && $orphan_terms_count > 0 ) {
			$term_ids = array_map( function( $el ) { return $el->term_id; }, $orphan_terms );
			$term_taxonomy_ids = array_map( function( $el ) { return $el->term_taxonomy_id; }, $orphan_terms );
			$in_str = implode( ',', array_fill( 0, count( $term_ids ), '%s' ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE term_id IN ($in_str)", $term_ids ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id IN ($in_str)", $term_taxonomy_ids ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_taxonomy WHERE term_taxonomy_id IN ($in_str)", $term_taxonomy_ids ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->terms WHERE term_id IN ($in_str)", $term_ids ) );
			$this->output->success( __( 'Terms deleted!', 'tainacan' ) );
		}
	}

	private function delete_metadata( $dry_run, $deep ) {
		global $wpdb;
		$deleted_metadata = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-metadatum' AND post_status = 'trash'" );
		$orphan_metadata = $wpdb->get_col( "SELECT p.ID FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE p.post_type = 'tainacan-metadatum' AND pm.meta_key = 'collection_id' AND pm.meta_value NOT IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-collection') AND pm.meta_value <> 'default'" );
		$meta_to_delete = array_merge( $deleted_metadata ? $deleted_metadata : [], $orphan_metadata ? $orphan_metadata : [] );
		$meta_to_delete_count = count( $meta_to_delete );
		$orphan_values = $wpdb->get_col( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)" );
		$orphan_values_count = count( $orphan_values ? $orphan_values : [] );

		if ( $meta_to_delete_count < 1 && $orphan_values_count < 1 ) {
			$this->output->log( __( 'No deleted or orphan Metadata found', 'tainacan' ), 'info' );
			return;
		}

		$metas = [];
		if ( $meta_to_delete_count > 0 ) {
			$in_str = implode( ',', array_fill( 0, $meta_to_delete_count, '%d' ) );
			$metas = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id IN ($in_str)", $meta_to_delete ) );
		}
		$metas_count = count( $metas ? $metas : [] );
		$this->output->log( sprintf( __( 'Found %1$d deleted or orphan Metadata with %2$d values associated', 'tainacan' ), $meta_to_delete_count, $metas_count ), 'info' );
		$this->output->log( sprintf( __( 'Found %d orphan metadata values', 'tainacan' ), $orphan_values_count ), 'info' );

		if ( ! $dry_run ) {
			$metas = array_merge( $metas ? $metas : [], $orphan_values ? $orphan_values : [] );
			$metas_count = count( $metas );
			if ( $metas_count > 0 ) {
				$in_str = implode( ',', array_fill( 0, $metas_count, '%d' ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_id IN ($in_str)", $metas ) );
			}
			foreach ( $meta_to_delete as $meta_id ) {
				wp_delete_post( $meta_id, true );
			}
			$this->output->success( __( 'Metadata deleted!', 'tainacan' ) );
		}
	}

	private function delete_transients( $dry_run ) {
		global $wpdb;
		$count = (int) $wpdb->get_var( "SELECT COUNT(option_id) FROM $wpdb->options WHERE option_name LIKE 'tnc_transient%'" );
		$this->output->log( sprintf( __( 'Found %d tainacan transients records in the Options table', 'tainacan' ), $count ), 'info' );

		if ( ! $dry_run ) {
			$this->output->log( __( 'Deleting transients...', 'tainacan' ), 'info' );
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'tnc_transient%'" );
			$this->output->success( __( 'Transients deleted!', 'tainacan' ) );
		}
	}
}
