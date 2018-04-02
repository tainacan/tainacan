<?php

namespace Tainacan\Exposers\Types;

class Html extends Type {
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( ['Content-Type: text/html; charset=' . get_option( 'blog_charset' )] );
		$html = '
			<!DOCTYPE html>
			<html>
				<body>
		';
					$html = $this->array_to_html($response->get_data(), apply_filters('tainacan-exposer-html', $html));
					$html .= '
				</body>
			</html>
		';
		
		$response->set_data($html);
		return $response;
	}
	
	/**
	 * Convert Array to Html
	 * @param array $data
	 * @param string $html
	 * @return string
	 */
	protected function array_to_html( $data, $html ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				//$key = apply_filters('tainacan-exposer-numeric-item-prefix', __('item', 'tainacan').'-', get_class($this)).$key; //dealing with <0/>..<n/> issues
			}
			if( is_array($value) ) {
				//$html .= $key.": ".$this->array_to_html($value, '['.$html.']\n');
			} else {
				//$html .= $key.": ".$value .'\n';
			}
		}
		return $html;
	}
}