<?php

function tainacan_migrate_post_type_field_to_metadatum(){
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


if (!get_option('tainacan_fix_core_indexes')) {
    
    add_action('init', function() {
        global $wpdb;
        update_option('tainacan_fix_core_indexes', true);
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

    });
    

}


?>