<?php

namespace Tainacan\Exposers\Mappers;

abstract class Mapper {
	public $type = null;
	public $name = null;
	public $allow_extra_fields = true;
	public $context_url = null;
	
	public abstract function rest_response($item_arr, $request);
}