# tainacan_get_the_attachments


To be used inside The Loop

Return the list of attachments of the current item (by default, excluding the document and the thumbnail)

***

* Full name: `tainacan_get_the_attachments`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter  | Type            | Description                                                                |
|------------|-----------------|----------------------------------------------------------------------------|
| `$exclude` | **mixed**       |                                                                            |
| `$item_id` | **int\|string** | (Optional) The item ID to retrive attachments. Default is the global $post |

## Return Value

**array**

Array of WP_Post objects. @see https://developer.wordpress.org/reference/functions/get_children/
