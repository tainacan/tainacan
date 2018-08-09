# Exposers

Exposers are declarations and methods that expose the items of your repository in a certain way.

## Structure

### Name

String $name

The name of the Exposer.

### Slug

String $slug

A URL friendly version of the Exposer name, to be used as a parameter to the API request informing you want to get the data using this exposer.


### Supported Mapping Standard

Array or true $mappers

A list of mapping standards that is exposer supports. This means that whenever someone makes a request to receive that via this exposer, he/she will also be able to choose in which mapping standar they want the content to be served, or true for serve all mappers.


### Methods

Every exposer have to implement PHP methods that will build the API response and have to be parent of Tainacan\Exposers\Types\Type

####callback rest response

\WP_REST_Response rest_request_after_callbacks will receve a array of elements to be exposed, and api request, server and reponse.

	/**
		 * Change response after api callbacks
		 * @param \WP_REST_Response $response
		 * @param \WP_REST_Server $handler
		 * @param \WP_REST_Request $request
		 * @return \WP_REST_Response
		 */
		public function rest_request_after_callbacks( $response, $handler, $request ); 

Using this method an exposer can also print data in the `HEAD` section of the HTML when visiting an item page. For example, JSON-LD exposer can add a JSON-LD object to the head of the page of every item in your collection, modifing the rest server ($handler).

### Example

	<?php
	
	namespace Tainacan\Exposers\Types;
	
	/**
	 * Generate a text formated response
	 *
	 */
	class Txt extends Type {
		
		public $mappers = ['Value'];
		public $slug = 'txt'; // type slug for url safe
		
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
					$key = apply_filters('tainacan-exposer-numeric-item-prefix', __('item', 'tainacan').'-', get_class($this)).$key; //dealing with giving a key prefix
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
		