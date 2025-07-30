<?php 

namespace Tainacan;

class Background_Importer extends Background_Process {
	
	/**
	 * @var string
	 */
	protected $action = 'import';

	/**
	 * @var int
	 */
	private $finish_status = 1;

	public function __construct() {
		parent::__construct();
				
		/**
		 * The name is defined after 'init' hook due to the loading of translation files.
		 * 
		 * @see https://make.wordpress.org/core/2024/10/21/i18n-improvements-6-7/
		 */
		add_action( 'init', function() { $this->set_name( __('Importer', 'tainacan') ); } );
	}

	/**
	 * @param $status
	 */
	private function set_finish_status( $status ){
		$this->finish_status = $status;
	}
	
	function task($batch) {

		$data = $batch->data;
		$key = $batch->key;
		
		if (!defined('TAINACAN_DOING_IMPORT')) define('TAINACAN_DOING_IMPORT', true);
		
		$className = $data['class_name'];
		if (class_exists($className)) {
			$object = new $className($data);
			$runned = $object->run();
			
			$this->write_log($key, $object->get_log());
			$this->write_error_log($key, $object->get_error_log());

			$batch->progress_label = $object->get_progress_label();
			$batch->progress_value = $object->get_progress_value();

			$batch->data = $object->_to_Array(true);

			if (true === $object->get_abort()) {
				throw new \Exception('Process aborted by Importer');
			}
			
			if (false === $runned) {
				$batch->output = $object->get_output();
				$this->update($key, $batch);
				return false;
			}
			
			return $batch;
		}
		return false;
		
		
	}

}