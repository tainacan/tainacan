<?php

namespace Tainacan\Tools\Management_Tool;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Repositories;
use Tainacan\Tools\Output_Collector;

/**
 * Index document content for items in a collection or all collections (e.g. for search).
 *
 * @since 1.0.0
 */
class Tool_Index_Content implements \Tainacan\Tools\Management_Tool {

	private $collection_repository;
	private $items_repository;
	private $result_count;
	private $dry_run = false;

	/** @var Output_Collector|null */
	private $output;

	public function __construct() {
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->items_repository = Repositories\Items::get_instance();
		$this->result_count = [ 'indexed_documents' => 0 ];
	}

	public function get_id() {
		return 'index_content';
	}

	public function get_name() {
		return __( 'Index document content', 'tainacan' );
	}

	public function get_description() {
		return __( 'Index document content of items for search. Use for a specific collection or all collections.', 'tainacan' );
	}

	public function get_params() {
		return [
			[
				'name'        => 'collection',
				'type'        => 'string',
				'required'    => true,
				'label'       => __( 'Collection', 'tainacan' ),
				'description' => __( 'Collection ID or "all" for all collections', 'tainacan' ),
			],
			[
				'name'        => 'dry_run',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Dry run', 'tainacan' ),
				'description' => __( 'Only count items, do not index', 'tainacan' ),
			],
		];
	}

	public function is_destructive() {
		return false;
	}

	public function is_exposed_to_rest() {
		return true;
	}

	public function run( array $args, Output_Collector $output ) {
		$this->output = $output;
		$this->dry_run = ! empty( $args['dry_run'] );

		if ( empty( $args['collection'] ) ) {
			$this->output->error( __( 'Wrong parameters', 'tainacan' ), true );
			return;
		}

		$collection = $args['collection'];
		if ( $collection === 'all' ) {
			$this->index_item_all_collections();
		} else {
			$this->index_item( $collection );
		}

		$msg = "\n" . $this->result_count['indexed_documents'] . ' ' . __( 'items indexed', 'tainacan' );
		$this->output->success( $msg );
	}

	private function index_item_all_collections() {
		$collections = $this->collection_repository->fetch( [ 'posts_per_page' => -1 ], 'OBJECT' );
		foreach ( $collections as $collection ) {
			$this->result_count['indexed_documents'] = 0;
			$this->index_item( $collection->get_id() );
		}
	}

	private function index_item( $collection_id ) {
		$per_page = 50;
		$page = 1;
		$args = [
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => get_post_stati(),
		];
		$collection_items = $this->items_repository->fetch( $args, $collection_id, 'WP_Query' );
		$total = $collection_items->found_posts;
		$last_page = (int) ceil( $total / $per_page );

		/* translators: %s: collection ID */
		$label = sprintf( __( 'Indexing documents of items in collection %s', 'tainacan' ), $collection_id );
		$this->output->start_progress( $total, $label );

		while ( $page <= $last_page ) {
			if ( $collection_items->have_posts() ) {
				while ( $collection_items->have_posts() ) {
					$collection_items->the_post();
					$item = new \Tainacan\Entities\Item( $collection_items->post );
					$this->index_content_document_item( $item );
					$this->output->tick_progress( 1 );
				}
			}
			$page++;
			$args['paged'] = $page;
			$collection_items = $this->items_repository->fetch( $args, $collection_id, 'WP_Query' );
		}

		$this->output->finish_progress();
		$msg = $this->result_count['indexed_documents'] . ' ' . __( 'items indexed in collection', 'tainacan' );
		$this->output->success( $msg );
	}

	private function index_content_document_item( $item ) {
		if ( ! $item instanceof \Tainacan\Entities\Item ) {
			$this->output->error( __( 'An item with this ID was not found', 'tainacan' ), true );
			return null;
		}

		if ( empty( $item->get_document() ) ) {
			return null;
		}

		$this->result_count['indexed_documents']++;
		if ( $this->dry_run ) {
			return true;
		}
		return $this->items_repository->generate_index_content( $item );
	}
}
