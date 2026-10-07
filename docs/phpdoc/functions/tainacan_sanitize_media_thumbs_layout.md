# tainacan_sanitize_media_thumbs_layout

Sanitize a thumbnails layout slug for the media gallery.

Core layouts are 'carousel', 'grid' and 'list'. Other class-safe slugs are
kept so themes and plugins can add a layout, style
`.tainacan-media-thumbs--layout-{slug}`, and handle it in
tainacan_get_the_media_component(). Empty or invalid values fall back to
'carousel'.

***

* Full name: `tainacan_sanitize_media_thumbs_layout`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter | Type      | Description  |
|-----------|-----------|--------------|
| `$layout` | **mixed** | Layout name. |

## Return Value

**string**
