<?php 

use \Tainacan\Entities;
use \Tainacan\Repositories;

/**
 * To be used inside The Loop
 * 
 * Return the item metadata as a HTML string to be used as output.
 *
 * Each metadata is a label with the metadatum name and the value.
 *
 * If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in 'metadata' parameter, it returns only one metadata, otherwise
 * it returns all metadata
 * 
 * @param array|string $args {
	 *     Optional. Array or string of arguments.
	 * 
	 * 	   @type mixed		 $metadata					Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata
	 * 
	 *     @type array		 $metadata__in				Array of metadata IDs or Slugs to be retrieved. Default none
	 * 
	 *     @type array		 $metadata__not_in			Array of metadata IDs (slugs not accepted) to excluded. Default none
	 * 
	 *     @type bool		 $exclude_title				Exclude the Core Title Metadata from result. Default false
	 * 
	 *     @type bool		 $exclude_description		Exclude the Core Description Metadata from result. Default false
	 * 
	 *     @type bool		 $exclude_core				Exclude Core Metadata (title and description) from result. Default false
	 * 
	 *     @type bool        $hide_empty                Wether to hide or not metadata the item has no value to
	 *                                                  Default: true
	 *     @type string      $before_title              String to be added before each metadata title
	 *                                                  Default '<h3>'
	 *     @type string      $after_title               String to be added after each metadata title
	 *                                                  Default '</h3>'
	 *     @type string      $before_value              String to be added before each metadata value
	 *                                                  Default '<p>'
	 *     @type string      $after_value               String to be added after each metadata value
	 *                                                  Default '</p>'
	 * }
 * @return string        The HTML output
 */
function tainacan_get_the_metadata($args = array()) {
	
	$item = tainacan_get_item();

	if ($item instanceof \Tainacan\Entities\Item) {
		return $item->get_metadata_as_html($args);
	}

	return '';
	
}

function tainacan_the_metadata($args = array()) {
	echo tainacan_get_the_metadata($args);
}

/**
 * To be used inside The Loop
 * 
 * Return the item document as a HTML string to be used as output.
 *
 * @param  int|string|Tainacan\Entities\Metadatum $metadatum Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata
 * @param bool $hide_empty Wether to hide or not metadata the item has no value to
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
 * Outputs the div used by Vue to render the faceted search
 */
function tainacan_the_faceted_search() {

	$props = ' ';
	
	// if in a collection page
	$collection_id = tainacan_get_collection_id();
	if ($collection_id) {
		$props .= 'collection-id="' . $collection_id . '" ';
		$collection = new  \Tainacan\Entities\Collection($collection_id);
		$props .= 'default-view-mode="' . $collection->get_default_view_mode() . '" ';
		$props .= 'enabled-view-modes="' . implode(',', $collection->get_enabled_view_modes()) . '" ';
	}

	echo '<div id="tainacan-items-page" ', $props, '></div>';

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

/**
 * @see \Tainacan\Theme_Helper->register_view_mode()
 */
function tainacan_register_view_mode($slug, $args = []) {
	\Tainacan\Theme_Helper::get_instance()->register_view_mode($slug, $args);
}

/**
 * Gets the Tainacan Item Entity object
 * 
 * If used inside the Loop of items, will get the Item object for the current post
 */
function tainacan_get_item($post_id = 0) {
	$post = get_post( $post_id );

	$theme_helper = \Tainacan\Theme_Helper::get_instance();
	
	if (!$theme_helper->is_post_an_item($post))
		return null;
	
	$item = new Entities\Item($post);

	return $item;

}

/**
 * To be used inside The Loop of a faceted serach view mode template.
 * 
 * Returns true or false indicating wether a certain property or metadata is
 * selected to be displayed
 * 
 * @param string|integer The property to be checked. If a string is passed, it will check against
 * 	one of the native property of the item, such as title, description and creation_date. 
 *  If an integer is passed, it will check against the IDs of the metadata.
 * 
 * @return bool
 */
function tainacan_current_view_displays($property) {
	global $view_mode_displayed_metadata;

	// Core metadata appear in fetch_only as metadata
	if ($property == 'title' || $property == 'description') {
		$item = tainacan_get_item();
		$core_getter_method = "get_core_{$property}_metadatum";
        $property = $item->get_collection()->$core_getter_method()->get_id();
	}

	if (is_string($property)) {
		return in_array($property, $view_mode_displayed_metadata);
	} elseif (is_integer($property) && array_key_exists('meta', $view_mode_displayed_metadata)) {
		return in_array($property, $view_mode_displayed_metadata['meta']);
	}
	return false;
}