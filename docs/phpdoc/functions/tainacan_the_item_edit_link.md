# tainacan_the_item_edit_link


Displays the link to the edit page of an item, if current user have permission

Can be used outside The Lopp if an ID is provided.

The same as edit_post_link() (@see https://developer.wordpress.org/reference/functions/edit_post_link/) but for
Tainacan Items

***

* Full name: `tainacan_the_item_edit_link`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter | Type              | Description                                                     |
|-----------|-------------------|-----------------------------------------------------------------|
| `$text`   | **string**        | (optional) Anchor text. If null, default is 'Edit this item'.   |
| `$before` | **string**        | (optional) Display before edit link                             |
| `$after`  | **mixed**         |                                                                 |
| `$id`     | **int\|\WP_Post** | (optional) Post ID or post object. Default is the global $post. |
| `$class`  | **string**        | (optional) Add custom class to link                             |

## Return Value

**mixed**
