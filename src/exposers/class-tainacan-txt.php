<?php

namespace Tainacan\Exposers;

/**
 * Generate a text formated response
 *
 */
class Txt extends Exposer {
	
	public $mappers = ['Value'];
	public $slug = 'txt'; // type slug for url safe
	public $name = 'TXT';
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( ['Content-Type: text/plain; charset=' . get_option( 'blog_charset' )] );
		$txt = '';
		$txt = $this->array_to_txt($response->get_data(), apply_filters('tainacan-exposer-txt', $txt));
		$response->set_data($txt);
		return $response;
	}
	
	/**
	 * Convert Array to Txt
	 * @param array $data
	 * @param string $txt
	 * @return string
	 */
	protected function array_to_txt( $data, $txt ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				$key = apply_filters('tainacan-exposer-numeric-item-prefix', __('item', 'tainacan').'-', get_class($this)).$key; //dealing with <0/>..<n/> issues
			}
			if( is_array($value) ) {
				$txt .= $key.": ".$this->array_to_txt($value, '['.$txt.']\n');
			} else {
				$txt .= $key.": ".$value .'\n';
			}
		}
		return $txt;
	}
}