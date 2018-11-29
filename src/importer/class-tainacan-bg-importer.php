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
		$this->set_name( __('Importer', 'tainacan') );
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
		
		$className = $data['class_name'];
		if (class_exists($className)) {
			$object = new $className($data);
			$runned = $object->run();
			
			$this->write_log($key, $object->get_log());
			$this->write_error_log($key, $object->get_error_log());

			if( count($object->get_error_log()) > 0 ){
			    $this->set_finish_status(2);
            }
			
			if (true === $object->get_abort()) {
                $this->set_finish_status(3);
                $this->close($key);
				throw new \Exception('Process aborted by Importer');
			}
			
			if (false === $runned) {
				return false;
			}
			
			$batch->progress_label = $object->get_progress_label();
			$batch->progress_value = $object->get_progress_value();
			
			$batch->data = $object->_to_Array(true);
			
			return $batch;
		}
		return false;
		
		
	}

    /**
     * Mark a process as done
     *
     * @param string $key Key.
     * @param array  $data Data.
     *
     * @return $this
     */
    public function close( $key ) {
        global $wpdb;

        switch ($this->finish_status){
            case 1:
                $label = __('Process completed','tainacan');
                break;

            case 2:
                $label = __('Process completed with errors','tainacan');
                break;

            case 3:
                $label = __('Process aborted by Importer','tainacan');
                break;

            default:
                $label = __('Process completed','tainacan');
                break;
        }

        $wpdb->update(
            $this->table,
            [
                'done' => 1,
                'progress_label' => $label,
                'progress_value' => 100
            ],
            ['ID' => $key]
        );

        return $this;
    }
	
	
}


 ?>