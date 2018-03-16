<?php

namespace Tainacan\Exposers\Mappers;

class Dublin_Core extends Mapper {
	public $type = 'DublinCore';
	public $name = 'Dublin Core';
	public $allow_extra_fields = true;
	public $context_url = 'http://dublincore.org/documents/dcmi-terms/';
	
	public function rest_response($item_arr, $request) {
		$field_mapping = $item_arr['field']['exposer_mapping'];
		if(array_key_exists('dublin-core', $field_mapping)) {
			$ret = ['dc:'.$field_mapping['dublin-core']['name'] => $item_arr['value']];
			return $ret;
		}
		return $item_arr;
	}
}