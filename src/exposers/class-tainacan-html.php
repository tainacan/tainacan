<?php

namespace Tainacan\Exposers;

/**
 * Generate a Html formated response
 *
 */
class Html extends Exposer {
	
	public $slug = 'html'; // type slug for url safe
	public $name = 'HyperText Markup Language';
	protected $mappers = true;
	public $accept_no_mapper = true;
	
	function __construct() {
		$this->set_name( 'HTML' );
		$this->set_description( __('A simple HTML table', 'tainacan') );
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( ['Content-Type: text/html; charset=' . get_option( 'blog_charset' )] );
		
		$items = $response->get_data();
		$items = is_array($items) && isset($items['items']) ? $items['items'] : [];
		
		$html = '
			<!DOCTYPE html>
			<html>
				<body>
					<table border="1">
		';
		
		if (sizeof($items) > 0) {
			
			$headers = array_map(function($a) {
				return $a['name'];
			}, $items[0]['metadata']);
			
			$html .= '<thead><tr>';
			
			foreach ( $items[0]['metadata'] as $slug => $meta ) {
				$html .= '<th>' . $meta['name'] . '</th>';
			}
			
			$html .= '</tr></thead>' . "\n";
			
			$html .= '<tbody>';
			
			foreach ($items as $item) {
				$values = array_map(function($a) {
					return $a['value_as_string'];
				}, $item['metadata']);
				
				$html .= '<tr>';
				
				foreach ( $item['metadata'] as $slug => $meta ) {
					$html .= '<td>' . $meta['value_as_html'] . '</td>';
				}
				
				$html .= '</tr>' . "\n";
				
				
			}
			
			$html .= '</tbody>' . "\n";
			
			
		}
		
		
		$html .= '
					</table>
				</body>
			</html>
		';
		
		$html = apply_filters('tainacan-exposer-html', $html);
					
		$response->set_data($html);
		return $response;
	}
	
	/**
	 * Convert Array to Html
	 * @param array $data
	 * @param string $html
	 * @return string
	 */
	protected function array_to_html( $data ) {
		$heads = [];
		$html = '';
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				$key = apply_filters('tainacan-exposer-numeric-item-prefix', __('item', 'tainacan').'-', get_class($this)).$key; //dealing with <0/>..<n/> issues
			}
			$heads[] = $key;
			if( is_array($value) ) {
				$html .= '<td>'.$this->array_to_html($value).'</td>';
			} else {
				$html .= '<td>'.htmlspecialchars($value).'</td>';
			}
		}
		if(count($data) > 0) $html = '<th>'.implode('</th><th>', $heads).'</th><tr>'.$html.'</tr>';
		return $html;
	}
}