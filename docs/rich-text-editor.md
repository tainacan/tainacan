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

When the setting is disabled, or the constant has any value other than boolean `true`, Tainacan does not use the rich text editor for the fields controlled by this setting. It hides the Core Description switch and uses the original textarea even when Core Description was previously configured to use rich text. Its saved setting is retained, ready to be used again when rich text is enabled. The dedicated **Rich Text** metadata type is an exception: it always uses the rich text editor.

Non-title content retains WordPress-safe HTML, including links, even when the rich text editor is disabled. This includes Text and Textarea metadata values and item text-document content; plain inputs still display the stored HTML as text. Item titles, entity names used as titles (collections, taxonomies, metadata definitions, filters, and metadata sections), log titles, and taxonomy term names remove anchor tags while retaining their text. Existing type validation and removal of executable markup or unsafe link protocols still apply.

When `TAINACAN_ALLOW_RICH_TEXT_EDITOR` is defined, it overrides the saved setting and disables its control in the Tainacan settings page. This lets host managers keep the global choice over administrator preferences. Reload the Tainacan admin page after changing the setting or constant.

## Rich Text metadata type

Choose **Rich Text** when creating a metadatum to edit its item values with TinyMCE from the beginning. Unlike **Textarea**, this type always uses the rich text editor, even when the global editor setting or constant is disabled. There is no per-metadatum editor switch, conversion of existing text, or editor-state flag. Existing Textarea and Core Description metadata do not change type automatically.

Rich Text values are stored as WordPress-safe HTML in item metadata. Links, paragraphs, lists, and emphasis survive saving; executable markup and unsafe link protocols are removed by the server. `value` in the item metadata REST API contains the stored HTML, and `value_as_html` displays it without adding line breaks or links a second time. Existing item metadata create, read, and update routes are used without a new request parameter. Rich Text also supports multiple values and the normal metadata options, including a character limit based on visible text in the editor.

An editor containing only an empty paragraph is treated as empty for required-field validation and optional-value removal. Bulk editing and item forms use the same editor. Exporters and integrations that read `value` receive HTML and should strip tags if they need plain text.

## Bulk copying between Textarea and Rich Text

In a collection's item list, select the items to edit, open **Actions for the selection → Bulk edit selected items**, and choose the destination metadatum, **Copy value**, and the source metadatum. Each selected item receives its own source value. Copying replaces the destination value; it does not change the source or either metadata definition.

Textarea and Rich Text can be copied in both directions, independently of the global rich text editor setting:

- **Textarea → Rich Text:** the server creates links with WordPress `make_clickable()`, then creates paragraphs with `wpautop()`, before saving through the destination's normal validation and sanitization. Existing links are kept without nested anchors.
- **Rich Text → Textarea:** the server copies the stored value without paragraph or link conversion. Safe HTML remains in the Textarea value; copying does not turn it into plain text or enable a rich text editor on that field.
- **Same type → same type:** the existing copy behavior remains, without the new conversion.

For example, a Textarea value containing `First paragraph`, a blank line, and `https://example.org` becomes:

```html
<p>First paragraph</p>
<p><a href="https://example.org">https://example.org</a></p>
```

WordPress determines link attributes and paragraph formatting. Copying this HTML back to Textarea retains the safe tags and link rather than extracting their visible text. See [Conditional link sanitization for developers](#conditional-link-sanitization-for-developers) for the existing HTML policy.

A single value can be copied to a single or multiple destination; a multiple destination receives one entry for a single source value. Multiple sources can only be copied to multiple destinations, keeping the entry order and formatting each entry independently. The new cross-type copies apply to top-level metadata, including applicable inherited metadata. Text, Core Description, and children of compound metadata are not included in this new compatibility rule.

An empty source clears an optional destination. If the destination requires a value, validation rejects the empty copy and retains the previous saved value. The text `0` is a value, and visually empty Rich Text markup follows the existing empty-value normalization. Repeating the copy starts from the source again, so it does not accumulate formatting from the previous destination.

Bulk copy is asynchronous. Adding a criterion to the queue confirms that it was submitted; check the background process result for completion or validation and permission errors. Item permissions and the metadata's normal validation rules still apply when the worker runs.

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

## Conditional link sanitization for developers

`Repository::sanitize_value($content, $remove_links = false)` and `REST_Controller::sanitize_value($value, $remove_links = false)` use WordPress's allowed post HTML. Safe anchor tags are preserved by default. Pass `true` when the value represents a title or another field that must exclude anchors. The option removes the anchor tag, not its visible text; it does not strip all HTML or turn a plain input into a rich text editor.

Title persistence paths explicitly request link removal, including the Core Title metadata mirror. REST title/name filters and queries targeting Core Title or taxonomy term names apply the same policy to their criteria. Queries for other metadata preserve safe anchors so their criteria can match stored HTML. This change does not migrate previously saved values or restore links removed by earlier saves. Plain-text representations such as `value_as_string` and excerpts continue to strip HTML tags.
