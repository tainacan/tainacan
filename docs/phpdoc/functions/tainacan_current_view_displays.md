# tainacan_current_view_displays

To be used inside The Loop of a faceted search view mode template.

Returns true or false indicating whether a certain property or metadata is
selected to be displayed

***

* Full name: `tainacan_current_view_displays`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter   | Type            | Description                                                                                                                                                                                                                            |
|-------------|-----------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$property` | **string\|int** | The property to be checked. If a string is passed, it will check against
one of the native property of the item, such as title, description and creation_date.
If an integer is passed, it will check against the IDs of the metadata. |
| `$item_id`  | **int\|string** | (Optional) The item ID. Default is the global $post                                                                                                                                                                                    |

## Return Value

**bool**
