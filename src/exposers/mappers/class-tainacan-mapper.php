<?php

namespace Tainacan\Exposers\Mappers;

abstract class Mapper {
	public $slug = null; // Slug of Mapper, used as option on api call 
	public $name = null; // Public name do mapper
	public $allow_extra_metadata = true; // Allow more metadatum to be register
	public $context_url = null; // URL of mapper documentation
	public $type = ''; // The Class of the ontology that this mapping refers to. For example `CreativeWork`, which is a class of Schema.org if applied
	
	/**
	 * array of supported metadata, false for not validade the list format:
	 * ['slug'] => [
	 *     'URI' => 'http://...',          // URI of the metadatum description
	 *     'label' => 'Label',             // Label to show on UI
	 *     'metadata_type' => 'date',         // Tainacan recomended metadatum type, default text
	 *     'core_metadatum' => false   // if have a core tainacan metadatum, what?
	 * ['date' => [
	 *     	'URI' => 'http://purl.org/dc/elements/1.1/date',
	 *  	'label' => 'Date',
     *      'metadata_type' => 'date'
	 *  ],
	 *  'description' => [
	 *  	'URI' => 'http://purl.org/dc/elements/1.1/description',
	 *  	'label' => 'Description',
	 *      'core_metadatum' => true
	 *  ]]
	 * @var array
	 */
	public $metadata = false;
	
	public $prefix = ''; // Tag prefix like "dc:"
	public $sufix = ''; // Tag sufix
	public $header = false; // API response header or file header to be used with
	public $show_ui = true; // Show mapper in ui and api calls
	
	public function _toArray() {
		return [
			'slug' => $this->slug,
			'name' => $this->name,
			'allow_extra_metadata' => $this->allow_extra_metadata,
			'context_url' => $this->context_url,
			'metadata' => $this->metadata,
			'prefix' => $this->prefix,
			'sufix' => $this->sufix,
			'header' => $this->header
		];
	}
}