<?php

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

class CSV extends Exporter {

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->set_mapping_method('any'); // set all method to mapping
		//$this->set_mapping_method('list', [ "dublin-core" => "Tainacan\\Exposers\\Mappers\\Dublin_Core" ]); // set specific list of methods to mapping
	}

	public function process_item( $index, $collection_definition ) {
		$collection_id = $collection_definition['id'];
		$tainacan_items = \Tainacan\Repositories\Items::get_instance();
		
		$filters = [
			'posts_per_page' => 1,
			'paged'   => $index+1,
			'order'   => 'DESC'
		];

		$this->add_log('Proccessing item index ' . $index . ' in collection ' . $collection_definition['id'] );
		$items = $tainacan_items->fetch($filters, $collection_id, 'WP_Query');
		$export_items = "";
		while ($items->have_posts()) {
			$items->the_post();
			$item = new Entities\Item($items->post);
			$printCol = $index == 0;
			$export_items .= $this->get_item_csv($item, $printCol);
			$this->add_log('export_items ' . $export_items );
		}
		wp_reset_postdata();
		return $export_items;
	}

	private function get_item_csv($item, $printCol) {
		$items_metadata = $item->get_metadata();
		$prepared_item = [];
		foreach ($items_metadata as $item_metadata) {
			array_push($prepared_item, $item_metadata->_toArray());
		}
		$mapper = $this->mapping_list[$this->mapping_selected];
		$instance_mapper = new $mapper();
		$data = $this->map($prepared_item, $instance_mapper);
		return $this->str_putcsv($data, ',', '"', $printCol);
	}

	protected function map($item_arr, $mapper) {
		$ret = $item_arr;
		if(array_key_exists('metadatum', $item_arr)) { // getting a unique metadatum
			$ret = $this->map_metadatum($item_arr, $mapper);
		} else { // array of elements
			$ret = [];
			foreach ($item_arr as $item) {
				if(array_key_exists('metadatum', $item)) {
					$ret = array_merge($ret, $this->map($item, $mapper) );
				} else {
					$ret[] = $this->map($item, $mapper);
				}
			}
		}
		return $ret;
	}

	protected function map_metadatum($item_arr, $mapper) {
		$ret = $item_arr;
		$metadatum_mapping = $item_arr['metadatum']['exposer_mapping'];
		if(array_key_exists($mapper->slug, $metadatum_mapping)) {
			if(
					is_string($metadatum_mapping[$mapper->slug]) && is_array($mapper->metadata) && !array_key_exists( $metadatum_mapping[$mapper->slug], $mapper->metadata) ||
					is_array($metadatum_mapping[$mapper->slug]) && $mapper->allow_extra_metadata != true
			) {
				throw new \Exception('Invalid Mapper Option');
			}
			$slug = '';
			if(is_string($metadatum_mapping[$mapper->slug])) {
				$slug = $metadatum_mapping[$mapper->slug];
			} else {
				$slug = $metadatum_mapping[$mapper->slug]['slug'];
			}
			$ret = [$mapper->prefix.$slug.$mapper->sufix => $item_arr['value']];
		} elseif($mapper->slug == 'value') {
			$ret = [$item_arr['metadatum']['name'] => $item_arr['value']];
		} else {
			$ret = [];
		}
		return $ret;
	}

	function str_putcsv($item, $delimiter = ',', $enclosure = '"', $printCol = false) {
		// Open a memory "file" for read/write...
		$fp = fopen('php://temp', 'r+');
		$out=[];
		$col=[];
		foreach ($item as $key => $value) {
			$col[] = $key;
			$out[] = $value;
		}
		if ($printCol) {
			fputcsv($fp, $col, $delimiter, $enclosure);
		}
		fputcsv($fp, $out, $delimiter, $enclosure);
		rewind($fp);
		$data = fread($fp, 1048576);
		fclose($fp);
		return rtrim($data, "\n");
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