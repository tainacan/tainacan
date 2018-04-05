<?php

namespace Tainacan\Exposers\Mappers;

class Dublin_Core extends Mapper {
	public $type = 'DublinCore';
	public $name = 'Dublin Core';
	public $allow_extra_fields = true;
	public $context_url = 'http://dublincore.org/documents/dcmi-terms/';
	public $header = '';
	const XML_DC_NAMESPACE = 'http://purl.org/dc/elements/1.1/';
	const XML_RDF_NAMESPACE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	
	public $options = [];
	
	public function rest_response($item_arr, $request) {
		$ret = $item_arr;
		if(array_key_exists('field', $item_arr)){ // getting a unique field
			$field_mapping = $item_arr['field']['exposer_mapping'];
			if(array_key_exists('dublin-core', $field_mapping)) {
				$ret = ['dc:'.$field_mapping['dublin-core']['name'] => $item_arr['value']];
			}
		} else { // array of elements
			$ret = [];
			foreach ($item_arr as $item_field) {
				$field_mapping = $item_field['field']['exposer_mapping'];
				if(array_key_exists('dublin-core', $field_mapping)) {
					$ret['dc:'.$field_mapping['dublin-core']['name']] = $item_field['value'];
				}
			}
		}
		$body = json_decode( $request->get_body(), true );
		if( // treat special cases TODO better way
			is_array($body) && array_key_exists('exposer-type', $body) &&
			strtolower($body['exposer-type']) == 'xml'
		) {
			
			add_filter('tainacan-exposer-head', [$this, 'tainacan_xml_exposer_head']);
			add_filter('tainacan-xml-namespace', function($namespace) {return self::XML_DC_NAMESPACE;});
			add_filter('tainacan-xml-root', function($xml) { return $xml->addChild('rdf:Description'); });
		}
		return $ret;
	}
	
	public function tainacan_xml_exposer_head($head) {
		$head = '<?xml version="1.0"?><!DOCTYPE rdf:RDF SYSTEM "http://dublincore.org/2000/12/01-dcmes-xml-dtd.dtd"><rdf:RDF xmlns:rdf="'.self::XML_RDF_NAMESPACE.'" xmlns:dc="'.self::XML_DC_NAMESPACE.'" ></rdf:RDF>';
		return $head;
	}
}