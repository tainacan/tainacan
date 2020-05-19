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
	 *     @type string      $before                    String to be added before each metadata block
	 *                                                  Default '<div class="metadata-type-$type">' where $type is the metadata type slug
	 *     @type string      $after		                String to be added after each metadata block
	 *                                                  Default '</div>'
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
 * @return string        The HTML output
 */
function tainacan_get_the_document() {
	$item = tainacan_get_item();

	if (!$item)
		return;

	return apply_filters('tainacan-get-the-document', $item->get_document_as_html(), $item);
}

function tainacan_the_item_document_download_link() {
	$item = tainacan_get_item();

	if (!$item)
		return;

	$link = $item->get_document_download_url();

	if (!$link || $item->get_document_type() == 'text')
		return;

	return '<a name="' . __('Download the item document', 'tainacan') . '" download="'. $link . '" href="' . $link . '">' . __('Download', 'tainacan') . '</a>';
}


function tainacan_the_item_attachment_download_link($attachment_id) {

	if ( !$attachment_id || !wp_get_attachment_url($attachment_id) )
		return;

	$link = wp_get_attachment_url($attachment_id);

	return '<a name="' . __('Download the item attachment', 'tainacan') . '" download="'. $link . '" href="' . $link . '">' . __('Download', 'tainacan') . '</a>';
}

function tainacan_the_document() {
	echo tainacan_get_the_document();
}

/**
 * Return HTML display-ready version of an attachment
 */
function tainacan_get_single_attachment_as_html($attachment_id) {

	$item = tainacan_get_item();

	if (!$attachment_id) {
		return '';
	}

	echo $item->get_attachment_as_html($attachment_id);
}

/**
 * To be used inside The Loop
 *
 * Check whether the current item has a document or not
 *
 * @return bool True if item has document, false if it does not
 */
function tainacan_has_document() {

	$document = tainacan_get_the_document();

	return ! empty($document);

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
	return apply_filters('tainacan-get-collection-name', $name, $collection);
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
	return apply_filters('tainacan-get-collection-description', $description, $collection);
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
 * When visiting a collection archive or single, returns the collection url link
 *
 * @return string
 */
function tainacan_get_the_collection_url() {
	$collection = tainacan_get_collection();
	$url = '';
	
	if ( $collection ) {
		$url = $collection->get_url();
	}
	return apply_filters('tainacan-get-collection-url', $url, $collection);
}					


/**
 * When visiting a collection archive or single, prints the collection url link
 *
 * @return void
 */
function tainacan_the_collection_url() {
	echo tainacan_get_the_collection_url();
}


/**
 * get related to view modes
 *
 * @return array ['default_view_mode'=> [], '$enabled_view_modes'=> [] ]
 */
function tainacan_get_the_view_modes() {
	$default_view_mode = apply_filters( 'tainacan-default-view-mode-for-themes', 'masonry' );
	$registered_view_modes = \Tainacan\Theme_Helper::get_instance()->get_registered_view_modes();
	$registered_view_modes_slugs = [];
	foreach ($registered_view_modes as $key => $value) {
		array_push($registered_view_modes_slugs, $key);
	}
	$enabled_view_modes = apply_filters( 'tainacan-enabled-view-modes-for-themes', $registered_view_modes_slugs );
	return [
		'default_view_mode' => $default_view_mode,
		'enabled_view_modes' => $enabled_view_modes,
		'registered_view_modes' => $registered_view_modes
	];
}


/**
 * Outputs the div used by Vue to render the Items List with a powerful faceted search
 *
 * The items list bellong to a collection, to the whole repository or a taxonomy term, according to where
 * it is used on the loop
 * 
 * The following params all optional for customizing the rendered vue component
 *
 * @param array $args {
	 *     Optional. Array of arguments.
	 *
	 * 	   @type bool 	$hide_filters								Completely hide filter sidebar or modal
	 * 	   @type bool 	$hide_hide_filters_button					Hides the button resonsible for collpasing filters sidebar on desktop
	 * 	   @type bool 	$hide_search								Hides the complete search bar, including advanced search link
	 * 	   @type bool 	$hide_advanced_search						Hides only the advanced search link
	 *	   @type bool	$hide_displayed_metadata_dropdown			Hides the "Displayed metadata" dropdown even if the current view modes allows it	
	 * 	   @type bool	$hide_sorting_area							Completely hides all sorting controls	
	 * 	   @type bool 	$hide_sort_by_button						Hides the button where user can select the metadata to sort by items (keeps the sort direction)
	 *	   @type bool	$hide_exposers_button						Hides the "View as..." button, a.k.a. Exposers modal
	 * 	   @type bool 	$hide_items_per_page_button					Hides the button for selecting amount of items loaded per page
	 * 	   @type bool 	$hide_go_to_page_button						Hides the button for skiping to a specific page
	 *	   @type bool	$hide_pagination_area						Completely hides pagination controls
	 *	   @type int	$default_items_per_page						Default number of items per page loaded
	 * 	   @type bool 	$show_filters_button_inside_search_control	Display the "hide filters" button inside of the search control instead of floating
	 * 	   @type bool 	$start_with_filters_hidden					Loads the filters list hidden from start
	 * 	   @type bool 	$filters_as_modal							Display the filters as a modal instead of a collapsable region on desktop
	 * 	   @type bool 	$show_inline_view_mode_options				Display view modes as inline icon buttons instead of the dropdown
	 * 	   @type bool 	$show_fullscreen_with_view_modes			Lists fullscreen viewmodes alongside with other view modes istead of separatelly
	 * 	   @type string $default_view_mode							The default view mode
	 * 	   @type bool	$is_forced_view_mode						Ignores user prefs to always render the choosen default view mode
	 *	   @type string[] $enabled_view_modes						The list os enable view modes to display
 * @return string  The HTML div to be used for rendering the items list vue component
 */
function tainacan_the_faceted_search($args = array()) {

	$props = ' ';
	
	// Loads info related to view modes
	$view_modes = tainacan_get_the_view_modes();
	$default_view_mode = $view_modes['default_view_mode'];
	$enabled_view_modes = $view_modes['enabled_view_modes'];

	if( isset($args['default_view_mode']) ) {
		$default_view_mode = $args['default_view_mode'];
		unset($args['default_view_mode']);
	}

	if( isset($args['enabled_view_modes']) ) {
		$enabled_view_modes = $args['enabled_view_modes'];
		if ( !in_array($default_view_mode, $enabled_view_modes) ) {
			$default_view_mode = $enabled_view_modes[0];
		}
		unset($args['enabled_view_modes']);
	}

	// If in a collection page
	$collection_id = tainacan_get_collection_id();
	if ($collection_id) {

		$props .= 'collection-id="' . $collection_id . '" ';
		$collection = new  \Tainacan\Entities\Collection($collection_id);
		$default_view_mode = $collection->get_default_view_mode();
		$enabled_view_modes = $collection->get_enabled_view_modes();
	}

	// If in a tainacan taxonomy
	$term = tainacan_get_term();
	if ($term) {
		$props .= 'term-id="' . $term->term_id . '" ';
		$props .= 'taxonomy="' . $term->taxonomy . '" ';
	}

	$props .= 'default-view-mode="' . $default_view_mode . '" ';
	$props .= 'enabled-view-modes="' . implode(',', $enabled_view_modes) . '" ';

	// Passes arguments to custom props
	foreach ($args as $key => $value) {
		if ($value == true || $value == 'true') {
			$props .= str_replace('_', '-', $key) . '="' . $value . '" ';
		}
	}

	echo "<main id='tainacan-items-page' $props ></main>";

}

/**
 * When visiting a term archive, returns the current term object if it belongs to a Tainacan taxonomy
 *
 * @return false|\WP_Term
 */
function tainacan_get_term() {
	if ( is_tax() ) {
		$term = get_queried_object();
		$theme_helper = \Tainacan\Theme_Helper::get_instance();
		if ( isset($term->taxonomy) && $theme_helper->is_taxonomy_a_tainacan_tax($term->taxonomy) ) {
			return $term;
		}
	}
	return false;
}

/**
 * When visiting a taxonomy archive, returns the term name
 *
 * @return string
 */
function tainacan_get_the_term_name() {
	$term = tainacan_get_term();
	$name = '';
	if ( $term ) {
		$name = $term->name;
	}
	return apply_filters('tainacan-get-term-name', $name, $term);
}

/**
 * When visiting a taxonomy archive, prints the term name
 *
 * @return void
 */
function tainacan_the_term_name() {
	echo tainacan_get_the_term_name();
}

/**
 * When visiting a taxonomy archive, returns the term description
 *
 * @return string
 */
function tainacan_get_the_term_description() {
	$term = tainacan_get_term();
	$description = '';
	if ( $term ) {
		$description = $term->description;
	}
	return apply_filters('tainacan-get-term-description', $description, $term);
}

/**
 * When visiting a taxonomy archive, prints the term description
 *
 * @return void
 */
function tainacan_the_term_description() {
	echo tainacan_get_the_term_description();
}

/**
 * To be used inside The Loop
 *
 * Return the list of attachments of the current item (by default, excluding the document and the thumbnail)
 *
 * @param string|array IDs of attachments to be excluded (by default this function already excludes the document and the thumbnail)
 * @return array      Array of WP_Post objects. @see https://developer.wordpress.org/reference/functions/get_children/
 */
function tainacan_get_the_attachments($exclude = null) {
	$item = tainacan_get_item();

	if (!$item)
		return [];

	return apply_filters('tainacan-get-the-attachments', $item->get_attachments($exclude), $item);

}

function tainacan_get_attachment_html_url($attachment_id) {
	return \Tainacan\Media::get_instance()->get_attachment_html_url($attachment_id);
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

	if (!$post) {
		return null;
	}

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

/**
 *
 * Displays the link to the edit page of an item, if current user have permission
 *
 * Can be used outside The Lopp if an ID is provided.
 *
 * The same as edit_post_link() (@see https://developer.wordpress.org/reference/functions/edit_post_link/) but for
 * Tainacan Items
 *
 * @param string $text 	(optional) Anchor text. If null, default is 'Edit this item'.
 * @param string $before 	(optional) Display before edit link
 * @param string $afer 	(optional) Display after edit link
 * @param int|WP_Post $id 	(optional) Post ID or post object. Default is the global $post.
 * @param string $class 	(optional) Add custom class to link
 *
 */
function tainacan_the_item_edit_link( $text = null, $before = '', $after = '', $id = 0, $class = 'post-edit-link' ) {
	if ( ! $item = tainacan_get_item( $id ) ) {
		return;
	}

	if ( ! $item->can_edit() || ! $url = $item->get_edit_url() ) {
		return;
	}

	if ( null === $text ) {
		$text = __( 'Edit this item', 'tainacan' );
	}

	$link = '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . $text . '</a>';

	echo $before . $link . $after;
}

/**
 * Gets the initials from a name.
 *
 * By default, returns 2 uppercase letters representing the name. The first letter from the first name and the first letter from the last.
 *
 * @param string $string The name to extract the initials from
 * @param bool $one whether to return only the first letter, instead of two
 *
 * @return string
 */
function tainacan_get_initials($string, $one = false) {

	if (empty($string)) {
		return '';
	}

	$string = remove_accents($string);

	if (strlen($string) == 1) {
		return strtoupper($string);
	}

	$first = strtoupper(substr($string,0,1));

	if ($one) {
		return $first;
	}

	$words=explode(" ",$string);

	if (sizeof($words) < 2) {
		$second = $string[1];
	} else {
		$second = $words[ sizeof($words) - 1 ][0];
	}

	$result = strtoupper($first . $second);
	return apply_filters('tainacan-get-initials', $result, $string, $one);
}
