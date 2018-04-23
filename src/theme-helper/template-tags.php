<?php 

use \Tainacan\Entities;
use \Tainacan\Repositories;

/**
 * To be used inside The Loop
 * 
 * Return the item metadata as a HTML string to be used as output.
 *
 * Each metadata is a label with the field name and the value.
 *
 * If an ID, a slug or a Tainacan\Entities\Field object is passed, it returns only one metadata, otherwise
 * it returns all metadata
 * 
 * @param  int|string|Tainacan\Entities\Field $field Field object, ID or slug to retrieve only one field. empty returns all fields
 * @param bool $hide_empty Wether to hide or not fields the item has no value to
 * @return string        The HTML output
 */
function tainacan_get_the_metadata($field = null, $hide_empty = true) {
	$post = get_post();
	$theme_helper = \Tainacan\Theme_Helper::get_instance();
	
	if (!$theme_helper->is_post_an_item($post));
		return;
	
	$item = new Entities\Item($post);
	
	return $item->get_metadata_as_html($field, $hide_empty);
	
}

function tainacan_the_metadata($field = null, $hide_empty = true) {
	echo get_tainacan_the_metadata($field, $hide_empty);
}

/**
 * When visiting a collection archive or single, returns the current collection id
 *
 * @uses get_post_type() WordPress function, which looks for the global $wp_query variable
 */
function tainacan_get_collection_id() {
	if ( is_post_type_archive() || is_single() ) {
		return Repositories\Collections::get_instance()->get_id_by_db_identifier(get_post_type());
	}
	return false;
}

/**
 * When visiting a term archive, returns the current term object
 *
 * @return false|\WP_Term 
 */
function tainacan_get_term() {
	if ( is_tax() ) {
		return get_queried_object();
	}
	return false;
}