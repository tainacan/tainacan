<?php

namespace Tainacan\Exposers\Mappers;

class Dublin_Core extends Mapper {
	public $slug = 'dublin-core';
	public $name = 'Dublin Core';
	public $allow_extra_fields = true;
	public $context_url = 'http://dublincore.org/documents/dcmi-terms/';
	public $header = '<?xml version="1.0"?><!DOCTYPE rdf:RDF SYSTEM "http://dublincore.org/2000/12/01-dcmes-xml-dtd.dtd"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" ></rdf:RDF>';
	public $prefix = 'dc:';
	public $options = [];
	
	/** XML especial case **/
	const XML_DC_NAMESPACE = 'http://purl.org/dc/elements/1.1/';
	const XML_RDF_NAMESPACE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	public $XML_namespace = 'http://purl.org/dc/elements/1.1/';
	public $XML_append_root = 'rdf:Description'; 
	/** END: XML especial case **/
	
}