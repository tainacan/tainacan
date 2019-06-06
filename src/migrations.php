<?php 
namespace Tainacan;



class Migrations {
	
	
	static function run_migrations() {
		
		$migrations = get_class_methods('Tainacan\Migrations');
		
		foreach ($migrations as $migration) {
			$option_name = '_migration_' . $migration;
			if ('run_migrations' == $migration || get_option($option_name)) {
				continue;
			}
			
			update_option($option_name, 1);
			
			call_user_func(array('Tainacan\Migrations', $migration));
			
		}
		
	}
	
	
	// Migrations methods below
	
	static function tainacan_create_bd_process_db() {
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
		  status ENUM('waiting','running','paused','cancelled','errored','finished','finished-errors'),
		  output longtext,
		  PRIMARY KEY (ID),
		  KEY user_id (user_id),
		  KEY action (action($max_index_length))
		) $charset_collate;\n";
		
		$wpdb->query($query);

	}
	
	/**
	 * We had some cases of tainacan upgrades from very old versions that missed some migrations...
	 * This migration make sure the table strucure is updated since the very first version
	 */
	static function assure_bg_process_database() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'tnc_bg_process';
		
		$column_exists = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'progress_label'"  );

	    if(empty($column_exists)) {
			$wpdb->query("
	        ALTER TABLE $table_name
	        ADD progress_label text,
	        ADD progress_value int
	        ");
		}

		$column_exists = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'name'"  );

	    if(empty($column_exists)) {
			$wpdb->query("
	        ALTER TABLE $table_name
	        ADD name text NOT NULL
	        ");
		}
		
		
		$column_exists = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'output'"  );

	    if(empty($column_exists)) {
			$wpdb->query("
	        ALTER TABLE $table_name
	        ADD output longtext
	        ");
		}
		
		$column_exists = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}tnc_bg_process' AND column_name = 'status'"  );

        if(empty($column_exists)) {
            $wpdb->query("
	        ALTER TABLE $table_name
	        ADD status ENUM('waiting','running','paused','cancelled','errored','finished','finished-errors')
	        ");
        }
		
	}

	static function init_capabilites() {
		$Tainacan_Capabilities = \Tainacan\Capabilities::get_instance();
		$Tainacan_Capabilities->init();
	}
	
	static function tainacan_migrate_post_type_field_to_metadatum(){
		global $wpdb;

	    $wpdb->update($wpdb->posts,
	        ['post_type' => 'tainacan-metadatum'],
	        ['post_type' => 'tainacan-field'],
	        '%s', '%s');
	      
	    $wpdb->update($wpdb->postmeta,
	        ['meta_key' => 'default_displayed_metadata'],
	        ['meta_key' => 'default_displayed_fields'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_key' => 'metadata_order'],
	        ['meta_key' => 'fields_order'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_key' => 'metadatum'],
	        ['meta_key' => 'field'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_key' => 'metadata_type'],
	        ['meta_key' => 'field_type'],
	        '%s', '%s');
	    
	    $wpdb->update($wpdb->postmeta,
	        ['meta_key' => 'metadata_type_options'],
	        ['meta_key' => 'field_type_options'],
	        '%s', '%s');
			
		$wpdb->update($wpdb->postmeta,
	        ['meta_key' => 'metadata_type'],
	        ['meta_key' => 'metadatum_type'],
	        '%s', '%s');
	    
	    $wpdb->update($wpdb->postmeta,
	        ['meta_key' => 'metadata_type_options'],
	        ['meta_key' => 'metadatum_type_options'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Core_Description'],
	        ['meta_value' => 'Tainacan\Field_Types\Core_Description'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Core_Title'],
	        ['meta_value' => 'Tainacan\Field_Types\Core_Title'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Text'],
	        ['meta_value' => 'Tainacan\Field_Types\Text'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Textarea'],
	        ['meta_value' => 'Tainacan\Field_Types\Textarea'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Date'],
	        ['meta_value' => 'Tainacan\Field_Types\Date'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Numeric'],
	        ['meta_value' => 'Tainacan\Field_Types\Numeric'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Selectbox'],
	        ['meta_value' => 'Tainacan\Field_Types\Selectbox'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Relationship'],
	        ['meta_value' => 'Tainacan\Field_Types\Relationship'],
	        '%s', '%s');


	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Taxonomy'],
	        ['meta_value' => 'Tainacan\Field_Types\Category'],
	        '%s', '%s');


	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Metadata_Types\Compound'],
	        ['meta_value' => 'Tainacan\Field_Types\Compound'],
	        '%s', '%s');
			
		
		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Core_Description'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Core_Description'],
		    '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Core_Title'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Core_Title'],
		    '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Text'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Text'],
		    '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Textarea'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Textarea'],
		    '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Date'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Date'],
		    '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Numeric'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Numeric'],
		    '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Selectbox'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Selectbox'],
		    '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Relationship'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Relationship'],
	        '%s', '%s');
	        
	    $wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Compound'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Compound'],
	        '%s', '%s');

		$wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Taxonomy'],
		    ['meta_value' => 'Tainacan\Metadatum_Types\Category'],
	        '%s', '%s');
	        
	    $wpdb->update($wpdb->postmeta,
		    ['meta_value' => 'Tainacan\Metadata_Types\Taxonomy'],
		    ['meta_value' => 'Tainacan\Metadata_Types\Category'],
		    '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Filter_Types\TaxonomyTaginput'],
	        ['meta_value' => 'Tainacan\Filter_Types\CategoryTaginput'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Filter_Types\TaxonomyCheckbox'],
	        ['meta_value' => 'Tainacan\Filter_Types\CategoryCheckbox'],
	        '%s', '%s');

	    $wpdb->update($wpdb->postmeta,
	        ['meta_value' => 'Tainacan\Filter_Types\TaxonomySelectbox'],
	        ['meta_value' => 'Tainacan\Filter_Types\CategorySelectbox'],
	        '%s', '%s');
	}

	static function update_tainacan_selectbox_to_tainacan_radio_and_tainacan_taginput(){
		global $wpdb;

		// update filter type
		$wpdb->update($wpdb->postmeta,
			['meta_value' => 'Tainacan\Filter_Types\TaxonomyTaginput'],
			['meta_value' => 'Tainacan\Filter_Types\TaxonomySelectbox'],
			'%s', '%s');

		// update input type
		$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = REPLACE(meta_value, 'tainacan-taxonomy-selectbox', 'tainacan-taxonomy-radio')");
	}
	
	static function update_core_metadata() {
		global $wpdb;
        $collections = \Tainacan\Repositories\Collections::get_instance()->fetch([], 'OBJECT');
        
        foreach ($collections as $collection) {

            // get title 
            $title_meta = $collection->get_core_title_metadatum();

            // delete metadata if exists
            $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key = %s", $title_meta->get_id() ));
            // create metadata
            $wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->postmeta 
                (post_id,meta_key,meta_value)
                SELECT ID, %s, post_title FROM $wpdb->posts WHERE post_type = %s
                ", $title_meta->get_id(), $collection->get_db_identifier() ));

            // get description
            $description_meta = $collection->get_core_description_metadatum();

            // delete metadata if exists
            $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key = %s", $description_meta->get_id() ));

            // create metadata
            $wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->postmeta 
                (post_id,meta_key,meta_value)
                SELECT ID, %s, post_content FROM $wpdb->posts WHERE post_type = %s
                ", $description_meta->get_id(), $collection->get_db_identifier() ));

        }
	}

	static function refresh_rewrite_rules() {
		// needed after we changed the Collections post type rewrite slug
		
		$option_name = '_migration_refresh_rewrite_rules_items';
		if (!get_option($option_name)) {
			return; // avoid running twice cause there is the same update right below this one
		}
		
		flush_rewrite_rules(false);
	}
	
	static function refresh_rewrite_rules_items() {
		// needed after we added the /items rewrite rule
		flush_rewrite_rules(false);
	}
	
	static function update_filters_definition() {
		global $wpdb;
		
		$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'metadatum_id' WHERE
			meta_key = 'metadatum' AND post_id IN (
				SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-filter'
			)");
		
	}
	
}



?>