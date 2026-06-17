# tainacan_the_item_document_download_link

To be used inside The Loop

Return the item document download link as HTML.

Only returns a link for attachment-type documents. Returns empty string for text or URL documents.

***

* Full name: `tainacan_the_item_document_download_link`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter  | Type            | Description                                         |
|------------|-----------------|-----------------------------------------------------|
| `$item_id` | **int\|string** | (Optional) The item ID. Default is the global $post |

## Return Value

**string**

The HTML download link, or empty string if item is not found, has no document, or document is not downloadable
