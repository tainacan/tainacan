<?php

namespace Tainacan\Exposers\Types;

/**
 * Generate a Html formated response
 *
 */
class Html extends Type {
	
	public $mappers = ['Value'];
	public $slug = 'html'; // type slug for url safe
	public $name = 'HyperText Markup Language';
	
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
					<table>
		';
						$html .= $this->array_to_html($response->get_data());
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