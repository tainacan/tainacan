<?php

namespace Tainacan\Tests\Factories;

class Metadatum_Factory {
	private $metadatum;
	protected $metadata_type;

	public function create_metadatum($type, $primitive_type = []){
		if(empty($type)){
			throw new \InvalidArgumentException('The type can\'t be empty');
		} elseif(!strrchr($type, '_')){
			$type = ucfirst(strtolower($type));
		} else {
			$type = ucwords(strtolower($type), '_');
		}

		$this->metadata_type = "\Tainacan\Metadata_Types\\$type";
		$this->metadatum = new $this->metadata_type(/* Here goes the primitive type */);

		return $this->metadatum;
	}
}

?>