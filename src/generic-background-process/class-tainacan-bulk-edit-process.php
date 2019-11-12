<?php
namespace Tainacan\GenericBackgroundProcess;
use Tainacan;
use Tainacan\Entities;

class Bulk_Edit_Process extends Generic_Process {

	public function __construct($id) {
		parent::__construct();
		$this->id = $id;
	}

	public function main_process() {
		$this->add_log("log");
		return false;
	}

}