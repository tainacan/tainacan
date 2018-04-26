<?php

namespace Tainacan\Exposers\Mappers;

/**
 *  Mapper class for export fields in key => value format where key can be defined 
 *
 */
class Value extends Mapper {
	public $slug = 'value';
	public $name = 'Value';
	public $allow_extra_fields = true;
	public $context_url = '';
	public $header = '';
	public $metadata = false;
	
}