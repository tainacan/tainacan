# Rich text editor

Tainacan uses the standard textarea by default. Administrators can allow the rich text editor in **Settings → Search and performance → Rich text editor**.

Host managers can lock this choice by defining the following constant in `wp-config.php`, before the line that loads WordPress:

```php
define( 'TAINACAN_ALLOW_RICH_TEXT_EDITOR', true );
```

When the setting is enabled, or the constant is defined as the boolean `true`, the following description fields load the rich text editor:

- Collection description
- Term description
- Taxonomy description
- Metadata section description
- Metadata description
- Filter description
- Item text-document content

It also adds a **Use rich text editor** switch to the metadata-type options for Core Description metadata.
Textarea metadata fields remain plain text.

The switch defaults to `no`, so enabling the setting or constant does not change existing metadata fields. Set it to `yes` for each metadata that should display the rich text editor in item editing forms. It appears in the relevant metadata-type options section.

When the setting is disabled, or the constant has any value other than boolean `true`, Tainacan does not load the rich text editor, hides this per-metadata switch, and uses the original textarea even when a metadata was previously configured to use rich text. Its saved setting is retained, ready to be used again when rich text is enabled.

Descriptions and Core Description metadata retain WordPress-safe formatting, including links, even while the editor is disabled or not selected for a metadata field. Other text inputs, such as titles and names, remove links when saved.

When `TAINACAN_ALLOW_RICH_TEXT_EDITOR` is defined, it overrides the saved setting and disables its control in the Tainacan settings page. This lets host managers keep the global choice over administrator preferences. Reload the Tainacan admin page after changing the setting or constant.

## Core Description in item editing

Core Description is the item's main description. It uses a textarea by default. To edit it with TinyMCE, enable the global rich text editor setting and turn on **Use rich text editor** in that Core Description metadatum's type options. The option belongs to the metadatum definition; the saved content state belongs to each item.

An existing item description without rich text state is treated as plain text. The first time it opens in TinyMCE, Tainacan prepares its paragraphs and links in memory so that line breaks remain visible. Opening or canceling the editor does not change the stored description. Once the item is saved through TinyMCE, Tainacan stores the resulting HTML and displays it without applying paragraph or link conversion a second time.

If the global or metadatum option is turned off, the textarea shows the stored value, including any HTML tags. Saving through the textarea treats the new value as plain text for future rich text editing. Changing either option without saving the item does not change its saved content state. A direct update of the item's description outside the Core Description editor also treats the replacement as plain text. Updating another item property does not change the description state. Duplicating an item copies the state when it copies the description unchanged.

Tainacan stores this state as the private item post meta `_tainacan_core_description_saved_with_rich_text_editor`. A missing key means the description needs preparation; `'yes'` means the last Core Description save used TinyMCE. The state is never inferred from HTML tags because plain textarea values may contain HTML.

### REST API for item editors

The Core Description entry in `GET /tainacan/v2/item/{item_id}/metadata?context=edit` and `GET /tainacan/v2/item/{item_id}/metadata/{metadatum_id}?context=edit` includes two additional fields for users who can edit the item:

- `saved_with_rich_text_editor`: whether this item's description was last saved through TinyMCE.
- `value_for_rich_text_editor`: the value to load into TinyMCE. Legacy plain text is prepared without being saved; previously saved rich HTML is returned unchanged.

`value` remains the stored, unformatted value for editing and integrations. `value_as_html` remains the display representation. The two additional fields are omitted from public view responses and from other metadata types.

When updating the Core Description through `PUT /tainacan/v2/item/{item_id}/metadata/{metadatum_id}`, send the boolean `edited_with_rich_text_editor` alongside `values`: use `true` for HTML produced by TinyMCE and `false` for a textarea save. Requests that omit the parameter continue to work and are treated as plain text saves. Tainacan rejects `true` if the editor has been disabled since the form loaded; reload the item before retrying. The server validates the mode and does not accept the read-only saved-state field as a write parameter.
