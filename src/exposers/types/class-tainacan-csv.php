<?php

namespace Tainacan\Exposers\Types;

class Csv extends Type {
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
		$csv = '';
		$csv = $this->array_to_csv($response->get_data(), apply_filters('tainacan-exposer-csv', $csv));
		$response->set_data($csv);
		return $response;
	}
	
	/**
	 * Convert Array to Txt
	 * @param array $data
	 * @param string $csv
	 * @return string
	 */
	protected function array_to_csv( $data, $csv ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				//$key = apply_filters('tainacan-exposer-numeric-item-prefix', __('item', 'tainacan').'-', get_class($this)).$key; //dealing with <0/>..<n/> issues
			}
			if( is_array($value) ) {
				//$csv .= $key.": ".$this->array_to_csv($value, '['.$csv.']\n');
			} else {
				//$csv .= $key.": ".$value .'\n';
			}
		}
		return $csv;
	}
}