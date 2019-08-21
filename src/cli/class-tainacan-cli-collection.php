<?php 

namespace Tainacan;

use WP_CLI;
use Tainacan\Repositories;

class Cli_Collection {

	private $collection_repository;
	private $items_repository;
	private $result_count;
	private $dry_run = false;

	public function __construct() {
		$this->items_repository = Repositories\Items::get_instance();
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->result_count = ['items' => 0, 'attachments' => 0];
	}

	/**
	 * Show a list of collections.
	 *
	 * ## EXAMPLES
   *
	 * wp tainacan collection list
	 * +------+-------------------+
	 * | ID   | title             |
	 * +------+-------------------+
	 * | 1919 | Collection test 1 |
	 * | 1201 | Collection test 1 |
	 * | 1177 | Livros            |
	 * | 1157 | autores           |
	 * +------+-------------------+
   *
	 */
	public function list() {
		$response = [];
		$collections = $this->collection_repository->fetch(['posts_per_page'=>-1], 'OBJECT');
		foreach ($collections as $collection) {
			array_push($response, ['ID' => $collection->get_id(), 'title'=> $collection->get_name()]);
		}
		\WP_CLI\Utils\format_items( 'table', $response, ['ID', 'title'] );
	}

	/**
	 * remove items of specific collection.
	 *
	 * ## OPTIONS
	 * <collection_id>
	 * : specifies the collection that will have your items removed.
	 * 
	 * [--permanently]
	 * : skip trash and permanently delete items.
	 * 
	 * [--dry-run]
	 * : only count the total of item which will remove, just output a report 
	 * 
	 * ## EXAMPLES
	 * 
	 * wp tainacan collection clean 1201 --permanently
	 * 
	 * cleaning collection items 
	 * 100% [============================================================================================] 0:00 / 0:00
	 * Success: 
	 * 10 items removed
	 * 23 attachments removed
	 * 
	 */
	public function clean($args, $assoc_args) {
		$permanently = false;
		if( empty($args[0]) || !is_numeric($args[0]) ) {
			\WP_CLI::error( 'Wrong parameters', true );
		}
		$collection_id = $args[0];

		if( !empty($assoc_args['permanently']) ) {
			$permanently = true; 
		}

		$this->dry_run = false;
		if ( !empty($assoc_args['dry-run']) ) {
			$this->dry_run = true;
		}

		$per_page = 50; $page = 1;
		$args = [
			'posts_per_page'=> $per_page,
			'paged' => $page,
			'post_status' => get_post_stati()
			//'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash')
		];
		$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		$total = $collection_items->found_posts;
		$last_page = ceil($total/$per_page);

		$progress = \WP_CLI\Utils\make_progress_bar( "cleaning collection items:", $total );
		while ($page++ <= $last_page) {
			if ($collection_items->have_posts()) {
				while ( $collection_items->have_posts() ) {
					$collection_items->the_post();
					$item = new Entities\Item($collection_items->post);
					$this->delete_item($item, $permanently);
					$progress->tick();
				}
			}
			if($this->dry_run) $args['paged'] = $page;
			$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		}
		$progress->finish();

		if ($permanently) {
			$msg = "\n" . $this->result_count['items']       . " items removed" .
						 "\n" .	$this->result_count['attachments'] . " attachments removed";
		} else {
			$msg = "\n" . $this->result_count['items']       . " items moved to trash";
		}

		\WP_CLI::success( $msg );
	}

	private function delete_item( $item, $permanently = false) {
		if (! $item instanceof Entities\Item) {
			\WP_CLI::error( 'An item with this ID was not found', true );
		}

		$this->result_count['items']++;
		if($permanently == true) {
			$this->delete_attachments($item);
			if(!$this->dry_run) {
				$item = $this->items_repository->delete($item);
			}
		} else {
			if(!$this->dry_run) {
				$item = $this->items_repository->trash($item);
			}
		}
		return $item;
	}

	private function delete_attachments ( $item ) {
		$attachment_list = array_values(
			get_children(
				array(
					'post_parent' => $item->get_id(),
					'post_type' => 'attachment',
					'order' => 'ASC',
					'numberposts'  => -1,
				)
			)
		);
		foreach ($attachment_list as $attachment) {
			$this->result_count['attachments']++;
			if(!$this->dry_run) {
				wp_delete_attachment($attachment->ID);
			}
		}
	}

}


 ?>