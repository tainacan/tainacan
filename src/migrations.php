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

	static function update_repository_filters_meta() {
		global $wpdb;

		$wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = 'default' WHERE
			post_id IN (
				SELECT ID FROM $wpdb->posts WHERE post_type = 'tainacan-filter'
			) AND meta_key = 'collection_id' AND meta_value = 'filter_in_repository'"
		);

	}

	static function update_relationship_metadata_search_option() {
		global $wpdb;

		$q = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'metadata_type' AND meta_value = 'Tainacan\\\\Metadata_Types\\\\Relationship'";

		$ids = $wpdb->get_col($q);

		foreach ($ids as $id) {
			$meta = get_post_meta($id, 'metadata_type_options', true);
			if ( is_array($meta) && isset($meta['search']) && is_array($meta['search']) && isset($meta['search'][0]) && is_numeric($meta['search'][0]) ) {
				$meta['search'] = $meta['search'][0];
				update_post_meta($id, 'metadata_type_options', $meta);
			}
		}

	}

	static function replace_custom_interval_filters() {
		$tainacan_filters = \Tainacan\Repositories\Filters::get_instance();
		$filters = $tainacan_filters->fetch([
			'nopaging' => true,
			'filter_type' => 'Tainacan\Filter_Types\Custom_Interval',
			'post_status' => 'any'
		], 'OBJECT');

		foreach ($filters as $filter) {
			$meta = $filter->get_metadatum();
			#echo 'found filter:' . $filter->get_name(). "<br>";
			if ($meta instanceof \Tainacan\Entities\Metadatum) {
				$type = $meta->get_metadata_type();
				#echo 'found meta:' . $meta->get_name(). "<br>";
				#echo 'found meta:' . $meta->get_metadata_type(). "<br>";

				$newtype = false;
				if ( $type == 'Tainacan\Metadata_Types\Date' ) {
					$newtype = 'Tainacan\Filter_Types\Date_Interval';
				} elseif ( $type == 'Tainacan\Metadata_Types\Numeric' ) {
					$newtype = 'Tainacan\Filter_Types\Numeric_Interval';
				}

				#echo 'New type:' . $newtype. "<br>";

				if ($newtype) {
					$filter->set_filter_type($newtype);
					if ($filter->validate()) {
						#echo "INSERT\n\n";
						$tainacan_filters->insert($filter);
					}

				}

			}




		}

	}

	static function update_repository_rename_document_index_meta_key() {
		global $wpdb;
		$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = 'document_content_index' WHERE meta_key = '_document_content_index'");
	}

	static function refresh_rewrite_rules_attachment_pages() {
		// needed after we added the /tainacan_attachments url
		flush_rewrite_rules(false);
	}

	static function init_new_default_roles_and_migrate_users() {
		remove_role('tainacan-administrator');
		remove_role('tainacan-editor');
		remove_role('tainacan-author');

		\tainacan_roles()->init_default_roles();

		$q = new \WP_User_Query([
			'role' => 'tainacan-contributor'
		]);

		$contribs = $q->get_results();

		foreach ( $contribs as $contrib ) {
			$contrib->set_role('tainacan-author');
		}

		remove_role('tainacan-contributor');

	}

	static function create_control_metadata() {
		$items_repository = \Tainacan\Repositories\Items::get_instance();
		$collection_repository = \Tainacan\Repositories\Collections::get_instance();
		$metadata_repository = \Tainacan\Repositories\Metadata::get_instance();
		$collections = $collection_repository->fetch(['posts_per_page' => -1], 'OBJECT');
		$helper = \Tainacan\Metadata_Types\Control::get_helper();

		foreach ($collections as $collection) {
			$collection_id = $collection->get_id();
			$metadata_repository->register_control_metadata( $collection );
			$per_page = 50; $page = 1;
			$args = [
				'posts_per_page'=> $per_page,
				'paged' => $page,
				'post_status' => get_post_stati()
			];
			$collection_items = $items_repository->fetch($args, $collection_id, 'WP_Query');
			$total = $collection_items->found_posts;
			$last_page = ceil($total/$per_page);
			while ($page++ <= $last_page) {
				if ($collection_items->have_posts()) {
					while ( $collection_items->have_posts() ) {
						$collection_items->the_post();
						$item = new \Tainacan\Entities\Item($collection_items->post);
						if ( $item instanceof \Tainacan\Entities\Item) {
							$helper->update_control_metadatum($item);
						}
					}
				}
				$args['paged'] = $page;
				$collection_items = $items_repository->fetch($args, $collection_id, 'WP_Query');
			}
		}
	}

	static function insert_meta_default_metadata_section() {
		global $wpdb;
		// create metadata
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $wpdb->postmeta (post_id,meta_key,meta_value)
				SELECT ID,'metadata_section_id', %s FROM $wpdb->posts 
				WHERE post_type = %s AND ID NOT IN (
					SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s
				)"
				,\Tainacan\Entities\Metadata_Section::$default_section_slug
				,\Tainacan\Entities\Metadatum::$post_type
				,\Tainacan\Entities\Metadata_Section::$default_section_slug
			)
		);
	}

	static function update_default_collections_orderby() {
		global $wpdb;
		// update default order by "creation_date" to "date"
		$wpdb->query(
			"UPDATE $wpdb->postmeta SET meta_value = 'date'
			WHERE meta_key='default_orderby'
				AND meta_value='creation_date'
				AND post_id IN (SELECT ID from $wpdb->posts WHERE post_type='tainacan-collection')
			"
		);
	}

	static function update_plugin_url_metadata_type_slug_to_core() {
		global $wpdb;
		// Brings plugin metadata type url to core
		$wpdb->update($wpdb->postmeta,
	        	['meta_value' => 'Tainacan\Metadata_Types\URL'],
	        	['meta_value' => 'TAINACAN_URL_Plugin_Metadata_Type'],
	        	'%s', '%s'
		);

		if ( function_exists('deactivate_plugins') )
			\deactivate_plugins( 'tainacan-url-metadata-type/tainacan-metadata-type-url.php' );
	}

	static function alter_table_tnc_bg_process_add_uuid_refactor() {
		global $wpdb;
		// update default order by "creation_date" to "date"
		$table_name = $wpdb->prefix . 'tnc_bg_process';
		$database_name = DB_NAME;
		$column_exists = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = 'bg_uuid' AND table_schema = '$database_name'"  );

	    if(empty($column_exists)) {
			$wpdb->query("
	        ALTER TABLE $table_name
	        ADD bg_uuid text NULL
	        ");
		}
	}
}

?>
