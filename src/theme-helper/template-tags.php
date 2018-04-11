<?php 

namespace Tainacan;
use Tainacan\Entities;

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
function get_tainacan_the_metadata($field = null, $hide_empty = true) {
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