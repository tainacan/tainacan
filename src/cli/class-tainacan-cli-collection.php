<?php 

namespace Tainacan;

use WP_CLI;
use Tainacan\Repositories;

class Cli_Collection {

	private $collection_repository;
	private $items_repository;
	private $result_count;

	public function __construct() {
		$this->items_repository = Repositories\Items::get_instance();
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->result_count = ['items' => 0, 'attachments' => 0];
	}

	public function list() {
		$response = [];
		$collections = $this->collection_repository->fetch(['per-page'=>-1], 'OBJECT');
		foreach ($collections as $collection) {
			array_push($response, ['ID' => $collection->get_id(), 'title'=> $collection->get_name()]);
		}
		\WP_CLI\Utils\format_items( 'table', $response, ['ID', 'title'] );
	}

	public function clean($args, $assoc_args) {
		$permanently = false;
		if( empty($assoc_args['collection-id']) ) {
			\WP_CLI::error( 'Wrong parameters', true );
		}
		$collection_id = $assoc_args['collection-id'];

		if( !empty($assoc_args['permanently']) ) {
			$permanently = true; 
		}

		$per_page = 50; $page = 0;
		$args = [
			'posts_per_page'=> $per_page,
			'paged' => $page,
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash')
		];
		$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		$total = $collection_items->found_posts;
		$last_page = ceil($total/$per_page);

		$progress = \WP_CLI\Utils\make_progress_bar( "\ncleaning collection items: \n", $total );
		while ($page <= $last_page) {
			if ($collection_items->have_posts()) {
				while ( $collection_items->have_posts() ) {
					$collection_items->the_post();
					$item = new Entities\Item($collection_items->post);
					$this->delete_item($item, $permanently);
					$progress->tick();
				}
			}
			$args['paged'] = $page++;
			$collection_items = $this->items_repository->fetch($args, $collection_id, 'WP_Query');
		}
		$progress->finish();

		$msg = "\n" . $this->result_count['items']       . " items removed" .
					 "\n" .	$this->result_count['attachments'] . " attachments removed";

		\WP_CLI::success( $msg );
	}

	private function delete_item( $item, $permanently = false ) {
		if (! $item instanceof Entities\Item) {
			\WP_CLI::error( 'An item with this ID was not found', true );
		}

		$this->result_count['items']++;
		if($permanently == true) {
			$this->delete_attachments($item);
			$item = $this->items_repository->delete($item);
		} else {
			$item = $this->items_repository->trash($item);
		}
		return $item;
	}

	private function delete_attachments ( $item ) {
		$this->result_count['attachments']++;
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
			wp_delete_attachment($attachment->ID);
		}
	}

}


 ?>