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

It also adds a **Use rich text editor** switch to the metadata-type options for:

- Core Description metadata
- Textarea (long text) metadata

The switch defaults to `no`, so enabling the setting or constant does not change existing metadata fields. Set it to `yes` for each metadata that should display the rich text editor in item editing forms. It appears in the relevant metadata-type options section.

When the setting is disabled, or the constant has any value other than boolean `true`, Tainacan does not load the rich text editor, hides this per-metadata switch, and uses the original textarea even when a metadata was previously configured to use rich text. Its saved setting is retained, ready to be used again when rich text is enabled.

Descriptions, Core Description metadata, and Textarea metadata retain WordPress-safe formatting, including links, even while the editor is disabled or not selected for a metadata field. Other text inputs, such as titles and names, remove links when saved.

When `TAINACAN_ALLOW_RICH_TEXT_EDITOR` is defined, it overrides the saved setting and disables its control in the Tainacan settings page. This lets host managers keep the global choice over administrator preferences. Reload the Tainacan admin page after changing the setting or constant.
