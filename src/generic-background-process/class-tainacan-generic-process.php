<?php
namespace Tainacan\GenericBackgroundProcess;
use Tainacan;
use Tainacan\Entities;

abstract class Generic_Process {

	public function __construct( $attributess = array() ) {
		$this->id = uniqid();
		$author = get_current_user_id();
		if($author) {
			$this->add_transient('author', $author);
		}
		if (!empty($attributess)) {
			foreach ($attributess as $attr => $value) {
				$method = 'set_' . $attr;
				if (method_exists($this, $method)) {
					$this->$method($value);
				}
			}
		}
	}

	/**
	 * The ID for this process session
	 *
	 * When creating a new process session via API, an id is returned and used to access this
	 * process instance
	 * 
	 * @var identifier
	 */
	protected $id;

	/**
	 * Declares what are the steps the process will run, in the right order.
	 *
	 * By default, there is only one step, and the callback is the main_process method 
	 *
	 * Child classes may declare as many steps as they want and can keep this default step to use 
	 * this method for process. But it is optional.
	 * 
	 * @var array
	 */
	protected $steps = [
		[
			'name' => 'Main process',
			'progress_label' => 'Generic process',
			'callback' => 'main_process'
		]
	];

	/**
	 * Transients is used to store temporary data to be used accross multiple requests
	 *
	 * Add and remove transient data using add_transient() and delete_transient() methods
	 *
	 * Transitens can be strings, numbers or arrays. Avoid storing objects.
	 * 
	 * @var array
	 */
	protected $transients = [];

	/**
	 * Wether to abort process execution.
	 * @var bool
	 */
	protected $abort = false;

	protected $current_step = 0;
	protected $in_step_count = 0;

	protected $log = [];
	protected $error_log = [];

	/**
	 * List of attributes that are saved in DB and that are used to 
	 * reconstruct the object, this property need be overwrite.
	 * @var array
	 */
	protected $array_attributes = [
		'in_step_count',
		'current_step',
		'transients'
	];

	/**
	 * @return string
	 */
	public function get_id(){
		return $this->id;
	}

	public function get_current_step() {
		return $this->current_step;
	}
	
	public function set_current_step($value) {
		$this->current_step = $value;
	}

	public function get_in_step_count() {
		return $this->in_step_count;
	}

	public function set_in_step_count($value) {
		$this->in_step_count = $value;
	}

	public function set_steps($steps) {
		$this->steps = $steps;
	}

	public function get_steps() {
		return $this->steps;
	}

	protected function get_transients() {
		return $this->transients;
	}

	protected function set_transients(array $data) {
		$this->transients = $data;
	}

	public function add_transient($key, $data) {
		$this->transients[$key] = $data;
	}

	public function delete_transient($key) {
		if (isset($this->transients[$key]))
			unset($this->transients[$key]);
	}

	public function get_transient($key) {
		if (isset($this->transients[$key]))
			return $this->transients[$key];
		return null;
	}

	public function get_log() {
		return $this->log;
	}

	public function get_error_log() {
		return $this->error_log;
	}

	public function add_log($message ) {
		$this->log[] = $message;
	}

	public function add_error_log($message ) {
		$this->error_log[] = $message;
	}

	public function is_finished() {
		if($this->current_step >= count($this->steps)) {
			return true;
		}
		return false;
	}

	/**
	 * Cancel Scheduled abortion at the end of run()
	 * @return void
	 */
	protected function cancel_abort() {
		$this->abort = false;
	}

	/**
	 * Schedule importer abortion at the end of run()
	 * @return void
	 */
	protected function abort() {
		$this->abort = true;
	}

	/**
	 * Return wether importer should abort execution or not
	 * @return bool 
	 */
	public function get_abort() {
		return $this->abort;
	}

	/**
	 * Gets the current label to be displayed below the progress bar to give
	 * feedback to the user.
	 * 
	 * It automatically gets the attribute progress_label from the current step running.
	 * 
	 * @return string
	 */
	public function get_progress_label() {
		$current_step = $this->get_current_step();
		$steps = $this->get_steps();
		$label = '';
		if ( isset($steps[$current_step]) ) {
			$step = $steps[$current_step];
			if ( isset($step['progress_label']) ) {
				$label = $step['progress_label'];
			} elseif ( isset($step['name']) ) {
				$label = $step['name'];
			}
		}

		if ( sizeof($steps) > 1 ) {
			$preLabel = sprintf( __('Step %d of %d', 'tainacan'), $current_step + 1, sizeof($steps) );
			$label = $preLabel . ': ' . $label;
		}

		if ( empty($label) ) {
			$label = __('Running process', 'tainacan');
		}

		return $label;
	}

	/**
	 * Gets the current value to build the progress bar and give feedback to the user
	 * on the background process that is running the process.
	 * 
	 * It does so by comparing the "size" attribute with the $in_step_count class attribute
	 * where size indicates the total size of iterations the step will take and $this->in_step_count 
	 * is the current iteration.
	 * 
	 * 
	 * The value must be from 0 to 100
	 * 
	 * If a negative value is passed, it is assumed that the progress is unknown
	 */
	public function get_progress_value() {
		$current_step = $this->get_current_step();
		$steps = $this->get_steps();
		$value = -1;

		if ( isset($steps[$current_step]) ) {
			$step = $steps[$current_step];
			if ( isset($step['total']) && is_integer($step['total']) && $step['total'] > 0 ) {
				$current = $this->get_in_step_count();
				$value = round( ($current/$step['total']) * 100 );
			}
		}
		return $value;
	}

	/**
	 * Sets the total attribute for the current step
	 * 
	 * The "total" attribute of a step indicates the number of iterations this step will take to complete.
	 * 
	 * The iteration is counted using $this->in_step_count attribute, and comparing the two values gives us
	 * the current progress of the process.
	 * 
	 */
	protected function set_current_step_total($value) {
		$this->set_step_total($this->get_current_step(), $value);
	}

	/**
	 * Sets the total attribute for a given step
	 * 
	 * The "total" attribute of a step indicates the number of iterations this step will take to complete.
	 * 
	 * The iteration is counted using $this->in_step_count attribute, and comparing the two values gives us
	 * the current progress of the process.
	 * 
	 */
	protected function set_step_total($step, $value) {
		$steps = $this->get_steps();
		if (isset($steps[$step]) && is_array($steps[$step])) {
			$steps[$step]['total'] = $value;
			$this->set_steps($steps);
		}
	}

	protected function next_step() {
		$current_step = $this->get_current_step();
		$steps = $this->get_steps();
		$current_step ++;
		if (isset($steps[$current_step])) {
			$this->set_current_step($current_step);
			return $current_step;
		}
		return false;
	}

	public function _to_Array($short = false) {
		$return = ['id' => $this->get_id()];
		foreach ($this->array_attributes as $attr) {
			$method = 'get_' . $attr;
			$return[$attr] = $this->$method();
		}
		$return['class_name'] = get_class($this);

		if ($short === false) {
			// global $Generic_Process_Handler;
			// $process_definition = $Generic_Process_Handler->get_process_by_object($this);
			// $return['key']	= $process_definition['manual_collection'];
		}

		return $return;
	}

	public function finished() {
		$this->add_log('finished');
	}

	/**
	 * runs one iteration
	 */
	public function run() {
		if ($this->is_finished()) {
			return false;
		}
		$steps = $this->get_steps();
		$current_step = $this->get_current_step();
		$method_name = $steps[$current_step]['callback'];

		if (method_exists($this, $method_name)) {
			$author = $this->get_transient('author');
			$this->add_log('User in process: ' . $author);
			wp_set_current_user($author);
			$result = $this->$method_name();
		} else {
			$this->add_error_log( 'Callback not found for step ' . $steps[$current_step]['name']);
			$result = false;
		}
		if($result === false || (!is_numeric($result) || $result < 0)) {
			//Move on to the next step
			$this->set_in_step_count(0);
			$return = $this->next_step();
		} else if(is_numeric($result) && $result >= 0) {
			$this->set_in_step_count($result);
			$return = $result;
		}
		
		if (false === $return) {
			$this->finished();
		}
		
		return $return;
	}
}