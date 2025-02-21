<?php 

namespace Tainacan;

use WP_CLI;

class Cli {
	use \Tainacan\Traits\Singleton_Instance;
	
	private function init() {
		\WP_CLI::add_hook( 'after_wp_load', [$this, 'add_commands'] );
	}
	
	function add_commands() {
		\WP_CLI::add_command('tainacan garbage-collector', 'Tainacan\Cli_Garbage_Collector');
		\WP_CLI::add_command('tainacan move-attachments-to-items-folder', 'Tainacan\Cli_Move_Attachments');
		\WP_CLI::add_command('tainacan collection', 'Tainacan\Cli_Collection');
		\WP_CLI::add_command('tainacan index-content', 'Tainacan\Cli_Document');
		\WP_CLI::add_command('tainacan control-metadata', 'Tainacan\Cli_Control_Metadata');
	}
	
	
	
}


 ?>