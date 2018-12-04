<?php

namespace Tainacan\Exposers;

/**
 * Generate a OAI_PMH/oai_dc formated response
 *
 */
class OAI_PMH extends Exposer {
	
	public $mappers = ['Dublin Core'];
	public $slug = 'oai-pmh'; // type slug for url safe
	public $name = 'Open Archives Initiative Protocol for Metadata Harvesting';
	
	const XML_OAI_DC_NAMESPACE = "http://www.openarchives.org/OAI/2.0/oai_dc/";
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Tainacan\Exposers\Types\Type::rest_request_after_callbacks()
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		$response->set_headers( ['Content-Type: application/xml; charset=' . get_option( 'blog_charset' )] );
		$xml = new \SimpleXMLElement(apply_filters('tainacan-exposer-head', '<?xml version="1.0" encoding="UTF-8"?>
			<oai_dc:dc 
    			xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
    			xmlns:dc="http://purl.org/dc/elements/1.1/" 
    			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    			xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ 
    			http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
			</oai_dc:dc>'
		));
		$namespace = apply_filters('tainacan-oai-pmh-namespace', \Tainacan\Exposers\Mappers\Dublin_Core::XML_DC_NAMESPACE);
		$this->array_to_xml($response->get_data(), apply_filters('tainacan-oai-pmh-root', $xml), $namespace);
		$response->set_data($xml->asXml());
		return $response;
	}
	
}