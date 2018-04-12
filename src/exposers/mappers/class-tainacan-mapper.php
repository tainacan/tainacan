<?php

namespace Tainacan\Exposers\Mappers;

abstract class Mapper {
	public $slug = null;
	public $name = null;
	public $allow_extra_fields = true;
	public $context_url = null;
	public $opstions = false;
	public $prefix = '';
	public $sufix = '';
	public $header = false;
	
	//public abstract function rest_response($item_arr, $request);
}