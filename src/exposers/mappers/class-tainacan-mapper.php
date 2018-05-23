<?php

namespace Tainacan\Exposers\Mappers;

abstract class Mapper {
	public $slug = null; // Slug of Mapper, used as option on api call 
	public $name = null; // Public name do mapper
	public $allow_extra_fields = true; // Allow more field to be register
	public $context_url = null; // URL of mapper documentation
	public $metadata = false; // array of supported metadata, false for not validade the list 
	public $prefix = ''; // Tag prefix like "dc:"
	public $sufix = ''; // Tag sufix
	public $header = false; // API response header or file header to be used with
	
	public function _toArray() {
		return [
			'slug' => $this->slug,
			'name' => $this->name,
			'allow_extra_fields' => $this->allow_extra_fields,
			'context_url' => $this->context_url,
			'metadata' => $this->metadata,
			'prefix' => $this->prefix,
			'sufix' => $this->sufix,
			'header' => $this->header
		];
	}
}