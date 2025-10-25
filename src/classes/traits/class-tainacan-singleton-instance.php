<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for Singleton pattern
 * @author Vinicius Nunus - L3P/Medialab
 *
 */
trait Singleton_Instance {

	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$parent = get_parent_class($this);
		if ( $parent && method_exists($parent, '__construct') ) {
			parent::__construct();
		}
		$this->init();
	}
}