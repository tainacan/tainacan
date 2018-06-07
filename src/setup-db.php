<?php 

function tainacan_create_bd_process_db() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'tnc_bg_process';
	$charset_collate = $wpdb->get_charset_collate();
	$max_index_length = 191;
	
	$query = "CREATE TABLE IF NOT EXISTS $table_name (
	  ID bigint(20) unsigned NOT NULL auto_increment,
	  user_id bigint(20) unsigned NOT NULL default '0',
	  queued_on datetime NOT NULL default '0000-00-00 00:00:00',
	  processed_last datetime NOT NULL default '0000-00-00 00:00:00',
	  data longtext NOT NULL,
	  action text NOT NULL,
	  done boolean not null default 0,
	  PRIMARY KEY (ID),
	  KEY user_id (user_id),
	  KEY action (action($max_index_length))
	) $charset_collate;\n";
	
	$wpdb->query($query);
	
}

 ?>