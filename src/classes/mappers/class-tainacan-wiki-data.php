<?php

namespace Tainacan\Mappers;

/**
 * Support Dublin Core Mapping 
 * http://purl.org/dc/elements/1.1/
 *
 */
class Wiki_Data extends Mapper {
	public $slug = 'wiki-data';
	public $name = 'WikiData';
	public $allow_extra_metadata = true;
	public $context_url = 'http://??????';
	public $header = '??????';
	public $prefixes = [
		'dc' => 'http://purl.org/dc/elements/1.1/'
	];
	public $metadata = [
		
	];	
}