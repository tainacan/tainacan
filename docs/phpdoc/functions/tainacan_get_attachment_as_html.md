# tainacan_get_attachment_as_html

Return HTML display-ready version of an attachment

***

* Full name: `tainacan_get_attachment_as_html`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter        | Type            | Description                                         |
|------------------|-----------------|-----------------------------------------------------|
| `$attachment_id` | **int**         | The attachment ID                                   |
| `$item_id`       | **int\|string** | (Optional) The item ID. Default is the global $post |
| `$img_size`      | **string**      | (Optional) The image size. Default is 'large'       |

## Return Value

**string**

The HTML output, or empty string if attachment ID is invalid or item is not found
