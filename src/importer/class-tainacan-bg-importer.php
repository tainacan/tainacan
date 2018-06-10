<?php 

namespace Tainacan;

class Background_Importer extends Background_Process {
	
	/**
	 * @var string
	 */
	protected $action = 'import';
	
	function task($data, $key) {

		$className = $data['class_name'];
		if (class_exists($className)) {
			$object = new $className($data);
			$runned = $object->run();
			
			$this->write_log($key, $object->get_log());
			$this->write_error_log($key, $object->get_error_log());
			
			if (false === $runned) {
				return false;
			}
			return $object->_to_Array();
		}
		return false;
		
		
	}
	
	
}


 ?>