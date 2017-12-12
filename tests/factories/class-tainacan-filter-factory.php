<?php

namespace Tainacan\Tests\Factories;

class Filter_Factory {
	private $filter;
	protected $filter_type;

	public function create_filter($type, $supported_types = []){
		if(empty($type)){
			throw new \InvalidArgumentException(__('The type can\'t be empty', 'tainacan'));
		} elseif(!strrchr($type, '_')){
			$type = ucfirst(strtolower($type));
		} else {
			$type = ucwords(strtolower($type), '_');
		}

		$this->filter_type = "\Tainacan\Filter_Types\\$type";
		$this->filter = new $this->filter_type(/* Here goes the supported types */);

		return $this->filter;
	}
}

?>