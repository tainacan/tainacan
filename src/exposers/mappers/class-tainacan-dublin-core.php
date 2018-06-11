<?php

namespace Tainacan\Exposers\Mappers;

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
	public $prefix = 'dc:';
	public $metadata = [
		'contributor' => [
			'URI' => 'http://purl.org/dc/elements/1.1/contributor',
			'label' => 'Contributor'
		],
		'coverage' => [
			'URI' => 'http://purl.org/dc/elements/1.1/coverage',
			'label' => 'Coverage'
		],
		'creator' => [
			'URI' => 'http://purl.org/dc/elements/1.1/creator',
			'label' => 'Creator'
		],
		'date' => [
			'URI' => 'http://purl.org/dc/elements/1.1/date',
			'label' => 'Date',
            'metadatum_type' => 'date'
		],
		'description' => [
			'URI' => 'http://purl.org/dc/elements/1.1/description',
			'label' => 'Description',
		    'core_metadatum' => 'description'
		],
		'format' => [
			'URI' => 'http://purl.org/dc/elements/1.1/format',
			'label' => 'Format',
		],
		'identifier' => [
			'URI' => 'http://purl.org/dc/elements/1.1/identifier',
			'label' => 'Identifier'
		],
		'language' => [
			'URI' => 'http://purl.org/dc/elements/1.1/language',
			'label' => 'Language'
		],
		'publisher' => [
			'URI' => 'http://purl.org/dc/elements/1.1/publisher',
			'label' => 'Publisher'
		],
		'relation' => [
			'URI' => 'http://purl.org/dc/elements/1.1/relation',
			'label' => 'Relation'
		],
		'rights' => [
			'URI' => 'http://purl.org/dc/elements/1.1/rights',
			'label' => 'Rights'
		],
		'source' => [
			'URI' => 'http://purl.org/dc/elements/1.1/source',
			'label' => 'Source'
		],
		'subject' => [
			'URI' => 'http://purl.org/dc/elements/1.1/subject',
			'label' => 'Subject'
		],
		'title' => [
			'URI' => 'http://purl.org/dc/elements/1.1/title',
			'label' => 'Title',
		    'core_metadatum' => 'title'
		],
		'type' => [
			'URI' => 'http://purl.org/dc/elements/1.1/type',
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