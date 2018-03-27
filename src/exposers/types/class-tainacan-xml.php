<?php

namespace Tainacan\Exposers\Types;

class Xml extends Type {
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( ['Content-Type: application/xml; charset=' . get_option( 'blog_charset' )] );
		$xml = new \SimpleXMLElement(apply_filters('tainacan-exposer-head', '<?xml version="1.0"?><data></data>'));
		$namespace = apply_filters('tainacan-xml-namespace', null);
		$this->array_to_xml($response->get_data(), apply_filters('tainacan-xml-root', $xml), $namespace);
		$response->set_data($xml->asXml());
		return $response;
	}
	
	/**
	 * Convert Array to Xml
	 * @param array $data
	 * @param \SimpleXMLElement $xml_data
	 * @return \SimpleXMLElement
	 */
	protected function array_to_xml( $data, $xml_data, $namespace = null ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				$key = 'item'.$key; //dealing with <0/>..<n/> issues
			}
			if( is_array($value) ) {
				$subnode = $xml_data->addChild($key, null, $namespace);
				$this->array_to_xml($value, $subnode, $namespace);
			} else {
				$xml_data->addChild($key,htmlspecialchars("$value"), $namespace);
			}
		}
		return $xml_data;
	}
}