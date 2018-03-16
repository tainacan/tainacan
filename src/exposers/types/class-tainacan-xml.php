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
		$xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
		$xml = $this->array_to_xml($response->get_data(), $xml);
		$response->set_data($xml->asXml());
		return $response;
	}
	
	/**
	 * Convert Array to Xml
	 * @param array $data
	 * @param \SimpleXMLElement $xml_data
	 * @return \SimpleXMLElement
	 */
	protected function array_to_xml( $data, $xml_data ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				$key = 'item'.$key; //dealing with <0/>..<n/> issues
			}
			if( is_array($value) ) {
				$subnode = $xml_data->addChild($key);
				$this->array_to_xml($value, $subnode);
			} else {
				$xml_data->addChild("$key",htmlspecialchars("$value"));
			}
		}
		return $xml_data;
	}
}