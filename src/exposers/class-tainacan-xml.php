<?php

namespace Tainacan\Exposers;

/**
 * Generate a Csv formated response
 *
 */
class Xml extends Exposer {
	/**
	 * {@inheritdoc}
	 * @see \Tainacan\Exposers\Types\Type::extension
	 * @var string
	 */
	protected $extension = 'xml';
	public $slug = 'xml'; // type slug for url safe
	public $name = 'Extensible Markup Language';
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( ['Content-Type: application/xml; charset=' . get_option( 'blog_charset' )] );
		$mapper = \Tainacan\Exposers_Handler::get_mapper_from_request($request);
		$xml = new \SimpleXMLElement( '<?xml version="1.0"?><data></data>' );
		$namespace = null;
		$xml_root = $xml;
		
		if($mapper) {
			if(!empty($mapper->header)) {
				$xml = new \SimpleXMLElement( $mapper->header );
			}
			if(property_exists($mapper, 'XML_namespace') && !empty($mapper->XML_namespace)) {
				$namespace = $mapper->XML_namespace;
			}
			if(property_exists($mapper, 'XML_append_root') && !empty($mapper->XML_append_root)) {
				$xml_root = $xml->addChild($mapper->XML_append_root);
			}
		}
		
		$this->array_to_xml($response->get_data(), $xml_root, $namespace);
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
				$key = apply_filters('tainacan-exposer-numeric-item-prefix', __('item', 'tainacan').'-', get_class($this)).$key; //dealing with <0/>..<n/> issues
			} else {
			    $key = str_replace(' ', '_', $key);
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