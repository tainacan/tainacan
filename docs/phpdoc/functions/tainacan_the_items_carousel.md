# tainacan_the_items_carousel


Displays a carousel of items, the same of the gutenberg block

***

* Full name: `tainacan_the_items_carousel`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter | Type      | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
|-----------|-----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`   | **array** | {
Optional. Array of arguments.
@type string  $collection_id					The Collection ID
@type string  $search_URL						A query string to fetch items from, if load strategy is 'search'
@type array   $selected_items					An array of item IDs to fetch items from, if load strategy is 'selection' and an array of items, if the load strategy is 'parent'
@type string  $load_strategy					Either 'search' or 'selection', to determine how items will be fetch
@type integer $max_items_number				Maximum number of items to be fetch
@type integer $max_tems_per_screen			Maximum columns of items to be displayed on a row of the carousel
@type string  $arrows_position				How the arrows will be positioned regarding the carousel ('around', 'left', 'right')
@type bool    $large_arrows					Should large arrows be displayed?
@type bool    $auto_play						Should the Caroulsel start automatically to slide?
@type integer $auto_play_speed				The time in s to translate to the next slide automatically
@type bool    $loop_slides					Should slides loop when reached the end of the Carousel?
@type bool    $hide_title						Should the title of the items be displayed?
@type bool	$variable_items_width			Should the carousel items be only as large as their natural width?
@type string  $image_size						Item image size. Defaults to 'tainacan-medium'
@type bool    $show_collection_header			Should it display a small version of the collection header?
@type bool    $show_collection_label			Should it displar a 'Collection' label before the collection name on the collection header?
@type string  $collection_background_color	Color of the collection header background
@type string  $collection_text_color			Color of the collection header text
@type string  $tainacan_api_root				Path of the Tainacan api root (to make the items request)
@type string  $tainacan_base_url				Path of the Tainacan base URL (to make the links to the items)
@type string  $class_name						Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-items-list |

## Return Value

**void**

The HTML div to be used for rendering the items carousel vue component
