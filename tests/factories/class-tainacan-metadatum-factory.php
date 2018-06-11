<?php

namespace Tainacan\Tests\Factories;

class Metadatum_Factory {
	private $metadatum;
	protected $metadatum_type;

	public function create_metadatum($type, $primitive_type = []){
		if(empty($type)){
			throw new \InvalidArgumentException('The type can\'t be empty');
		} elseif(!strrchr($type, '_')){
			$type = ucfirst(strtolower($type));
		} else {
			$type = ucwords(strtolower($type), '_');
		}

		$this->metadatum_type = "\Tainacan\Metadatum_Types\\$type";
		$this->metadatum = new $this->metadatum_type(/* Here goes the primitive type */);

		return $this->metadatum;
	}
}

?>