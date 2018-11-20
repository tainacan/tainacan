<?php

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;

class CSV extends Exporter {

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
		$this->set_mapping_method('any'); // set all method to mapping
		//$this->set_mapping_method('list', [ "dublin-core" => "Tainacan\\Exposers\\Mappers\\Dublin_Core" ]); // set specific list of methods to mapping
		//todo create list only slug
	}

	public function process_item( $processed_item, $mapping ) {
		if( $processed_item ) {
			$csv_line = '';
			foreach ($processed_item as $key => $value) {
				$csv_line .= $this->str_putcsv($value, $mapping, ',', '"');
			}
			$this->append_to_file('exporter', $csv_line."\n");
		} else {
			$this->add_error_log('failed on item '. $this->get_current_collection() );
		}
	}

	public function output_header($collection_definition) {
		$columns = [];
		foreach ($collection_definition['mapping'] as $key => $value) {
			$columns[] = $value;
		}
		$this->append_to_file('exporter', \implode(",", $columns) . "\n");
		return false;
	}

	function str_putcsv($item, $mapping, $delimiter = ',', $enclosure = '"') {
		// Open a memory "file" for read/write...
		$fp = fopen('php://temp', 'r+');
		$out=[];
		foreach ($item as $key => $value) {
			if (array_key_exists($key, $mapping)) {
				if (is_array($value)) {
					$out[] = implode("||", $value);
				} else {
					$out[] = $value;
				}
			}
		}
		fputcsv($fp, $out, $delimiter, $enclosure);
		rewind($fp);
		//Getting detailed stats to check filesize:
		$fstats = fstat($fp);
		$data = fread($fp, $fstats['size']);
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