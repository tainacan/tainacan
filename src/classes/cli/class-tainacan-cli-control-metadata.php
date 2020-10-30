<?php 

namespace Tainacan;

use WP_CLI;
use Tainacan\Repositories;

class Cli_Control_Metadata {

	private $collection_repository;
	private $items_repository;
	private $result_count;
	private $dry_run = false;
	
	public function __construct() {
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->items_repository = Repositories\Items::get_instance();
		$this->result_count = [
			'items' => 0,
			'items_collection' => 0,
		];
	} 

	/**
	 * recalculete values of metadata control 
	 *
	 * ## OPTIONS
	 * [--collection=<value>]
	 * : <value> Collection specific ID into which the control metadata will be recalculated, or 'all' for all collections.
	 * 
	 * [--dry-run]
	 * : only count the total of item which will recalculated, just output a report
	 * 
	 * [--recreate-control-metadata-definitions]
	 * : recreate the control metadata collection definitions
	 * 
	 * ## EXAMPLES
	 * 
	 * wp tainacan control-metadata --collection=416
	 * recalculate control metadata for items to collection 416:  100% [====================================================] 0:00 / 0:00
	 * Success: 
	 * 710 items recalculate
	 * 
	 * 
	 * wp tainacan control-metadata --collection=all
	 * recalculate control metadata for items to collection 416:  100% [====================================================] 0:00 / 0:00
	 * Success: 
	 * 710 items recalculate
	 * recalculate control metadata for items to collection 301:  100% [====================================================] 0:00 / 0:00
	 * Success: 
	 * 156 items recalculate
	 * 
	 */
	public function __invoke($args, $assoc_args) {
		$this->dry_run = false;
		if ( !empty($assoc_args['dry-run']) ) {
			$this->dry_run = true;
		}

		if( empty($assoc_args['collection']) ) {
			\WP_CLI::error( 'Wrong parameters', true );
		}

		if ( !empty($assoc_args['recreate-control-metadata-definitions']) ) {
			$this->recreate_control_metadata_collection_definitions();
		}

		$collection = $assoc_args['collection'];
		if ($collection == 'all') {
			$this->recalculate_items_for_all_collections();
		} else {
			$this->recalculate_items($collection);
		}
		$msg = "\n\n" . $this->result_count['items'] . " items recalculated.";
		\WP_CLI::success( $msg );
	}

	private function recalculate_items_for_all_collections() {
		$collections = $this->collection_repository->fetch(['posts_per_page' => -1], 'OBJECT');
		foreach ($collections as $collection) {
			$this->result_count['items_collection'] = 0;
			$this->recalculate_items($collection->get_id());
		}
	}

	private function recalculate_items($collection_id) {
		$per_page = 50; $page = 1;
		$args = [
			'posts_per_page'=> $per_page,
			'paged' => $page,
			'post_status' => get_post_stati()
		];
		$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		$total = $collection_items->found_posts;
		$last_page = ceil($total/$per_page);

		$progress = \WP_CLI\Utils\make_progress_bar( "recalculate control metadata for items to collection $collection_id:", $total );
		while ($page++ <= $last_page) {
			if ($collection_items->have_posts()) {
				while ( $collection_items->have_posts() ) {
					$collection_items->the_post();
					$item = new Entities\Item($collection_items->post);
					$this->perform_item_recalculation($item);
					$progress->tick();
				}
			}
			$args['paged'] = $page;
			$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		}
		$progress->finish();

		$this->result_count['items'] += $this->result_count['items_collection'];
		$msg = $this->result_count['items_collection'] . " items recalculated in collection";

		\WP_CLI::success( $msg );
	}

	private function recreate_control_metadata_collection_definitions() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$collections = $this->collection_repository->fetch(['posts_per_page' => -1], 'OBJECT');
		foreach ($collections as $collection) {
			$Tainacan_Metadata->register_control_metadata( $collection, true );
		}
		$msg = "recreate control metadata collection definitions completed successfully";
		\WP_CLI::success( $msg );
	}

	private function perform_item_recalculation($item) {
		if (! $item instanceof Entities\Item) {
			\WP_CLI::error( 'An item with this ID was not found', true );
		}

		$this->result_count['items_collection']++;

		if ($this->dry_run)
			return true;

		$helper = \Tainacan\Metadata_Types\Control::get_helper();
		$helper->update_control_metadatum($item);
	}
}


 ?>