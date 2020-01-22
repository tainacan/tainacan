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
		\WP_CLI::add_command('tainacan move-attachments-to-items-folder', 'Tainacan\Cli_Move_Attachments');
    \WP_CLI::add_command('tainacan collection', 'Tainacan\Cli_Collection');
    \WP_CLI::add_command('tainacan index-content', 'Tainacan\Cli_Document');
	}
	
	
	
}


 ?>