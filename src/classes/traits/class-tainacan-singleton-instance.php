<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for formatter texts
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
		$this->init();
	}
}