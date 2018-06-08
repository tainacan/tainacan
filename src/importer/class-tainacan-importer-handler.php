<?php 

namespace Tainacan;

class Importer_Handler {
	
	
	
	function __construct() {
		
		$this->bg_importer = new Background_Importer();
		
	}
	
	function add_to_queue($importer_object) {
		$data = $importer_object->_to_Array();
		$this->bg_importer->data($data)->save()->dispatch();
	}
	
	
}

global $Tainacan_Importer_Handler;
$Tainacan_Importer_Handler = new Importer_Handler();

 ?>