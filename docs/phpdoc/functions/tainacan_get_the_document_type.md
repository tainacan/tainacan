# tainacan_get_the_document_type

To be used inside The Loop

Return the item document type (e.g., 'attachment', 'url', 'text').

***

* Full name: `tainacan_get_the_document_type`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter  | Type            | Description                                         |
|------------|-----------------|-----------------------------------------------------|
| `$item_id` | **int\|string** | (Optional) The item ID. Default is the global $post |

## Return Value

**string**

The document type, or empty string if item is not found
