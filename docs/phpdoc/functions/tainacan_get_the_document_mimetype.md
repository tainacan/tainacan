# tainacan_get_the_document_mimetype

To be used inside The Loop

Return the item document MIME type. For attachment documents this is the file MIME type
(e.g. 'image/jpeg', 'application/pdf'). For URL or text documents this is the document type itself.

***

* Full name: `tainacan_get_the_document_mimetype`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter  | Type            | Description                                         |
|------------|-----------------|-----------------------------------------------------|
| `$item_id` | **int\|string** | (Optional) The item ID. Default is the global $post |

## Return Value

**string**

The document MIME type or document type, or empty string if item is not found
