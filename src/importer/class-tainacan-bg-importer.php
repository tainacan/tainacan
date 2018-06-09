<?php 

namespace Tainacan;

class Background_Importer extends Background_Process {
	
	/**
	 * @var string
	 */
	protected $action = 'import';
	
	function task($data) {

		$className = $data['class_name'];
		if (class_exists($className)) {
			$object = new $className($data);
			$runned = $object->run();
			if (false === $runned) {
				return false;
			}
			return $object->_to_Array();
		}
		return false;
		
		
	}
	
	
}


 ?>