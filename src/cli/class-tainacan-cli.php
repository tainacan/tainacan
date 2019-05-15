<?php 

namespace Tainacan;

use WP_CLI;

class Cli {
	
	private static $instance = null;
	
	public static function get_instance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	private function __construct() {
		
		\WP_CLI::add_hook( 'after_wp_load', [$this, 'add_commands'] );
		
	}
	
	function add_commands() {
		
		\WP_CLI::add_command('tainacan garbage-collector', 'Tainacan\Cli_Garbage_Collector');
		
		
	}
	
	
	
}


 ?>