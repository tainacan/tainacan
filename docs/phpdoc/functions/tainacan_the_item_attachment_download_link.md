# tainacan_the_item_attachment_download_link

Return the item attachment download link as HTML.

Unlike typical WordPress `the_*` helpers, this function returns the HTML instead of echoing it.
The original implementation returned a string and themes concatenate that value, so echoing
here would break existing templates. Prefer tainacan_get_the_item_attachment_download_link() in new code.

***

* Full name: `tainacan_the_item_attachment_download_link`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter        | Type    | Description       |
|------------------|---------|-------------------|
| `$attachment_id` | **int** | The attachment ID |

## Return Value

**string**

The HTML download link, or empty string if attachment is not found or has no URL
