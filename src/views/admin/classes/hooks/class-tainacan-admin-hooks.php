<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Admin_Hooks {
	use \Tainacan\Traits\Singleton_Instance;

	private $registered_hooks = [];

	private function init() {
		add_action('admin_init', [$this, 'register_hooks']);
	}

	function register_hooks() {
		do_action('tainacan-register-admin-hooks');
	}

	public function get_available_positions() {
		return apply_filters('tainacan-admin-hooks-positions', ['begin-left', 'begin-right', 'end-left', 'end-right']);
	}

	public function get_available_contexts() {
		return apply_filters('tainacan-admin-hooks-contexts', ['collection', 'metadatum', 'item', 'taxonomy', 'term', 'filter', 'role', 'metadataSection']);
	}
	
	public function get_registered_hooks() {
		return $this->registered_hooks;
	}

	/**
	 * 
	 * @param string $context The context to add the hook to (collection, metadatum, item, taxonomy, term or filter)
	 * @param callable $callback The callback that will output the form HTML
	 * @param string $position The position inside the page to hook. (begin, end, begin-left, begin-right, end-left, end-right)
	 * @param array $conditional Key-named array containing an 'attribute' and a 'value' that will be checked in the context form object before rendering the hook.
	 */
	public function register( $context, $callback, $position = 'end-left', $conditional = null ) {
		
		$contexts = $this->get_available_contexts();
		$positions = $this->get_available_positions();

		if ( !in_array($context, $contexts) || !in_array($position, $positions) ) {
			return false;
		}
				
		$result = call_user_func($callback);

		if ( is_string($result) ) {

			$this->registered_hooks[$context][$position][] = [
				'form' => $result,
				'conditional' => !empty($conditional) ? $conditional : false
			];

			return true;
		}
		return false;
	}
   
}

