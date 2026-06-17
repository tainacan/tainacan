# tainacan_get_item


Gets the Tainacan Item Entity object

If used inside the Loop of items, will get the Item object for the current post

***

* Full name: `tainacan_get_item`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter  | Type            | Description                                              |
|------------|-----------------|----------------------------------------------------------|
| `$post_id` | **int\|string** | (Optional) The post ID. Default is 0 (uses global $post) |

## Return Value

**\Tainacan\Entities\Item|null**

The Item object, or null if not found or not a valid item
