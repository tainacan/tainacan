<?php

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

class CSV extends Exporter {

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->set_mapping_method('any');
	}

	public function process_item( $index, $collection_definition ) {
		$collection_id = $collection_definition['id'];
		$tainacan_items = \Tainacan\Repositories\Items::get_instance();
		
		$filters = [
			'posts_per_page' => 12,
			'paged'   => $index+1,
			'order'   => 'DESC'
		];
		
		$this->add_log('Proccessing item index ' . $index . ' in collection ' . $collection_definition['id'] );
		$items = $tainacan_items->fetch($filters, $collection_id, 'WP_Query');
		$export_items = "";
		while ($items->have_posts()) {
			$items->the_post();
			$item = new Entities\Item($items->post);
			$export_items .= json_encode($item);
		}
		wp_reset_postdata();
		return $export_items;
	}

	public function options_form() {
		ob_start();
		?>
			<div class="field">
				<p>Priemiro teste da construção de um Exporter! </p>
			</div>
		<?php
		return ob_get_clean();
	}
}