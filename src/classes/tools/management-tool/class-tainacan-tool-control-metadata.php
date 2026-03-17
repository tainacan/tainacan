<?php

namespace Tainacan\Tools\Management_Tool;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Repositories;
use Tainacan\Tools\Output_Collector;

/**
 * Recalculate control metadata for items in a collection or all collections.
 *
 * @since 1.0.0
 */
class Tool_Control_Metadata implements \Tainacan\Tools\Management_Tool {

	private $collection_repository;
	private $items_repository;
	private $result_count;
	private $dry_run = false;

	/**
	 * @var Output_Collector|null
	 */
	private $output;

	public function __construct() {
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->items_repository = Repositories\Items::get_instance();
		$this->result_count = [
			'items' => 0,
			'items_collection' => 0,
		];
	}

	/** @return string */
	public function get_id() {
		return 'control_metadata';
	}

	/** @return string */
	public function get_name() {
		return __( 'Recalculate control metadata', 'tainacan' );
	}

	/** @return string */
	public function get_description() {
		return __( 'Recalculate control metadata values for items in a collection or all collections. Optionally recreate control metadata definitions.', 'tainacan' );
	}

	/** @return array<int, array{name: string, type: string, required: bool, label?: string, default?: mixed, description?: string}> */
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
				'description' => __( 'Only count items, do not update', 'tainacan' ),
			],
			[
				'name'        => 'recreate_control_metadata_definitions',
				'type'        => 'boolean',
				'required'    => false,
				'default'     => false,
				'label'       => __( 'Recreate definitions', 'tainacan' ),
				'description' => __( 'Recreate control metadata definitions for collections', 'tainacan' ),
			],
		];
	}

	/** @return bool */
	public function is_destructive() {
		return false;
	}

	/** @return bool */
	public function is_exposed_to_rest() {
		return true;
	}

	/**
	 * Run the tool. Args use canonical keys: collection, dry_run, recreate_control_metadata_definitions.
	 *
	 * @param array<string, mixed> $args
	 * @param Output_Collector $output
	 * @return void
	 */
	public function run( array $args, Output_Collector $output ) {
		$this->output = $output;
		$this->dry_run = ! empty( $args['dry_run'] );

		if ( empty( $args['collection'] ) ) {
			$this->output->error( __( 'Wrong parameters', 'tainacan' ), true );
			return;
		}

		if ( ! empty( $args['recreate_control_metadata_definitions'] ) ) {
			$this->recreate_control_metadata_collection_definitions();
		}

		$collection = $args['collection'];
		if ( $collection === 'all' ) {
			$this->recalculate_items_for_all_collections();
		} else {
			$this->recalculate_items( $collection );
		}
		$msg = "\n\n" . $this->result_count['items'] . " " . __( 'items recalculated.', 'tainacan' );
		$this->output->success( $msg );
	}

	private function recalculate_items_for_all_collections() {
		$collections = $this->collection_repository->fetch( [ 'posts_per_page' => -1 ], 'OBJECT' );
		foreach ( $collections as $collection ) {
			$this->result_count['items_collection'] = 0;
			$this->recalculate_items( $collection->get_id() );
		}
	}

	private function recalculate_items( $collection_id ) {
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

		$label = sprintf( __( 'Recalculate control metadata for items in collection %s', 'tainacan' ), $collection_id );
		$this->output->start_progress( $total, $label );

		while ( $page <= $last_page ) {
			if ( $collection_items->have_posts() ) {
				while ( $collection_items->have_posts() ) {
					$collection_items->the_post();
					$item = new \Tainacan\Entities\Item( $collection_items->post );
					$this->perform_item_recalculation( $item );
					$this->output->tick_progress( 1 );
				}
			}
			$page++;
			$args['paged'] = $page;
			$collection_items = $this->items_repository->fetch( $args, $collection_id, 'WP_Query' );
		}

		$this->output->finish_progress();
		$this->result_count['items'] += $this->result_count['items_collection'];
		$msg = $this->result_count['items_collection'] . " " . __( 'items recalculated in collection', 'tainacan' );
		$this->output->success( $msg );
	}

	private function recreate_control_metadata_collection_definitions() {
		$Tainacan_Metadata = Repositories\Metadata::get_instance();
		$collections = $this->collection_repository->fetch( [ 'posts_per_page' => -1 ], 'OBJECT' );
		foreach ( $collections as $collection ) {
			$Tainacan_Metadata->register_control_metadata( $collection, true );
		}
		$msg = __( 'recreate control metadata collection definitions completed successfully', 'tainacan' );
		$this->output->success( $msg );
	}

	private function perform_item_recalculation( $item ) {
		if ( ! $item instanceof \Tainacan\Entities\Item ) {
			$this->output->error( __( 'An item with this ID was not found', 'tainacan' ), true );
			return;
		}

		$this->result_count['items_collection']++;

		if ( $this->dry_run ) {
			return;
		}

		$helper = \Tainacan\Metadata_Types\Control::get_helper();
		$helper->update_control_metadatum( $item );
	}
}
