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
	
	/** XML especial case **/
	const XML_DC_NAMESPACE = 'http://purl.org/dc/elements/1.1/';
	const XML_RDF_NAMESPACE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	public $XML_namespace = 'http://purl.org/dc/elements/1.1/';
	public $XML_append_root = 'rdf:Description'; 
	/** END: XML especial case **/

	function __construct() {
		parent::__construct();

		/* Metadata should be set here to allow translable labels */ 
		$this->metadata = [
			'dc:contributor' => [
				/* translators: Label for the dc:contributor field in the Dublin Core standard */
				'label' => __( 'Contributor', 'tainacan')
			],
			'dc:coverage' => [
				/* translators: Label for the dc:coverage field in the Dublin Core standard */
				'label' => __( 'Coverage', 'tainacan')
			],
			'dc:creator' => [
				/* translators: Label for the dc:creator field in the Dublin Core standard */
				'label' => __( 'Creator', 'tainacan')
			],
			'dc:date' => [
				/* translators: Label for the dc:date field in the Dublin Core standard */
				'label' => __( 'Date', 'tainacan'),
				'metadata_type' => 'date'
			],
			'dc:description' => [
				/* translators: Label for the dc:description field in the Dublin Core standard */
				'label' => __( 'Description', 'tainacan'),
				'core_metadatum' => 'description'
			],
			'dc:format' => [
				/* translators: Label for the dc:format field in the Dublin Core standard */
				'label' => __( 'Format', 'tainacan')
			],
			'dc:identifier' => [
				/* translators: Label for the dc:identifier field in the Dublin Core standard */
				'label' => __( 'Identifier', 'tainacan')
			],
			'dc:language' => [
				/* translators: Label for the dc:language field in the Dublin Core standard */
				'label' => __( 'Language', 'tainacan')
			],
			'dc:publisher' => [
				/* translators: Label for the dc:publisher field in the Dublin Core standard */
				'label' => __( 'Publisher', 'tainacan')
			],
			'dc:relation' => [
				/* translators: Label for the dc:relation field in the Dublin Core standard */
				'label' => __( 'Relation', 'tainacan')
			],
			'dc:rights' => [
				/* translators: Label for the dc:rights field in the Dublin Core standard */
				'label' => __( 'Rights', 'tainacan')
			],
			'dc:source' => [
				/* translators: Label for the dc:source field in the Dublin Core standard */
				'label' => __( 'Source', 'tainacan')
			],
			'dc:subject' => [
				/* translators: Label for the dc:subject field in the Dublin Core standard */
				'label' => __( 'Subject', 'tainacan')
			],
			'dc:title' => [
				/* translators: Label for the dc:title field in the Dublin Core standard */
				'label' => __( 'Title', 'tainacan'),
				'core_metadatum' => 'title'
			],
			'dc:type' => [
				/* translators: Label for the dc:type field in the Dublin Core standard */
				'label' => __( 'Type', 'tainacan')
			]
		];
	}
	
}