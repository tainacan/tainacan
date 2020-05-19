<?php 

namespace Tainacan;

use WP_CLI;
use Tainacan\Repositories;

class Cli_Document {

	private $collection_repository;
	private $items_repository;
	private $result_count;
	private $dry_run = false;
	
	public function __construct() {
		$this->items_repository = Repositories\Items::get_instance();
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->result_count = ['indexed_documents' => 0];
	} 

	/**
	 * index content of documents
	 *
	 * ## OPTIONS
	 * [--collection=<value>]
	 * : <value> Specific ID of the collection into which the document content of the items will be indexed, or 'all' to all collections.
	 * 
	 * 
	 * [--dry-run]
	 * : only count the total of item which will index, just output a report 
	 * 
	 * ## EXAMPLES
	 * 
	 * wp tainacan index-content --collection=416
	 * indexing documents of items to collection 416:  100% [====================================================] 0:00 / 0:00
	 * Success: 
	 * 7 items indexed
	 * 
	 * 
	 * wp tainacan index-content --collection=all
	 * indexing documents of items to collection 416:  100% [====================================================] 0:00 / 0:00
	 * Success: 
	 * 7 items indexed
	 * indexing documents of items to collection 301:  100% [====================================================] 0:00 / 0:00
	 * Success: 
	 * 10 items indexed
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

		$collection = $assoc_args['collection'];
		if ($collection == 'all') {
			$this->index_item_all_collections();
		} else {
			$this->index_item($collection);
		}
	}

	private function index_item_all_collections() {
		$collections = $this->collection_repository->fetch(['posts_per_page'=>-1], 'OBJECT');
		foreach ($collections as $collection) {
			$this->result_count['indexed_documents'] = 0;
			$this->index_item($collection->get_id());
		}
	}

	private function index_item($collection_id) {
		$per_page = 50; $page = 1;
		$args = [
			'posts_per_page'=> $per_page,
			'paged' => $page,
			'post_status' => get_post_stati()
		];
		$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		$total = $collection_items->found_posts;
		$last_page = ceil($total/$per_page);

		$progress = \WP_CLI\Utils\make_progress_bar( "indexing documents of items to collection $collection_id:", $total );
		while ($page++ <= $last_page) {
			if ($collection_items->have_posts()) {
				while ( $collection_items->have_posts() ) {
					$collection_items->the_post();
					$item = new Entities\Item($collection_items->post);
					$this->index_content_document_item($item);
					$progress->tick();
				}
			}
			$args['paged'] = $page;
			$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		}
		$progress->finish();

		$msg = "\n" . $this->result_count['indexed_documents'] . " items indexed";

		\WP_CLI::success( $msg );
	}

	private function index_content_document_item($item) {
		if (! $item instanceof Entities\Item) {
			\WP_CLI::error( 'An item with this ID was not found', true );
		}

		if ( empty( $item->get_document() ) ) {
			return null;
		}

		$this->result_count['indexed_documents']++;
		if ($this->dry_run)
			return true;
		return $this->items_repository->generate_index_content($item);
	}
}


 ?>