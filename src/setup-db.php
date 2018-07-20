<?php 

function tainacan_create_bd_process_db() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'tnc_bg_process';
	$charset_collate = $wpdb->get_charset_collate();
	$max_index_length = 191;
	
	$query = "CREATE TABLE IF NOT EXISTS $table_name (
	  ID bigint(20) unsigned NOT NULL auto_increment,
	  user_id bigint(20) unsigned NOT NULL default '0',
	  priority bigint(20) unsigned NOT NULL default 10,
	  queued_on datetime NOT NULL default '0000-00-00 00:00:00',
	  processed_last datetime NOT NULL default '0000-00-00 00:00:00',
	  data longtext NOT NULL,
	  action text NOT NULL,
	  name text NOT NULL,
	  done boolean not null default 0,
	  progress_label text,
	  progress_value int,
	  PRIMARY KEY (ID),
	  KEY user_id (user_id),
	  KEY action (action($max_index_length))
	) $charset_collate;\n";
	
	$wpdb->query($query);

	$column_exists = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'progress_label'"  );

    if(empty($column_exists)) {
		$wpdb->query("
        ALTER TABLE {$wpdb->prefix}tnc_bg_process
        ADD progress_label text,
        ADD progress_value int
        ");
	}

	$column_exists = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'name'"  );

    if(empty($column_exists)) {
		$wpdb->query("
        ALTER TABLE {$wpdb->prefix}tnc_bg_process
        ADD name text NOT NULL,
        ");
	}
    
	
}

 ?>