<?php

namespace Tainacan\Tools\Tool;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Repositories;
use Tainacan\Tools\Output;

/**
 * Remove all items from a collection (trash or permanently delete).
 *
 * @since 1.0.0
 */
class Collection_Clean implements \Tainacan\Tools\Tool {

	private $items_repository;
	private $result_count;
	private $dry_run = false;

	/** @var Output|null */
	private $output;

	public function __construct() {
		$this->items_repository = Repositories\Items::get_instance();
		$this->result_count = [ 'items' => 0, 'attachments' => 0 ];
	}

	public function get_id() {
		return 'collection_clean';
	}

	public function get_name() {
		return __( 'Clean collection items', 'tainacan' );
	}

	public function get_description() {
		return __( 'Remove all items from a collection. Items can be moved to trash or permanently deleted with their attachments.', 'tainacan' );
	}

	public function get_params() {
		return [
			[
				'name'        => 'collection_id',
				'type'        => 'string',
				'required'    => true,
				'label'       => __( 'Collection ID', 'tainacan' ),
				'description' => __( 'Collection ID to clean', 'tainacan' ),
			],
			[
				'name'        => 'permanently',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Permanently delete', 'tainacan' ),
				'description' => __( 'Permanently delete items and attachments (skip trash)', 'tainacan' ),
			],
			[
				'name'        => 'dry_run',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Dry run', 'tainacan' ),
				'description' => __( 'Only count items, do not remove', 'tainacan' ),
			],
		];
	}

	public function is_destructive() {
		return true;
	}

	public function is_exposed_to_rest() {
		return true;
	}

	public function run( array $args, Output $output ) {
		$this->output = $output;
		$this->dry_run = ! empty( $args['dry_run'] );
		$permanently = ! empty( $args['permanently'] );

		if ( empty( $args['collection_id'] ) || ! is_numeric( $args['collection_id'] ) ) {
			$this->output->error( __( 'Wrong parameters', 'tainacan' ), true );
			return;
		}

		$collection_id = (int) $args['collection_id'];
		$per_page = 50;
		$page = 1;
		$query_args = [
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => get_post_stati(),
		];
		$collection_items = $this->items_repository->fetch( $query_args, $collection_id, 'WP_Query' );
		$total = $collection_items->found_posts;
		$last_page = (int) ceil( $total / $per_page );

		$label = __( 'Cleaning collection items', 'tainacan' );
		$this->output->start_progress( $total, $label );

		while ( $page <= $last_page ) {
			if ( $collection_items->have_posts() ) {
				while ( $collection_items->have_posts() ) {
					$collection_items->the_post();
					$item = new \Tainacan\Entities\Item( $collection_items->post );
					$this->delete_item( $item, $permanently );
					$this->output->tick_progress( 1 );
				}
			}
			$page++;
			$query_args['paged'] = $page;
			$collection_items = $this->items_repository->fetch( $query_args, $collection_id, 'WP_Query' );
		}

		$this->output->finish_progress();

		if ( $permanently ) {
			$msg = "\n" . $this->result_count['items'] . ' ' . __( 'items removed', 'tainacan' ) .
				"\n" . $this->result_count['attachments'] . ' ' . __( 'attachments removed', 'tainacan' );
		} else {
			$msg = "\n" . $this->result_count['items'] . ' ' . __( 'items moved to trash', 'tainacan' );
		}
		$this->output->success( $msg );
	}

	private function delete_item( $item, $permanently ) {
		if ( ! $item instanceof \Tainacan\Entities\Item ) {
			$this->output->error( __( 'An item with this ID was not found', 'tainacan' ), true );
			return null;
		}

		$this->result_count['items']++;
		if ( $permanently ) {
			$this->delete_attachments( $item );
			if ( ! $this->dry_run ) {
				$this->items_repository->delete( $item );
			}
		} else {
			if ( ! $this->dry_run ) {
				$this->items_repository->trash( $item );
			}
		}
		return $item;
	}

	private function delete_attachments( $item ) {
		$attachment_list = array_values( get_children( [
			'post_parent' => $item->get_id(),
			'post_type'   => 'attachment',
			'order'       => 'ASC',
			'numberposts' => -1,
		] ) );
		foreach ( $attachment_list as $attachment ) {
			$this->result_count['attachments']++;
			if ( ! $this->dry_run ) {
				wp_delete_attachment( $attachment->ID, true );
			}
		}
	}
}
