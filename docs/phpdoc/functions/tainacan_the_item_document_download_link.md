# tainacan_the_item_document_download_link

To be used inside The Loop

Return the item document download link as HTML.

Only returns a link for attachment-type documents. Returns empty string for text or URL documents.

Unlike typical WordPress `the_*` helpers, this function returns the HTML instead of echoing it.
The original implementation returned a string and themes concatenate that value, so echoing
here would break existing templates. Prefer tainacan_get_the_item_document_download_link() in new code.

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
