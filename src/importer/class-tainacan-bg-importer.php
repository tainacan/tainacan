<?php 



class Tainacan_Bg_Importer extends Tainacan_Background_Process {
	
	/**
	 * @var string
	 */
	protected $action = 'import';
	
	function task($data) {
		
		$object = new $data['class_name']($data);
		$runned = $object->run();
		if (false === $runned) {
			return false;
		}
		return $object->_to_Array();
		
	}
	
	
}


 ?>