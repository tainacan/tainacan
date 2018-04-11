<?php

namespace Tainacan\Exposers\Types;

class Csv extends Type {
	
	public $mappers = ['value']; 
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( [
			'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ),
			'Content-disposition: attachment;filename=tainacan.csv'] // TODO filter/optional 
		);
		ob_start();
		$csv = fopen('php://output', 'w');
		$this->array_to_csv($response->get_data(), apply_filters('tainacan-exposer-csv', $csv));
		$ret_csv = ob_get_clean();
		ob_end_clean();
		fclose($csv);
		$response->set_data($ret_csv);
		return $response;
	}
	
	/**
	 * Convert Array to Txt
	 * @param array $data
	 * @param string $csv
	 * @return string
	 */
	protected function array_to_csv( $data, $csv ) {
		$values = [];
		fputcsv($csv, array_keys($data), apply_filters('tainacan-exposer-csv-delimiter', ';') );
		fputcsv($csv, array_values($data), apply_filters('tainacan-exposer-csv-delimiter', ';') );
		return $csv;
	}
}