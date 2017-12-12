<?php

namespace Tainacan\Tests\Factories;

class Field_Factory {
	private $field;
	protected $field_type;

	public function create_field($type, $primitive_type = []){
		if(empty($type)){
			throw new \InvalidArgumentException('The type can\'t be empty');
		} elseif(!strrchr($type, '_')){
			$type = ucfirst(strtolower($type));
		} else {
			$type = ucwords(strtolower($type), '_');
		}

		$this->field_type = "\Tainacan\Field_Types\\$type";
		$this->field = new $this->field_type(/* Here goes the primitive type */);

		return $this->field;
	}
}

?>