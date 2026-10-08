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

It also adds a **Use rich text editor** switch to the metadata-type options for Textarea and Core Description metadata.

The switch defaults to `no`, so enabling the setting or constant does not change existing metadata fields. Set it to `yes` for each metadata that should display the rich text editor in item editing forms. It appears in the relevant metadata-type options section.

When the setting is disabled, or the constant has any value other than boolean `true`, Tainacan does not use the rich text editor for the fields controlled by this setting. It hides those switches and uses the original textarea even when a metadatum was previously configured to use rich text. The saved option is retained, ready to be used again when rich text is enabled.

Non-title content retains WordPress-safe HTML, including links, even when the rich text editor is disabled. This includes Text and Textarea metadata values and item text-document content; plain inputs still display the stored HTML as text. Item titles, entity names used as titles (collections, taxonomies, metadata definitions, filters, and metadata sections), log titles, and taxonomy term names remove anchor tags while retaining their text. Existing type validation and removal of executable markup or unsafe link protocols still apply.

When `TAINACAN_ALLOW_RICH_TEXT_EDITOR` is defined, it overrides the saved setting and disables its control in the Tainacan settings page. This lets host managers keep the global choice over administrator preferences. Reload the Tainacan admin page after changing the setting or constant.

## Textarea and Core Description in item editing

Textarea and Core Description use a plain textarea by default. To edit either with TinyMCE, enable the global rich text editor setting and turn on **Use rich text editor** in that metadatum's type options. The option belongs to the metadatum definition.

`value` remains the stored text. `value_as_html` always runs `wpautop()` and `make_clickable()` on that stored string, including after it has been saved from TinyMCE. The item metadata routes return the usual `value` and `value_as_html` fields.

The first time a stored value opens in TinyMCE, the admin screen prepares paragraphs and links in the browser when the string has no `<p>`, `<ul>`, or `<ol>` tag. Opening or canceling the editor does not change the stored value. The next save stores the HTML the editor produced. A later open sees those tags and loads `value` as it is stored. A list-only value is treated as already converted, because TinyMCE can save a list without a paragraph.

An editor containing only an empty paragraph is stored as an empty value. That keeps required-field validation and optional-value removal working for Textarea and Core Description when the option is on. Bulk editing uses the same item form component, so a Textarea with the option on shows the editor there as well. Copying values stays limited to metadata of the same type and does not convert paragraphs or links during the copy.

## Conditional link sanitization for developers

`Repository::sanitize_value($content, $remove_links = true)` and `REST_Controller::sanitize_value($value, $remove_links = true)` use WordPress's allowed post HTML and remove anchor tags by default. The option removes the anchor tag, not its visible text. Pass `false` for a field the rich text editor can save.

`Metadata_Type::allows_links()` returns `false`. Textarea and Core Description return `true`, and saving or querying item metadata follows that answer. Entity descriptions are `post_content` and use `sanitize_rich_text_value()`, which keeps anchors. A text document does the same. Titles, names, Core Title, term names, and every other stored string still lose anchors.

REST `title` and `name` filters remove anchors before matching. A `metaquery` keeps them only when the targeted metadata type allows links. Other query clauses remove them. This change does not migrate previously saved values or restore links removed by earlier saves. Plain-text representations such as `value_as_string` and excerpts continue to strip HTML tags.
