# tainacan_the_terms_carousel


Displays a carousel of terms, the same of the Gutenberg block

***

* Full name: `tainacan_the_terms_carousel`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter | Type      | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
|-----------|-----------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`   | **array** | {
Optional. Array of arguments.
@type string  $taxonomy_id					The Taxonomy ID
@type array   $selected_terms				An array of term IDs to fetch terms from, if load strategy is 'selection' and an array of terms, if the load strategy is 'parent'
@type integer $max_terms_number				Maximum number of terms to be fetch
@type integer $max_terms_per_screen			Maximum columns of terms to be displayed on a row of the carousel
@type bool    $show_term_thumbnail			Show term thumbnail instead of a grid with items terms
@type string  $arrows_position				How the arrows will be positioned regarding the carousel ('around', 'left', 'right')
@type bool    $large_arrows					Should large arrows be displayed?
@type bool    $auto_play						Should the Caroulsel start automatically to slide?
@type integer $auto_play_speed				The time in s to translate to the next slide automatically
@type integer $space_between_terms			The space in px between each term in the carousel
@type integer $space_around_carousel			The space in px between around the carousel
@type bool    $loop_slides					Should slides loop when reached the end of the Carousel?
@type bool    $hide_name						Should the name of the terms be displayed?
@type string  $image_size					term image size. Defaults to 'tainacan-medium'
@type bool	 $variable_terms_width			Should the carousel terms be only as large as their natural width?
@type string  $tainacan_api_root				Path of the Tainacan api root (to make the terms request)
@type string  $tainacan_base_url				Path of the Tainacan base URL (to make the links to the terms)
@type string  $class_name					Extra class to add to the wrapper, besides the default wp-block-tainacan-carousel-terms-list |

## Return Value

**string**

The HTML div to be used for rendering the terms carousel vue component
