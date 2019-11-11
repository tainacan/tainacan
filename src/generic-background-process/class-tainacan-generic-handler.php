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
			'class_name' => '\Tainacan\GenericBGProcess\BulkEdit'
		]);

		do_action('tainacan-register-generic_process');
	}

	public function register_generic_process(array $process) {
		$attrs = wp_parse_args($process);

		if (!isset($attrs['slug']) || !isset($attrs['class_name']) || !isset($attrs['name'])) {
			return false;
		}
	
		$this->registered_process['slug']] = $attrs;

		return true;
	}
	
	function add_to_queue(\Tainacan\process\process $process_object) {
		$data = $process_object->_to_Array(true);
		$process = $this->get_process_by_object($process_object);
		
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

	public function initialize_generic_process($slug) {
		$process = $this->get_generic_process($slug);
		if ( is_array($process) && isset($process['class_name']) && class_exists($process['class_name']) ) {
			return new $process['class_name']();
		}
		return false;
	}

	public function initialize_generic_process($slug, $id) {
		$process = $this->get_generic_process($slug);
		if ( is_array($process) && isset($process['class_name']) && class_exists($process['class_name']) ) {
			return new $process['class_name']($id);
		}
		return false;
	}
}

global $Tainacan_Generic_Process_Handler;
$Tainacan_Generic_Process_Handler = new Generic_Process_Handler();
?>