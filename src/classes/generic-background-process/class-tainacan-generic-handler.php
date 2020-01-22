<?php 

namespace Tainacan;

class Generic_Process_Handler {

	private $registered_process = [];

	function __construct() {
		$this->bg_process = new Background_Generic_Process();
		add_action('init', array(&$this, 'init'));
	}

	public function init() {

		$this->register_generic_process([
			'name' => 'Bulk edit',
			'description' => __('Bulk edit', 'tainacan'),
			'slug' => 'bulk_edit',
			'class_name' => '\Tainacan\GenericBackgroundProcess\Bulk_Edit_Process'
		]);

		do_action('tainacan-register-generic_process');
	}

	public function register_generic_process(array $process) {
		$attrs = wp_parse_args($process);

		if (!isset($attrs['slug']) || !isset($attrs['class_name']) || !isset($attrs['name'])) {
			return false;
		}
	
		$this->registered_process[$process['slug']] = $attrs;
		return true;
	}
	
	function add_to_queue(\Tainacan\GenericBackgroundProcess\Generic_Process $process_object) {
		$data = $process_object->_to_Array(true);
		$process = $this->get_generic_process_by_object($process_object);
		
		$process_name = sprintf( __('%s process', 'tainacan'), $process['name'] );

		$bg_process = $this->bg_process->data($data)->set_name($process_name)->save();
		if ( is_wp_error($bg_process->dispatch()) ) {
			return false;
		}
		return $bg_process;
	}

	public function unregister_generic_process($slug) {
		unset($this->registered_process[$slug]);
	}

	public function get_registered_generic_process() {
		return $this->registered_process;
	}

	public function get_generic_process($slug) {
		$process = $this->get_registered_generic_process();
		if (isset($process[$slug])) {
			return $process[$slug];
		}
		return null;
	}

	public function get_generic_process_by_object(\Tainacan\GenericBackgroundProcess\Generic_Process $process_object) {
		$class_name = get_class($process_object);
		$class_name = '\\' . $class_name;
		$generic_process = $this->get_registered_generic_process();
		foreach ($generic_process as $process) {
			if ($process['class_name'] == $class_name)
				return $process;
		}
		return null;
	}

	public function initialize_generic_process($slug) {
		$process = $this->get_generic_process($slug);
		if ( is_array($process) && isset($process['class_name']) && class_exists($process['class_name']) ) {
			$prc = new $process['class_name']();
			return $prc;
		}
		return false;
	}

	/**
	 * Save process instance to the database
	 * @param  Tainacan\GenericBackgroundProcess\Generic_Process $process The process object
	 * @return void
	 */
	public function save_process_instance(\Tainacan\GenericBackgroundProcess\Generic_Process $process) {
		update_option('tnc_transient_' . $process->get_id(), $process, false);
	}

	/**
	 * Retrieves an Process instance from the database based on its session_id
	 * @param  string $session_id The Process ID
	 * @return Tainacan\GenericBackgroundProcess\Generic_Process|false The Process object, if found. False otherwise
	 */
	public function get_process_instance_by_session_id($session_id) {
		$process = get_option('tnc_transient_' . $session_id);
		return $process;
	}

	/**
	 * Deletes this process instance from the database
	 * @param  Tainacan\GenericBackgroundProcess\Generic_Process $process The Process object
	 * @return bool True, if process is successfully deleted. False on failure.
	 */
	public function delete_process_instance(\Tainacan\GenericBackgroundProcess\Generic_Process $process) {
		return delete_option('tnc_transient_' . $process->get_id());
	}

}

global $Tainacan_Generic_Process_Handler;
$Tainacan_Generic_Process_Handler = new Generic_Process_Handler();
?>