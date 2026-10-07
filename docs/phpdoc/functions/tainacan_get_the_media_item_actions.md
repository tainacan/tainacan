# tainacan_get_the_media_item_actions

Return a slide actions wrapper around Expand, Download, and similar controls.

***

* Full name: `tainacan_get_the_media_item_actions`
* Defined in: `classes/theme-helper/template-tags.php`

## Parameters

| Parameter | Type      | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
|-----------|-----------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$args`   | **array** | {
    Optional. Array of arguments.
    @type string $expand_html    Expand control HTML, or empty. Default ''.
    @type string $download_html  Download control HTML, or empty. Default ''.
    @type int    $item_id        The item ID this slide belongs to. Default 0.
    @type int    $attachment_id WP attachment ID when the slide is an attachment (item document or extra file). Default 0.
    @type string $media_source   'document' or 'attachment'. Default ''.
    @type string $media_type     MIME type or media type string. Default ''.
} |

## Return Value

**string**

The actions wrapper HTML, or empty string if there is nothing to show.
