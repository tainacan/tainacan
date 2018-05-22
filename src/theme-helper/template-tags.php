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
	
	if (!$theme_helper->is_post_an_item($post))
		return;
	
	$item = new Entities\Item($post);
	
	return $item->get_metadata_as_html($field, $hide_empty);
	
}

function tainacan_the_metadata($field = null, $hide_empty = true) {
	echo tainacan_get_the_metadata($field, $hide_empty);
}

/**
 * To be used inside The Loop
 * 
 * Return the item document as a HTML string to be used as output.
 *
 * @param  int|string|Tainacan\Entities\Field $field Field object, ID or slug to retrieve only one field. empty returns all fields
 * @param bool $hide_empty Wether to hide or not fields the item has no value to
 * @return string        The HTML output
 */
function tainacan_get_the_document() {
	$post = get_post();
	$theme_helper = \Tainacan\Theme_Helper::get_instance();
	
	if (!$theme_helper->is_post_an_item($post))
		return;
	
	$item = new Entities\Item($post);
	
	return $item->get_document_html();
	
}

function tainacan_the_document() {
	echo tainacan_get_the_document();
}

/**
 * When visiting a collection archive or single, returns the current collection id
 *
 * @uses get_post_type() WordPress function, which looks for the global $wp_query variable
 */
function tainacan_get_collection_id() {
	if ( is_post_type_archive() || is_single() ) {
		return Repositories\Collections::get_instance()->get_id_by_db_identifier(get_post_type());
	} elseif ( false !== \Tainacan\Theme_Helper::get_instance()->visiting_collection_cover ) {
		return \Tainacan\Theme_Helper::get_instance()->visiting_collection_cover;
	}
	return false;
}

/**
 * When visiting a collection archive or single, returns the current collection object
 *
 * @uses tainacan_get_collection_id() 
 * @return \Tainacan\Entities\Collection | false 
 */
function tainacan_get_collection() {
	$collection_id = tainacan_get_collection_id();
	if ( $collection_id ) {
		$TainacanCollections = Repositories\Collections::get_instance();
		$collection = $TainacanCollections->fetch($collection_id);
		if ( $collection instanceof Entities\Collection ) {
			return $collection;
		}
		return false;
	}
}

/**
 * When visiting a collection archive or single, returns the collection name
 *
 * @return string
 */
function tainacan_get_the_collection_name() {
	$collection = tainacan_get_collection();
	$name = '';
	if ( $collection ) {
		$name = $collection->get_name();
	}
	return $name;
}

/**
 * When visiting a collection archive or single, prints the collection name
 *
 * @return void
 */
function tainacan_the_collection_name() {
	echo tainacan_get_the_collection_name();
}

/**
 * When visiting a collection archive or single, returns the collection description
 *
 * @return string
 */
function tainacan_get_the_collection_description() {
	$collection = tainacan_get_collection();
	$description = '';
	if ( $collection ) {
		$description = $collection->get_description();
	}
	return $description;
}

/**
 * When visiting a collection archive or single, prints the collection description
 *
 * @return void
 */
function tainacan_the_collection_description() {
	echo tainacan_get_the_collection_description();
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