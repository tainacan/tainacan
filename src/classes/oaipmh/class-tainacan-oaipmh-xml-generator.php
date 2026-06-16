<?php

namespace Tainacan\OAIPMH;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Builds OAI-PMH 2.0 XML responses with DOMDocument.
 *
 * Element text is added through DOMDocument::createTextNode, which escapes
 * special characters exactly once, so callers pass raw values.
 */
class OAIPMH_Xml_Generator {

	const OAI_NS    = 'http://www.openarchives.org/OAI/2.0/';
	const XSI_NS    = 'http://www.w3.org/2001/XMLSchema-instance';
	const DC_NS     = 'http://purl.org/dc/elements/1.1/';
	const OAI_DC_NS = 'http://www.openarchives.org/OAI/2.0/oai_dc/';

	/**
	 * @var \DOMDocument
	 */
	private $dom;

	/**
	 * @var \DOMElement
	 */
	private $root;

	public function __construct() {
		$this->dom               = new \DOMDocument( '1.0', 'UTF-8' );
		$this->dom->formatOutput = true;
	}

	/**
	 * Create an element holding a single (escaped) text value.
	 *
	 * @param string $name
	 * @param string $value
	 * @return \DOMElement
	 */
	private function text_element( $name, $value ) {
		$element = $this->dom->createElement( $name );
		$element->appendChild( $this->dom->createTextNode( (string) ( null === $value ? '' : $value ) ) );
		return $element;
	}

	/**
	 * Initialise the response envelope.
	 *
	 * @param string $base_url
	 * @param string $verb
	 * @param array  $params
	 * @return OAIPMH_Xml_Generator
	 */
	public function init( $base_url, $verb, $params = array() ) {
		$this->root = $this->dom->createElementNS( self::OAI_NS, 'OAI-PMH' );
		$this->root->setAttributeNS(
			self::XSI_NS,
			'xsi:schemaLocation',
			self::OAI_NS . ' http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'
		);
		$this->dom->appendChild( $this->root );

		$this->root->appendChild( $this->text_element( 'responseDate', gmdate( 'Y-m-d\TH:i:s\Z' ) ) );

		$request = $this->text_element( 'request', $base_url );
		if ( $verb ) {
			$request->setAttribute( 'verb', $verb );
		}
		$allowed = array( 'identifier', 'metadataPrefix', 'from', 'until', 'set', 'resumptionToken' );
		foreach ( $allowed as $key ) {
			if ( isset( $params[ $key ] ) && '' !== $params[ $key ] && ! is_array( $params[ $key ] ) ) {
				$request->setAttribute( $key, (string) $params[ $key ] );
			}
		}
		$this->root->appendChild( $request );

		return $this;
	}

	/**
	 * @param string $code
	 * @param string $message
	 * @return OAIPMH_Xml_Generator
	 */
	public function add_error( $code, $message = '' ) {
		$error = $this->text_element( 'error', $message );
		$error->setAttribute( 'code', $code );
		$this->root->appendChild( $error );
		return $this;
	}

	/**
	 * @param array $data
	 * @return OAIPMH_Xml_Generator
	 */
	public function create_identify( $data ) {
		$identify = $this->dom->createElement( 'Identify' );
		$fields   = array(
			'repositoryName',
			'baseURL',
			'protocolVersion',
			'adminEmail',
			'earliestDatestamp',
			'deletedRecord',
			'granularity',
		);
		foreach ( $fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$identify->appendChild( $this->text_element( $field, $data[ $field ] ) );
			}
		}

		if ( ! empty( $data['repositoryIdentifier'] ) ) {
			$description = $this->dom->createElement( 'description' );
			$oai_id      = $this->dom->createElement( 'oai-identifier' );
			$oai_id->setAttribute( 'xmlns', 'http://www.openarchives.org/OAI/2.0/oai-identifier' );
			$oai_id->setAttribute( 'xmlns:xsi', self::XSI_NS );
			$oai_id->setAttribute(
				'xsi:schemaLocation',
				'http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd'
			);
			$oai_id->appendChild( $this->text_element( 'scheme', 'oai' ) );
			$oai_id->appendChild( $this->text_element( 'repositoryIdentifier', $data['repositoryIdentifier'] ) );
			$oai_id->appendChild( $this->text_element( 'delimiter', ':' ) );
			$sample = ! empty( $data['sampleIdentifier'] ) ? $data['sampleIdentifier'] : 'oai:' . $data['repositoryIdentifier'] . ':1';
			$oai_id->appendChild( $this->text_element( 'sampleIdentifier', $sample ) );
			$description->appendChild( $oai_id );
			$identify->appendChild( $description );
		}

		$this->root->appendChild( $identify );
		return $this;
	}

	/**
	 * @return OAIPMH_Xml_Generator
	 */
	public function create_metadata_formats() {
		$list   = $this->dom->createElement( 'ListMetadataFormats' );
		$format = $this->dom->createElement( 'metadataFormat' );
		$format->appendChild( $this->text_element( 'metadataPrefix', 'oai_dc' ) );
		$format->appendChild( $this->text_element( 'schema', 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd' ) );
		$format->appendChild( $this->text_element( 'metadataNamespace', self::OAI_DC_NS ) );
		$list->appendChild( $format );
		$this->root->appendChild( $list );
		return $this;
	}

	/**
	 * @param array $sets
	 * @return OAIPMH_Xml_Generator
	 */
	public function create_sets( $sets ) {
		if ( empty( $sets ) ) {
			return $this->add_error( 'noSetHierarchy', 'This repository does not support sets.' );
		}
		$list = $this->start_list( 'ListSets' );
		foreach ( $sets as $set ) {
			$this->add_set( $list, $set );
		}
		return $this;
	}

	/**
	 * @param \DOMElement $list
	 * @param array       $set
	 * @return OAIPMH_Xml_Generator
	 */
	public function add_set( $list, $set ) {
		$node = $this->dom->createElement( 'set' );
		$node->appendChild( $this->text_element( 'setSpec', $set['setSpec'] ) );
		$node->appendChild( $this->text_element( 'setName', $set['setName'] ) );
		if ( ! empty( $set['setDescription'] ) ) {
			$desc = $this->dom->createElement( 'setDescription' );
			$dc   = $this->build_oai_dc( array( 'description' => $set['setDescription'] ) );
			$desc->appendChild( $dc );
			$node->appendChild( $desc );
		}
		$list->appendChild( $node );
		return $this;
	}

	/**
	 * Start a verb list node (ListRecords/ListIdentifiers/GetRecord).
	 *
	 * @param string $type
	 * @return \DOMElement
	 */
	public function start_list( $type ) {
		$list = $this->dom->createElement( $type );
		$this->root->appendChild( $list );
		return $list;
	}

	/**
	 * @param \DOMElement $list
	 * @param array       $data
	 * @param bool        $include_metadata
	 * @return OAIPMH_Xml_Generator
	 */
	public function add_record( $list, $data, $include_metadata = true ) {
		$record = $this->dom->createElement( 'record' );
		$record->appendChild( $this->create_header( $data ) );
		if ( $include_metadata && 'trash' !== $data['status'] ) {
			$metadata = $this->dom->createElement( 'metadata' );
			$metadata->appendChild( $this->build_oai_dc( isset( $data['metadata'] ) ? $data['metadata'] : array() ) );
			$record->appendChild( $metadata );
		}
		$list->appendChild( $record );
		return $this;
	}

	/**
	 * @param \DOMElement $list
	 * @param array       $data
	 * @return OAIPMH_Xml_Generator
	 */
	public function add_header( $list, $data ) {
		$list->appendChild( $this->create_header( $data ) );
		return $this;
	}

	/**
	 * @param array $data
	 * @return \DOMElement
	 */
	private function create_header( $data ) {
		$header = $this->dom->createElement( 'header' );
		if ( 'trash' === $data['status'] ) {
			$header->setAttribute( 'status', 'deleted' );
		}
		$header->appendChild( $this->text_element( 'identifier', $data['identifier'] ) );
		$header->appendChild( $this->text_element( 'datestamp', $data['datestamp'] ) );
		if ( ! empty( $data['setSpec'] ) ) {
			$header->appendChild( $this->text_element( 'setSpec', $data['setSpec'] ) );
		}
		return $header;
	}

	/**
	 * Build an <oai_dc:dc> node from a Dublin Core field map.
	 *
	 * @param array $dc_data
	 * @return \DOMElement
	 */
	private function build_oai_dc( $dc_data ) {
		$dc = $this->dom->createElementNS( self::OAI_DC_NS, 'oai_dc:dc' );
		$dc->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:dc', self::DC_NS );
		$dc->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::XSI_NS );
		$dc->setAttributeNS(
			self::XSI_NS,
			'xsi:schemaLocation',
			self::OAI_DC_NS . ' http://www.openarchives.org/OAI/2.0/oai_dc.xsd'
		);

		$dc_fields = array(
			'title',
			'creator',
			'subject',
			'description',
			'publisher',
			'contributor',
			'date',
			'type',
			'format',
			'identifier',
			'source',
			'language',
			'relation',
			'coverage',
			'rights',
		);

		foreach ( $dc_fields as $field ) {
			if ( empty( $dc_data[ $field ] ) ) {
				continue;
			}
			$values = is_array( $dc_data[ $field ] ) ? $dc_data[ $field ] : array( $dc_data[ $field ] );
			foreach ( $values as $value ) {
				if ( '' === $value || null === $value ) {
					continue;
				}
				$dc->appendChild( $this->text_element( 'dc:' . $field, $value ) );
			}
		}

		return $dc;
	}

	/**
	 * @param \DOMElement $list
	 * @param string      $token
	 * @param int|null    $total
	 * @param int|null    $cursor
	 * @param string|null $expiration
	 * @return OAIPMH_Xml_Generator
	 */
	public function add_resumption_token( $list, $token = '', $total = null, $cursor = null, $expiration = null ) {
		$rt = $this->text_element( 'resumptionToken', $token );
		if ( null !== $total ) {
			$rt->setAttribute( 'completeListSize', (string) $total );
		}
		if ( null !== $cursor ) {
			$rt->setAttribute( 'cursor', (string) $cursor );
		}
		if ( null !== $expiration ) {
			$rt->setAttribute( 'expirationDate', $expiration );
		}
		$list->appendChild( $rt );
		return $this;
	}

	/**
	 * @return string
	 */
	public function output() {
		return $this->dom->saveXML();
	}
}
