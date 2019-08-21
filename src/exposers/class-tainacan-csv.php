<?php

namespace Tainacan\Exposers;

/**
 * Generate a Csv formated response
 *
 */
class Csv extends Exposer {
	
	public $slug = 'csv'; // type slug for url safe

	function __construct() {
		$this->set_name( __('CSV', 'tainacan') );
		$this->set_description( __('Comma-separated values', 'tainacan') );
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( [
			'Content-Type: text/csv; charset=' . get_option( 'blog_charset' )
		]);
		
		if (isset($request['collection_id'])) {
			$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $request['collection_id'] );
			if ($collection) {
				$filename = sanitize_title($collection->get_name());
			}
		} else {
			$filename = sanitize_title(get_option('blogname'));
		}
		
		if (empty($filename)) {
			$filename = sanitize_title( __('items', 'tainacan') );
		}
		
		if ( isset($request['paged']) ) {
			$filename .= '-' . $request['paged'];
		}
		
		$filename .= '.csv';
		
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		
		$items = $response->get_data();
		$items = is_array($items) && isset($items['items']) ? $items['items'] : [];
		
		if (sizeof($items) > 0) {
			
			$csv = fopen('php://memory', 'r+');
			
			$headers = array_map(function($a) {
				return $a['name'];
			}, $items[0]['metadata']);
			
			fputcsv($csv, $headers, ';', '"' );

			foreach ($items as $item) {
				$values = array_map(function($a) {
					return $a['value_as_string'];
				}, $item['metadata']);
				
				fputcsv($csv, $values, ';', '"' );
				
			}
			rewind($csv);
			$ret_csv = stream_get_contents($csv);
			fclose($csv);
			$response->set_data($ret_csv);
			
		}
		
		return $response;
		
	}
	
}