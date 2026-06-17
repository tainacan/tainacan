# wp_kses_tainacan


Custom wp_kses function for Tainacan content.

Sanitizes content using WordPress kses with Tainacan-specific allowed HTML tags.
Extends the default 'post' context to include iframe elements for embedded content.

***

* Full name: `wp_kses_tainacan`
* Defined in: `classes/tainacan-utils.php`

## Parameters

| Parameter  | Type       | Description                                          |
|------------|------------|------------------------------------------------------|
| `$content` | **string** | The content to sanitize.                             |
| `$context` | **string** | The kses context to use. Default 'tainacan_content'. |

## Return Value

**string**

Sanitized content.
