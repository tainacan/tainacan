<?php

namespace Tainacan\Exposers\Types;

/**
 * Generate a text formated response
 *
 */
class JSON_LD extends Type {
	
	public $mappers = ['value', 'dublin-core'];
	public $slug = 'json-ld'; // type slug for url safe
	
	protected $contexts = [];
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( [
		    'Content-Type: application/json; charset=' . get_option( 'blog_charset' ),
		    'Link: <'.get_bloginfo('url').'/item.jsonld>; rel="http://www.w3.org/ns/json-ld#context"; type="application/ld+json"'
		]);
		$mapper = \Tainacan\Exposers\Exposers::request_has_mapper($request);
		if(property_exists($mapper, 'XML_namespace') && !empty($mapper->XML_namespace)) {
		    $namespace = $mapper->XML_namespace;
		    $context_slug = str_replace(':', '', $mapper->prefix);
		    $this->contexts[$context_slug] = $namespace;
		}
		$contexts = '';
		foreach ($this->contexts as $slug => $url) {
		    $contexts .= '"'.$slug.'": "'.$url.'"';
		}
		$jsonld ='';
		$jsonld ='
{
    "@context":{
'.$contexts.'
    }
    '.$this->array_to_jsonld($response->get_data(), apply_filters('tainacan-exposer-jsonld', $jsonld)).'
},
'       ;
		$response->set_data($jsonld);
		return $response;
	}
	
	/**
	 * Convert Array to Txt
	 * @param array $data
	 * @param string $jsonld
	 * @return string
	 */
	protected function array_to_jsonld( $data, $jsonld ) {
		foreach( $data as $key => $value ) {
			if( is_array($value) ) {
				$jsonld .= (strlen($jsonld) > 0 ? ',' : '').'"'.$key.'": '.$this->array_to_jsonld($value, '['.$jsonld.']');
			} else {
			    $jsonld .= (strlen($jsonld) > 0 ? ',' : '').'"'.$key.'": [{"@value": "'.$value .'"}]';
			}
		}
		return $jsonld;
	}
}