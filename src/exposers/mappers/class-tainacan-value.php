<?php

namespace Tainacan\Exposers\Mappers;

class Value extends Mapper {
	public $type = 'Value';
	public $name = 'value';
	public $allow_extra_fields = true;
	public $context_url = '';
	public $header = '';
	public $options = [];
	
	public function rest_response($item_arr, $request) {
		$ret = $item_arr;
		if(array_key_exists('field', $item_arr)){ // getting a unique field
			$field_mapping = $item_arr['field']['exposer_mapping'];
			if(array_key_exists($this->name, $field_mapping)) {
				$ret = [$field_mapping['value']['name'] => $item_arr['value']];
			} else {
				$ret = [$item_field['field']['name'] => $item_arr['value']];
			}
		} else { // array of elements
			$ret = [];
			foreach ($item_arr as $item_field) {
				$field_mapping = $item_field['field']['exposer_mapping'];
				if(array_key_exists($this->name, $field_mapping)) {
					$ret[$field_mapping[$this->name]['name']] = $item_field['value'];
				} else {
					$ret[$item_field['field']['name']] = $item_field['value'];
				}
			}
		}
		return $ret;
	}
}