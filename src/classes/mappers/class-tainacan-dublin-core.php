<?php

namespace Tainacan\Mappers;

/**
 * Support Dublin Core Mapping 
 * http://purl.org/dc/elements/1.1/
 *
 */
class Dublin_Core extends Mapper {
	public $slug = 'dublin-core';
	public $name = 'Dublin Core';
	public $allow_extra_metadata = true;
	public $context_url = 'http://dublincore.org/documents/dcmi-terms/';
	public $header = '<?xml version="1.0"?><!DOCTYPE rdf:RDF SYSTEM "http://dublincore.org/2000/12/01-dcmes-xml-dtd.dtd"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" ></rdf:RDF>';
	public $prefixes = [
		'dc' => 'http://purl.org/dc/elements/1.1/'
	];
	public $metadata = [
		'dc:contributor' => [
			'label' => 'Contributor'
		],
		'dc:coverage' => [
			'label' => 'Coverage'
		],
		'dc:creator' => [
			'label' => 'Creator'
		],
		'dc:date' => [
			'label' => 'Date',
            'metadata_type' => 'date'
		],
		'dc:description' => [
			'label' => 'Description',
		    'core_metadatum' => 'description'
		],
		'dc:format' => [
			'label' => 'Format',
		],
		'dc:identifier' => [
			'label' => 'Identifier'
		],
		'dc:language' => [
			'label' => 'Language'
		],
		'dc:publisher' => [
			'label' => 'Publisher'
		],
		'dc:relation' => [
			'label' => 'Relation'
		],
		'dc:rights' => [
			'label' => 'Rights'
		],
		'dc:source' => [
			'label' => 'Source'
		],
		'dc:subject' => [
			'label' => 'Subject'
		],
		'dc:title' => [
			'label' => 'Title',
		    'core_metadatum' => 'title'
		],
		'dc:type' => [
			'label' => 'Type'
		]
	];
	
	/** XML especial case **/
	const XML_DC_NAMESPACE = 'http://purl.org/dc/elements/1.1/';
	const XML_RDF_NAMESPACE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	public $XML_namespace = 'http://purl.org/dc/elements/1.1/';
	public $XML_append_root = 'rdf:Description'; 
	/** END: XML especial case **/
	
}