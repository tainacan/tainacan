# tainacan_wp_kses

Custom wp_kses function for Tainacan content.

Sanitizes content using WordPress kses with Tainacan-specific allowed HTML tags.
Extends the default 'post' context to include iframe elements for embedded content.

***

* **Warning:** this function is **deprecated**. This means that this function will likely be removed in a future version.* Full name: `tainacan_wp_kses`
* Defined in: `classes/tainacan-utils.php`

## Parameters

| Parameter  | Type       | Description                                          |
|------------|------------|------------------------------------------------------|
| `$content` | **string** | The content to sanitize.                             |
| `$context` | **string** | The kses context to use. Default 'tainacan_content'. |

## Return Value

**string**

Sanitized content.
