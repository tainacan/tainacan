# tainacan_get_the_item_attachment_download_link

Return the item attachment download link as HTML.

The HTML includes a `.tainacan-item-file-download` wrapper around the link so themes can style the control.

***

* Full name: `tainacan_get_the_item_attachment_download_link`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter        | Type    | Description       |
|------------------|---------|-------------------|
| `$attachment_id` | **int** | The attachment ID |

## Return Value

**string**

The HTML download link, or empty string if attachment is not found or has no URL
