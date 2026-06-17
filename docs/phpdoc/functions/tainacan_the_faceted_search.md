# tainacan_the_faceted_search


Outputs the div used by Vue to render the Items List with a powerful faceted search

The items list bellong to a collection, to the whole repository or a taxonomy term, according to where
it is used on the loop, or to given params

The following params are all optional for customizing the rendered vue component

***

* Full name: `tainacan_the_faceted_search`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter | Type      | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
|-----------|-----------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`   | **array** | {
Optional. Array of arguments.
@type string $collection_id								Collection ID for a collection items list
@type string $term_id									Term ID for a taxonomy term items list
@type bool 	$hide_filters								Completely hide filter sidebar or modal
@type bool 	$hide_hide_filters_button					Hides the button resonsible for collpasing filters sidebar on desktop
@type bool 	$hide_search								Hides the complete search bar, including advanced search link
@type bool 	$hide_advanced_search						Hides only the advanced search link
@type bool	$hide_displayed_metadata_button			Hides the "Displayed metadata" dropdown even if the current view modes allows it
@type bool	$hide_sorting_area							Completely hides all sorting controls
@type bool 	$hide_sort_by_button						Hides the button where user can select the metadata to sort by items (keeps the sort direction)
@type bool 	$hide_items_thumbnail						Forces the thumbnail to be hiden on every listing. This setting also disables view modes that contain the 'requires-thumbnail' attr. By default is false or inherited from collection setting
@type bool	$hide_exposers_button						Hides the "View as..." button, a.k.a. Exposers modal
@type bool 	$hide_items_per_page_button					Hides the button for selecting amount of items loaded per page
@type bool 	$hide_go_to_page_button						Hides the button for skiping to a specific page
@type bool	$hide_pagination_area						Completely hides pagination controls
@type int	$default_items_per_page						Default number of items per page loaded
@type bool 	$show_filters_button_inside_search_control	Display the "hide filters" button inside of the search control instead of floating
@type bool 	$start_with_filters_hidden					Loads the filters list hidden from start
@type bool 	$filters_as_modal							Display the filters as a modal instead of a collapsible region on desktop
@type bool 	$show_inline_view_mode_options				Display view modes as inline icon buttons instead of the dropdown
@type bool 	$show_fullscreen_with_view_modes			Lists fullscreen viewmodes alongside with other view modes istead of separatelly
@type string $default_view_mode							The default view mode
@type bool	$is_forced_view_mode						Ignores user prefs to always render the choosen default view mode
@type string[] $enabled_view_modes						The list os enable view modes to display |

## Return Value

**string**

The HTML div to be used for rendering the items list vue component
