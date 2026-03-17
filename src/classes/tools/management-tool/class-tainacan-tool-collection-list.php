<?php

namespace Tainacan\Tools\Management_Tool;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Tainacan\Repositories;
use Tainacan\Tools\Output_Collector;

/**
 * List all collections (ID and title). Output is via output_table().
 *
 * @since 1.0.0
 */
class Tool_Collection_List implements \Tainacan\Tools\Management_Tool {

	private $collection_repository;

	public function __construct() {
		$this->collection_repository = Repositories\Collections::get_instance();
	}

	public function get_id() {
		return 'collection_list';
	}

	public function get_name() {
		return __( 'List collections', 'tainacan' );
	}

	public function get_description() {
		return __( 'List all collections with ID and title.', 'tainacan' );
	}

	public function get_params() {
		return [];
	}

	public function is_destructive() {
		return false;
	}

	public function is_exposed_to_rest() {
		return false;
	}

	public function run( array $args, Output_Collector $output ) {
		$rows = [];
		$collections = $this->collection_repository->fetch( [ 'posts_per_page' => -1 ], 'OBJECT' );
		foreach ( $collections as $collection ) {
			$rows[] = [ 'ID' => $collection->get_id(), 'title' => $collection->get_name() ];
		}
		$output->output_table( $rows, [ 'ID', 'title' ] );
	}
}
