<?php

/**
 * Retrieve the singleton Collections Repository instance
 * @return \Tainacan\Repositories\Collections The Tainacan Collections Repository
 */
function tainacan_collections() {
	return \Tainacan\Repositories\Collections::get_instance();
}

/**
 * Retrieve the singleton Filters Repository instance
 * @return \Tainacan\Repositories\Filters The Tainacan Filters Repository
 */
function tainacan_filters() {
	return \Tainacan\Repositories\Filters::get_instance();
}

/**
 * Retrieve the singleton Item_Metadata Repository instance
 * @return \Tainacan\Repositories\Item_Metadata The Tainacan Item_Metadata Repository
 */
function tainacan_item_metadata() {
	return \Tainacan\Repositories\Item_Metadata::get_instance();
}

/**
 * Retrieve the singleton Items Repository instance
 * @return \Tainacan\Repositories\Items The Tainacan Items Repository
 */
function tainacan_items() {
	return \Tainacan\Repositories\Items::get_instance();
}

/**
 * Retrieve the singleton Logs Repository instance
 * @return \Tainacan\Repositories\Logs The Tainacan Logs Repository
 */
function tainacan_logs() {
	return \Tainacan\Repositories\Logs::get_instance();
}

/**
 * Retrieve the singleton Metadata Repository instance
 * @return \Tainacan\Repositories\Metadata The Tainacan Metadata Repository
 */
function tainacan_metadata() {
	return \Tainacan\Repositories\Metadata::get_instance();
}

/**
 * Retrieve the singleton Taxonomies Repository instance
 * @return \Tainacan\Repositories\Taxonomies The Tainacan Taxonomies Repository
 */
function tainacan_taxonomies() {
	return \Tainacan\Repositories\Taxonomies::get_instance();
}

/**
 * Retrieve the singleton Terms Repository instance
 * @return \Tainacan\Repositories\Terms The Tainacan Terms Repository
 */
function tainacan_terms() {
	return \Tainacan\Repositories\Terms::get_instance();
}

/**
 * Retrieve the singleton Tainacan Roles instance
 * @return \Tainacan\Roles The Tainacan Roles class
 */
function tainacan_roles() {
	return \Tainacan\Roles::get_instance();
}
